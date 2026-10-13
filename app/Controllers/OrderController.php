<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\HttpException;
use App\Models\Order;

/** Histórico e rastreamento de pedidos do cliente (US07). */
final class OrderController extends Controller
{
    public function index(): void
    {
        $this->view('pedidos/index', [
            'title' => 'Meus pedidos',
            'orders' => (new Order())->forUser(Auth::id()),
        ]);
    }

    public function show(int $id): void
    {
        $orders = new Order();
        $order = $this->owned($orders, $id);
        $this->view('pedidos/show', [
            'title' => 'Pedido #' . $id,
            'order' => $order,
            'items' => $orders->items($id),
            'history' => $orders->history($id),
            'isNew' => $this->request->query('novo') === '1',
        ]);
    }

    /** Consultado a cada 5 s pela página do pedido (RNF: atualização em menos de 5 segundos). */
    public function status(int $id): void
    {
        $orders = new Order();
        $order = $this->owned($orders, $id);
        $this->json([
            'status' => $order['status'],
            'status_label' => status_label($order['status']),
            'status_pagamento' => $order['status_pagamento'],
            'status_pagamento_label' => Order::PAYMENT_STATUS_LABELS[$order['status_pagamento']],
            'historico' => array_map(static fn ($h) => [
                'status' => $h['status'],
                'label' => status_label($h['status']),
                'quando' => format_date($h['alterado_em'], true),
            ], $orders->history($id)),
        ]);
    }

    private function owned(Order $orders, int $id): array
    {
        $order = $orders->findForUser($id, Auth::id());
        if ($order === null) {
            throw new HttpException(404, 'Pedido não encontrado.');
        }
        return $order;
    }
}
