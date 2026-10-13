<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Address;
use App\Models\Covering;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use DateTimeImmutable;
use DomainException;

/**
 * Orquestra carrinho, cálculo de custos, agendamento e pagamento para fechar o pedido.
 * Mantém os controladores enxutos e concentra as regras do checkout num só lugar.
 */
final class CheckoutService
{
    private Product $products;
    private Covering $coverings;
    private Address $addresses;
    private DeliveryZone $zones;
    private Setting $settings;
    private Order $orders;

    public function __construct(
        ?Product $products = null,
        ?Covering $coverings = null,
        ?Address $addresses = null,
        ?DeliveryZone $zones = null,
        ?Setting $settings = null,
        ?Order $orders = null,
        private readonly PricingService $pricing = new PricingService(),
        private readonly PaymentSimulator $payments = new PaymentSimulator(),
    ) {
        $this->products = $products ?? new Product();
        $this->coverings = $coverings ?? new Covering();
        $this->addresses = $addresses ?? new Address();
        $this->zones = $zones ?? new DeliveryZone();
        $this->settings = $settings ?? new Setting();
        $this->orders = $orders ?? new Order();
    }

    /**
     * Linhas do carrinho com nome, imagem e preços atuais do banco.
     * Itens cujo produto foi removido/desativado são descartados do carrinho.
     */
    public function detailedLines(Cart $cart): array
    {
        $lines = $cart->lines();
        $products = $this->products->findMany(array_unique(array_column($lines, 'produto_id')));
        $coverings = $this->coverings->findMany(array_filter(array_unique(array_column($lines, 'cobertura_id'))));

        $detailed = [];
        foreach ($lines as $key => $line) {
            $product = $products[$line['produto_id']] ?? null;
            if ($product === null || (int) $product['ativo'] !== 1) {
                $cart->remove($key);
                continue;
            }
            $covering = $line['cobertura_id'] ? ($coverings[$line['cobertura_id']] ?? null) : null;
            $unit = PricingService::unitPrice((float) $product['preco'], $covering ? (float) $covering['preco_adicional'] : null);
            $detailed[] = [
                'key' => $key,
                'produto_id' => (int) $product['id'],
                'nome_produto' => $product['nome'],
                'imagem' => $product['imagem'],
                'cobertura_id' => $covering ? (int) $covering['id'] : null,
                'nome_cobertura' => $covering['nome'] ?? null,
                'mensagem' => $line['mensagem'],
                'quantidade' => $line['quantidade'],
                'estoque' => (int) $product['estoque'],
                'preco_unitario' => $unit,
                'subtotal' => round($unit * $line['quantidade'], 2),
            ];
        }
        return $detailed;
    }

    public function settings(): array
    {
        return $this->settings->values();
    }

    public function scheduler(): DeliveryScheduler
    {
        return new DeliveryScheduler($this->settings->values());
    }

    /**
     * Cotação usada no carrinho/checkout (CA#2: atualiza ao mudar itens, endereço ou data).
     * @return array{ok: bool, erro?: string, valores?: array, zona?: ?string}
     */
    public function quote(Cart $cart, ?int $addressId, int $userId, string $date, DateTimeImmutable $now): array
    {
        $lines = $this->detailedLines($cart);
        $settings = $this->settings->values();
        $zoneFee = null;
        $zoneName = null;

        if ($addressId !== null) {
            $address = $this->addresses->findForUser($addressId, $userId);
            if ($address === null) {
                return ['ok' => false, 'erro' => 'Endereço não encontrado.'];
            }
            $zone = $this->zones->findByCep($address['cep']);
            if ($zone === null) {
                return [
                    'ok' => false,
                    'erro' => 'Infelizmente ainda não entregamos no CEP ' . format_cep($address['cep']) . '.',
                    'valores' => $this->pricing->quote($lines, null, $settings, false),
                ];
            }
            $zoneFee = (float) $zone['taxa'];
            $zoneName = $zone['nome'];
        }

        $urgent = $date !== '' && (new DeliveryScheduler($settings))->isUrgent($date, $now);
        return ['ok' => true, 'zona' => $zoneName, 'valores' => $this->pricing->quote($lines, $zoneFee, $settings, $urgent)];
    }

    /**
     * Valida tudo e cria o pedido.
     * @return array{ok: bool, errors?: array<string,string>, order_id?: int}
     */
    public function placeOrder(Cart $cart, int $userId, array $input, DateTimeImmutable $now): array
    {
        $errors = [];
        $lines = $this->detailedLines($cart);
        if ($lines === []) {
            return ['ok' => false, 'errors' => ['carrinho' => 'Seu carrinho está vazio.']];
        }
        foreach ($lines as $line) {
            if ($line['quantidade'] > $line['estoque']) {
                $errors['carrinho'] = sprintf(
                    'Só temos %d unidade(s) de "%s". Ajuste a quantidade no carrinho.',
                    $line['estoque'],
                    $line['nome_produto']
                );
            }
        }

        $address = $this->addresses->findForUser((int) ($input['endereco_id'] ?? 0), $userId);
        $zone = null;
        if ($address === null) {
            $errors['endereco_id'] = 'Escolha um endereço de entrega.';
        } else {
            $zone = $this->zones->findByCep($address['cep']);
            if ($zone === null) {
                $errors['endereco_id'] = 'Ainda não entregamos neste CEP. Escolha outro endereço.';
            }
        }

        $settings = $this->settings->values();
        $scheduler = new DeliveryScheduler($settings);
        $date = (string) ($input['data_entrega'] ?? '');
        $time = (string) ($input['hora_entrega'] ?? '');
        if (($scheduleError = $scheduler->validate($date, $time, $now)) !== null) {
            $errors['data_entrega'] = $scheduleError;
        }

        $method = (string) ($input['metodo_pagamento'] ?? '');
        if (!array_key_exists($method, Order::PAYMENT_LABELS)) {
            $errors['metodo_pagamento'] = 'Escolha uma forma de pagamento.';
        }

        if ($errors !== []) {
            return ['ok' => false, 'errors' => $errors];
        }

        $values = $this->pricing->quote($lines, (float) $zone['taxa'], $settings, $scheduler->isUrgent($date, $now));
        $payment = $this->payments->process($method, $input, $values['total'], $now);
        if (!$payment['ok']) {
            return ['ok' => false, 'errors' => [$payment['campo'] => $payment['erro']]];
        }

        try {
            $orderId = $this->orders->create($values + [
                'usuario_id' => $userId,
                'endereco_entrega' => Address::format($address),
                'cep_entrega' => $address['cep'],
                'data_entrega' => $date,
                'hora_entrega' => $time . ':00',
                'metodo_pagamento' => $method,
                'status_pagamento' => $payment['status_pagamento'],
                'codigo_pagamento' => $payment['codigo_pagamento'],
                'cartao_final' => $payment['cartao_final'],
                'vencimento_pagamento' => $payment['vencimento_pagamento'],
            ], $lines);
        } catch (DomainException $e) {
            return ['ok' => false, 'errors' => ['carrinho' => $e->getMessage()]];
        }

        $cart->clear();
        return ['ok' => true, 'order_id' => $orderId];
    }
}
