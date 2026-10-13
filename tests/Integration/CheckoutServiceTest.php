<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Address;
use App\Models\Order;
use App\Services\Cart;
use App\Services\CheckoutService;
use DateTimeImmutable;

/** Fluxo completo de compra (US03 → US06) com banco real. */
final class CheckoutServiceTest extends DatabaseTestCase
{
    private const CLIENTE = 2;
    private array $session = [];
    private Cart $cart;
    private CheckoutService $checkout;
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        parent::setUp();
        $this->session = [];
        $this->cart = new Cart($this->session);
        $this->checkout = new CheckoutService();
        $this->now = new DateTimeImmutable('today 10:00');
    }

    private function input(array $override = []): array
    {
        return $override + [
            'endereco_id' => 1,
            'data_entrega' => $this->now->modify('+1 day')->format('Y-m-d'),
            'hora_entrega' => '14:00',
            'metodo_pagamento' => 'cartao',
            'numero_cartao' => '4111111111111111', 'nome_cartao' => 'CLIENTE', 'validade_cartao' => '12/30', 'cvv' => '123',
        ];
    }

    public function testPedidoComCartaoGravaItensBaixaEstoqueELimpaCarrinho(): void
    {
        $this->cart->add(3, 3, 'Feliz aniversário Ana', 6); // Red Velvet + Cream cheese = 14,40 × 6
        $estoqueAntes = (int) $this->scalar('SELECT estoque FROM produtos WHERE id = 3');

        $result = $this->checkout->placeOrder($this->cart, self::CLIENTE, $this->input(), $this->now);

        self::assertTrue($result['ok'], json_encode($result));
        $order = (new Order())->find($result['order_id']);
        self::assertSame('86.40', $order['subtotal']);
        self::assertSame('8.00', $order['taxa_entrega']);
        self::assertSame('94.40', $order['total']);
        self::assertSame('aprovado', $order['status_pagamento']);
        self::assertSame('1111', $order['cartao_final']);
        self::assertSame('recebido', $order['status']);
        self::assertSame($estoqueAntes - 6, (int) $this->scalar('SELECT estoque FROM produtos WHERE id = 3'));

        $items = (new Order())->items($result['order_id']);
        self::assertCount(1, $items);
        self::assertSame('Cream cheese', $items[0]['nome_cobertura']);
        self::assertSame('Feliz aniversário Ana', $items[0]['mensagem']);
        self::assertCount(1, (new Order())->history($result['order_id']));
        self::assertTrue($this->cart->isEmpty());
    }

    public function testPedidoPequenoNoMesmoDiaCobraTaxas(): void
    {
        $this->cart->add(1, null, '', 2); // 19,80
        $result = $this->checkout->placeOrder($this->cart, self::CLIENTE, $this->input([
            'data_entrega' => $this->now->format('Y-m-d'), 'hora_entrega' => '15:00', 'metodo_pagamento' => 'pix',
        ]), $this->now);

        self::assertTrue($result['ok'], json_encode($result));
        $order = (new Order())->find($result['order_id']);
        self::assertSame('5.00', $order['taxa_pedido_minimo']);
        self::assertSame('10.00', $order['taxa_urgencia']);
        self::assertSame('42.80', $order['total']);
        self::assertSame('aguardando', $order['status_pagamento']);
        self::assertNotEmpty($order['codigo_pagamento']);
    }

    public function testEstoqueInsuficienteNaoCriaPedido(): void
    {
        $this->cart->add(8, null, '', 11); // Sem Glúten de Amêndoas: estoque 10
        $result = $this->checkout->placeOrder($this->cart, self::CLIENTE, $this->input(), $this->now);

        self::assertFalse($result['ok']);
        self::assertStringContainsString('10 unidade', $result['errors']['carrinho']);
        self::assertSame(0, (int) $this->scalar('SELECT COUNT(*) FROM pedidos'));
        self::assertSame(10, (int) $this->scalar('SELECT estoque FROM produtos WHERE id = 8'));
    }

    public function testCepForaDaAreaDeEntrega(): void
    {
        $id = (new Address())->create(self::CLIENTE, [
            'apelido' => 'SP', 'cep' => '01310100', 'logradouro' => 'Av. Paulista', 'numero' => '1000',
            'complemento' => '', 'bairro' => 'Bela Vista', 'cidade' => 'São Paulo', 'uf' => 'SP',
        ]);
        $this->cart->add(1, null, '', 6);
        $quote = $this->checkout->quote($this->cart, $id, self::CLIENTE, '', $this->now);
        self::assertFalse($quote['ok']);
        self::assertStringContainsString('01310-100', $quote['erro']);

        $result = $this->checkout->placeOrder($this->cart, self::CLIENTE, $this->input(['endereco_id' => $id]), $this->now);
        self::assertArrayHasKey('endereco_id', $result['errors']);
    }

    public function testNaoUsaEnderecoDeOutroCliente(): void
    {
        $this->pdo->exec("INSERT INTO usuarios (id, nome, email, senha_hash) VALUES (3, 'Outro', 'outro@x.com', 'x')");
        $this->cart->add(1, null, '', 6);
        $result = $this->checkout->placeOrder($this->cart, 3, $this->input(['endereco_id' => 1]), $this->now);
        self::assertFalse($result['ok']);
        self::assertArrayHasKey('endereco_id', $result['errors']);
    }

    public function testCartaoRecusadoNaoCriaPedidoNemBaixaEstoque(): void
    {
        $this->cart->add(1, null, '', 6);
        $result = $this->checkout->placeOrder($this->cart, self::CLIENTE, $this->input(['numero_cartao' => '4000000000000002']), $this->now);
        self::assertFalse($result['ok']);
        self::assertArrayHasKey('numero_cartao', $result['errors']);
        self::assertSame(0, (int) $this->scalar('SELECT COUNT(*) FROM pedidos'));
        self::assertSame(40, (int) $this->scalar('SELECT estoque FROM produtos WHERE id = 1'));
        self::assertFalse($this->cart->isEmpty(), 'cliente pode tentar de novo');
    }

    public function testHorarioForaDoExpedienteERecusado(): void
    {
        $this->cart->add(1, null, '', 6);
        $result = $this->checkout->placeOrder($this->cart, self::CLIENTE, $this->input(['hora_entrega' => '20:00']), $this->now);
        self::assertArrayHasKey('data_entrega', $result['errors']);
    }

    public function testCarrinhoVazio(): void
    {
        $result = $this->checkout->placeOrder($this->cart, self::CLIENTE, $this->input(), $this->now);
        self::assertSame('Seu carrinho está vazio.', $result['errors']['carrinho']);
    }

    public function testProdutoDesativadoSaiDoCarrinho(): void
    {
        $this->cart->add(5, null, '', 1);
        $this->pdo->exec('UPDATE produtos SET ativo = 0 WHERE id = 5');
        self::assertSame([], $this->checkout->detailedLines($this->cart));
        self::assertTrue($this->cart->isEmpty());
    }
}
