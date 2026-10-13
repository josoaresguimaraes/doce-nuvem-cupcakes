<?php

declare(strict_types=1);

namespace App\Models;

use DomainException;

/** Pedidos, itens e linha do tempo de status (US04–US07, US10). */
final class Order extends Model
{
    public const STATUS_LABELS = [
        'recebido' => 'Recebido',
        'em_preparo' => 'Em preparo',
        'em_transito' => 'Em trânsito',
        'entregue' => 'Entregue',
        'cancelado' => 'Cancelado',
    ];

    public const PAYMENT_LABELS = [
        'cartao' => 'Cartão de crédito',
        'pix' => 'Pix',
        'boleto' => 'Boleto',
    ];

    public const PAYMENT_STATUS_LABELS = [
        'aguardando' => 'Aguardando pagamento',
        'aprovado' => 'Aprovado',
        'recusado' => 'Recusado',
    ];

    /** Transições permitidas (RN US07#1: o status reflete as etapas reais). */
    public const TRANSITIONS = [
        'recebido' => ['em_preparo', 'cancelado'],
        'em_preparo' => ['em_transito', 'cancelado'],
        'em_transito' => ['entregue'],
        'entregue' => [],
        'cancelado' => [],
    ];

    /**
     * Cria o pedido numa transação: confere e baixa o estoque, grava itens e o 1º status.
     *
     * @param array $order campos da tabela pedidos
     * @param array<int, array{produto_id:int, nome_produto:string, cobertura_id:?int, nome_cobertura:?string,
     *                          mensagem:?string, quantidade:int, preco_unitario:float, subtotal:float}> $items
     * @throws DomainException se faltar estoque
     */
    public function create(array $order, array $items): int
    {
        $this->db->beginTransaction();
        try {
            $needed = [];
            foreach ($items as $item) {
                $needed[$item['produto_id']] = ($needed[$item['produto_id']] ?? 0) + $item['quantidade'];
            }
            foreach ($needed as $productId => $qty) {
                $product = $this->fetch('SELECT nome, estoque, ativo FROM produtos WHERE id = ? FOR UPDATE', [$productId]);
                if ($product === null || (int) $product['ativo'] !== 1) {
                    throw new DomainException('Um dos produtos do carrinho não está mais disponível.');
                }
                if ((int) $product['estoque'] < $qty) {
                    throw new DomainException(sprintf(
                        'Estoque insuficiente para "%s": restam %d unidade(s).',
                        $product['nome'],
                        (int) $product['estoque']
                    ));
                }
                $this->execute('UPDATE produtos SET estoque = estoque - ? WHERE id = ?', [$qty, $productId]);
            }

            $orderId = $this->insert(
                'INSERT INTO pedidos (usuario_id, endereco_entrega, cep_entrega, subtotal, taxa_entrega, taxa_pedido_minimo,
                                      taxa_urgencia, total, data_entrega, hora_entrega, metodo_pagamento, status_pagamento,
                                      codigo_pagamento, cartao_final, vencimento_pagamento)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                [
                    $order['usuario_id'], $order['endereco_entrega'], $order['cep_entrega'], $order['subtotal'],
                    $order['taxa_entrega'], $order['taxa_pedido_minimo'], $order['taxa_urgencia'], $order['total'],
                    $order['data_entrega'], $order['hora_entrega'], $order['metodo_pagamento'], $order['status_pagamento'],
                    $order['codigo_pagamento'] ?? null, $order['cartao_final'] ?? null, $order['vencimento_pagamento'] ?? null,
                ]
            );

            foreach ($items as $item) {
                $this->execute(
                    'INSERT INTO itens_pedido (pedido_id, produto_id, nome_produto, cobertura_id, nome_cobertura, mensagem,
                                               quantidade, preco_unitario, subtotal)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$orderId, $item['produto_id'], $item['nome_produto'], $item['cobertura_id'], $item['nome_cobertura'],
                     $item['mensagem'] ?: null, $item['quantidade'], $item['preco_unitario'], $item['subtotal']]
                );
            }

            $this->execute('INSERT INTO historico_status (pedido_id, status) VALUES (?, ?)', [$orderId, 'recebido']);
            $this->db->commit();
            return $orderId;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function find(int $id): ?array
    {
        return $this->fetch(
            'SELECT p.*, u.nome AS cliente_nome, u.email AS cliente_email
               FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id
              WHERE p.id = ?',
            [$id]
        );
    }

    public function findForUser(int $id, int $userId): ?array
    {
        $order = $this->find($id);
        return ($order !== null && (int) $order['usuario_id'] === $userId) ? $order : null;
    }

    public function forUser(int $userId): array
    {
        return $this->fetchAll(
            'SELECT p.*, (SELECT SUM(quantidade) FROM itens_pedido i WHERE i.pedido_id = p.id) AS total_itens
               FROM pedidos p WHERE p.usuario_id = ? ORDER BY p.criado_em DESC, p.id DESC',
            [$userId]
        );
    }

    public function items(int $orderId): array
    {
        return $this->fetchAll('SELECT * FROM itens_pedido WHERE pedido_id = ? ORDER BY id', [$orderId]);
    }

    public function history(int $orderId): array
    {
        return $this->fetchAll('SELECT * FROM historico_status WHERE pedido_id = ? ORDER BY alterado_em, id', [$orderId]);
    }

    public function allForAdmin(?string $status = null): array
    {
        $sql = 'SELECT p.*, u.nome AS cliente_nome
                  FROM pedidos p JOIN usuarios u ON u.id = p.usuario_id';
        $params = [];
        if ($status !== null && isset(self::STATUS_LABELS[$status])) {
            $sql .= ' WHERE p.status = ?';
            $params[] = $status;
        }
        return $this->fetchAll($sql . ' ORDER BY p.data_entrega, p.hora_entrega, p.id', $params);
    }

    /**
     * Avança o status respeitando as transições permitidas.
     * Pedido cancelado devolve os itens ao estoque.
     */
    public function changeStatus(int $id, string $newStatus): void
    {
        $order = $this->find($id);
        if ($order === null) {
            throw new DomainException('Pedido não encontrado.');
        }
        if (!in_array($newStatus, self::TRANSITIONS[$order['status']] ?? [], true)) {
            throw new DomainException(sprintf(
                'Não é possível mudar de "%s" para "%s".',
                status_label($order['status']),
                status_label($newStatus)
            ));
        }
        if (in_array($newStatus, ['em_preparo', 'em_transito', 'entregue'], true) && $order['status_pagamento'] !== 'aprovado') {
            throw new DomainException('Confirme o pagamento antes de avançar o pedido.');
        }

        $this->db->beginTransaction();
        try {
            $this->execute('UPDATE pedidos SET status = ? WHERE id = ?', [$newStatus, $id]);
            $this->execute('INSERT INTO historico_status (pedido_id, status) VALUES (?, ?)', [$id, $newStatus]);
            if ($newStatus === 'cancelado') {
                foreach ($this->items($id) as $item) {
                    $this->execute('UPDATE produtos SET estoque = estoque + ? WHERE id = ?', [$item['quantidade'], $item['produto_id']]);
                }
            }
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function confirmPayment(int $id): void
    {
        $this->execute(
            "UPDATE pedidos SET status_pagamento = 'aprovado' WHERE id = ? AND status_pagamento = 'aguardando' AND status <> 'cancelado'",
            [$id]
        );
    }

    /**
     * Relatório de vendas (US10). Considera apenas pedidos com pagamento aprovado e não cancelados (RN#1).
     *
     * @param 'dia'|'semana'|'mes' $groupBy
     */
    public function salesReport(string $from, string $to, string $groupBy = 'dia'): array
    {
        $period = match ($groupBy) {
            'semana' => "DATE_FORMAT(DATE_SUB(DATE(criado_em), INTERVAL WEEKDAY(criado_em) DAY), '%Y-%m-%d')",
            'mes' => "DATE_FORMAT(criado_em, '%Y-%m')",
            default => "DATE_FORMAT(criado_em, '%Y-%m-%d')",
        };
        $rows = $this->fetchAll(
            "SELECT $period AS periodo, COUNT(*) AS pedidos, SUM(total) AS faturamento
               FROM pedidos
              WHERE status_pagamento = 'aprovado' AND status <> 'cancelado'
                AND DATE(criado_em) BETWEEN ? AND ?
           GROUP BY periodo
           ORDER BY periodo",
            [$from, $to]
        );
        $totals = ['pedidos' => 0, 'faturamento' => 0.0];
        foreach ($rows as $row) {
            $totals['pedidos'] += (int) $row['pedidos'];
            $totals['faturamento'] += (float) $row['faturamento'];
        }
        $totals['ticket_medio'] = $totals['pedidos'] > 0 ? $totals['faturamento'] / $totals['pedidos'] : 0.0;
        return ['linhas' => $rows, 'totais' => $totals];
    }
}
