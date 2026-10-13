<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Models\Order;
use DomainException;

/** US07 (status/rastreamento) e US10 (relatórios). */
final class OrderTest extends DatabaseTestCase
{
    private Order $orders;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orders = new Order();
    }

    private function newOrder(string $payment = 'aprovado', int $qty = 2, float $total = 50.0): int
    {
        return $this->orders->create([
            'usuario_id' => 2, 'endereco_entrega' => 'Rua Exemplo, 100', 'cep_entrega' => '18035000',
            'subtotal' => $total, 'taxa_entrega' => 0, 'taxa_pedido_minimo' => 0, 'taxa_urgencia' => 0, 'total' => $total,
            'data_entrega' => date('Y-m-d', strtotime('+1 day')), 'hora_entrega' => '10:00:00',
            'metodo_pagamento' => $payment === 'aprovado' ? 'cartao' : 'pix', 'status_pagamento' => $payment,
        ], [[
            'produto_id' => 1, 'nome_produto' => 'Chocolate Clássico', 'cobertura_id' => null, 'nome_cobertura' => null,
            'mensagem' => '', 'quantidade' => $qty, 'preco_unitario' => 9.9, 'subtotal' => 9.9 * $qty,
        ]]);
    }

    public function testFluxoCompletoDeStatusGeraLinhaDoTempo(): void
    {
        $id = $this->newOrder();
        foreach (['em_preparo', 'em_transito', 'entregue'] as $status) {
            $this->orders->changeStatus($id, $status);
        }
        self::assertSame('entregue', $this->orders->find($id)['status']);
        self::assertSame(['recebido', 'em_preparo', 'em_transito', 'entregue'], array_column($this->orders->history($id), 'status'));
    }

    /** RN#1 da US07: o status reflete as etapas reais — não pula etapas. */
    public function testNaoPermiteTransicaoInvalida(): void
    {
        $id = $this->newOrder();
        $this->expectException(DomainException::class);
        $this->orders->changeStatus($id, 'entregue');
    }

    public function testNaoAvancaSemPagamentoConfirmado(): void
    {
        $id = $this->newOrder('aguardando');
        try {
            $this->orders->changeStatus($id, 'em_preparo');
            self::fail('Deveria exigir pagamento');
        } catch (DomainException $e) {
            self::assertStringContainsString('pagamento', $e->getMessage());
        }
        $this->orders->confirmPayment($id);
        $this->orders->changeStatus($id, 'em_preparo');
        self::assertSame('em_preparo', $this->orders->find($id)['status']);
    }

    public function testCancelamentoDevolveEstoque(): void
    {
        $id = $this->newOrder('aprovado', 5);
        self::assertSame(35, (int) $this->scalar('SELECT estoque FROM produtos WHERE id = 1'));
        $this->orders->changeStatus($id, 'cancelado');
        self::assertSame(40, (int) $this->scalar('SELECT estoque FROM produtos WHERE id = 1'));
    }

    /** US10 RN#1: só entram pedidos com pagamento aprovado e não cancelados. */
    public function testRelatorioConsideraApenasPedidosValidos(): void
    {
        $this->newOrder('aprovado', 1, 30.0);
        $this->newOrder('aprovado', 1, 70.0);
        $this->newOrder('aguardando', 1, 999.0);
        $cancelado = $this->newOrder('aprovado', 1, 500.0);
        $this->orders->changeStatus($cancelado, 'cancelado');

        $today = date('Y-m-d');
        $report = $this->orders->salesReport($today, $today, 'dia');
        self::assertSame(2, $report['totais']['pedidos']);
        self::assertSame(100.0, $report['totais']['faturamento']);
        self::assertSame(50.0, $report['totais']['ticket_medio']);
        self::assertCount(1, $report['linhas']);
    }

    public function testClienteSoVeOsProprios(): void
    {
        $id = $this->newOrder();
        self::assertNotNull($this->orders->findForUser($id, 2));
        self::assertNull($this->orders->findForUser($id, 1));
    }
}
