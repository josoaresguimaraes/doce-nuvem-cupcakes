<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Session;
use App\Models\Order;
use DomainException;

/** Painel de pedidos do gerente: atualiza status (US07) e confirma pagamentos Pix/boleto. */
final class OrderController extends Controller
{
    public function index(): void
    {
        $status = (string) $this->request->query('status', '');
        $this->view('admin/pedidos/index', [
            'title' => 'Pedidos',
            'orders' => (new Order())->allForAdmin($status !== '' ? $status : null),
            'status' => $status,
        ], 'layouts/admin');
    }

    public function show(int $id): void
    {
        $orders = new Order();
        $order = $orders->find($id) ?? throw new HttpException(404, 'Pedido não encontrado.');
        $this->view('admin/pedidos/show', [
            'title' => 'Pedido #' . $id,
            'order' => $order,
            'items' => $orders->items($id),
            'history' => $orders->history($id),
        ], 'layouts/admin');
    }

    public function status(int $id): void
    {
        try {
            (new Order())->changeStatus($id, (string) $this->request->input('status'));
            Session::flash('success', "Pedido #{$id} atualizado.");
        } catch (DomainException $e) {
            Session::flash('error', $e->getMessage());
        }
        redirect('/admin/pedidos/' . $id);
    }

    public function confirmPayment(int $id): void
    {
        (new Order())->confirmPayment($id);
        Session::flash('success', "Pagamento do pedido #{$id} confirmado.");
        redirect('/admin/pedidos/' . $id);
    }
}
