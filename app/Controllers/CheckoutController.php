<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Address;
use App\Models\Order;
use App\Services\Cart;
use App\Services\CheckoutService;
use DateTimeImmutable;

/** Finalização da compra: endereço, data/horário (US06), custos (US04) e pagamento (US05). */
final class CheckoutController extends Controller
{
    public function index(): void
    {
        $cart = Cart::fromSession();
        $service = new CheckoutService();
        $lines = $service->detailedLines($cart);
        if ($lines === []) {
            Session::flash('info', 'Seu carrinho está vazio. Escolha seus cupcakes na vitrine.');
            redirect('/');
        }

        $now = new DateTimeImmutable();
        $addresses = (new Address())->forUser(Auth::id());
        $scheduler = $service->scheduler();
        $settings = $service->settings();

        // Primeira data com horário livre, para já sugerir no formulário.
        $firstDate = $now->format('Y-m-d');
        for ($i = 0; $i <= (int) $settings['dias_maximos']; $i++) {
            $candidate = $now->modify("+{$i} days")->format('Y-m-d');
            if ($scheduler->availableSlots($candidate, $now) !== []) {
                $firstDate = $candidate;
                break;
            }
        }

        $this->view('checkout/index', [
            'title' => 'Finalizar pedido',
            'lines' => $lines,
            'addresses' => $addresses,
            'settings' => $settings,
            'firstDate' => $firstDate,
            'maxDate' => $now->modify('+' . (int) $settings['dias_maximos'] . ' days')->format('Y-m-d'),
            'slots' => $scheduler->availableSlots($firstDate, $now),
            'payments' => Order::PAYMENT_LABELS,
        ]);
    }

    /** Recalcula os valores ao trocar endereço ou data (CA#2), sem recarregar a página. */
    public function quote(): void
    {
        $addressId = (int) $this->request->query('endereco_id', 0) ?: null;
        $result = (new CheckoutService())->quote(
            Cart::fromSession(),
            $addressId,
            Auth::id(),
            (string) $this->request->query('data_entrega', ''),
            new DateTimeImmutable()
        );
        if (isset($result['valores'])) {
            $result['formatado'] = array_map('money', $result['valores']);
        }
        $this->json($result, $result['ok'] ? 200 : 422);
    }

    public function slots(): void
    {
        $date = (string) $this->request->query('data', '');
        $this->json(['data' => $date, 'horarios' => (new CheckoutService())->scheduler()->availableSlots($date, new DateTimeImmutable())]);
    }

    public function place(): void
    {
        $result = (new CheckoutService())->placeOrder(Cart::fromSession(), Auth::id(), $this->request->all(), new DateTimeImmutable());
        if (!$result['ok']) {
            $this->backWithErrors('/checkout', $result['errors']);
        }
        redirect('/pedidos/' . $result['order_id'] . '?novo=1');
    }
}
