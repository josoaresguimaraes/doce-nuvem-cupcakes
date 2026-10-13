<?php

declare(strict_types=1);

namespace App\Models;

final class Product extends Model
{
    /**
     * Lista produtos para a vitrine, com filtro por categorias (US02: combinação de filtros).
     * Com várias categorias marcadas, o produto aparece se pertencer a TODAS elas
     * (ex.: "Veganos" + "Sem glúten" mostra só o que atende às duas restrições).
     *
     * @param int[] $categoryIds
     * @return array{items: array<int, array>, total: int, page: int, per_page: int, has_more: bool}
     */
    public function paginate(array $categoryIds = [], int $page = 1, int $perPage = 8, bool $onlyActive = true): array
    {
        $page = max(1, $page);
        $where = [];
        $params = [];

        if ($onlyActive) {
            $where[] = 'p.ativo = 1';
        }
        $categoryIds = array_values(array_unique(array_map('intval', $categoryIds)));
        if ($categoryIds !== []) {
            $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));
            $where[] = "p.id IN (
                SELECT produto_id FROM produto_categoria
                 WHERE categoria_id IN ($placeholders)
              GROUP BY produto_id
                HAVING COUNT(DISTINCT categoria_id) = ?)";
            array_push($params, ...$categoryIds);
            $params[] = count($categoryIds);
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $total = (int) ($this->fetch("SELECT COUNT(*) AS total FROM produtos p $whereSql", $params)['total'] ?? 0);

        $offset = ($page - 1) * $perPage;
        $items = $this->fetchAll(
            "SELECT p.*, GROUP_CONCAT(c.nome ORDER BY c.nome SEPARATOR ', ') AS categorias
               FROM produtos p
          LEFT JOIN produto_categoria pc ON pc.produto_id = p.id
          LEFT JOIN categorias c ON c.id = pc.categoria_id
               $whereSql
           GROUP BY p.id
           ORDER BY (p.estoque = 0), p.nome
              LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => $offset + count($items) < $total,
        ];
    }

    public function find(int $id, bool $onlyActive = false): ?array
    {
        $product = $this->fetch(
            'SELECT * FROM produtos WHERE id = ?' . ($onlyActive ? ' AND ativo = 1' : ''),
            [$id]
        );
        if ($product === null) {
            return null;
        }
        $product['categoria_ids'] = array_map(
            static fn ($r) => (int) $r['categoria_id'],
            $this->fetchAll('SELECT categoria_id FROM produto_categoria WHERE produto_id = ?', [$id])
        );
        return $product;
    }

    /** @param int[] $categoryIds */
    public function create(array $data, array $categoryIds): int
    {
        $this->db->beginTransaction();
        try {
            $id = $this->insert(
                'INSERT INTO produtos (nome, descricao, preco, estoque, imagem, ativo) VALUES (?, ?, ?, ?, ?, ?)',
                [$data['nome'], $data['descricao'], $data['preco'], $data['estoque'], $data['imagem'] ?? null, $data['ativo'] ?? 1]
            );
            $this->syncCategories($id, $categoryIds);
            $this->db->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** @param int[] $categoryIds */
    public function update(int $id, array $data, array $categoryIds): void
    {
        $this->db->beginTransaction();
        try {
            $fields = 'nome = ?, descricao = ?, preco = ?, estoque = ?, ativo = ?';
            $params = [$data['nome'], $data['descricao'], $data['preco'], $data['estoque'], $data['ativo'] ?? 1];
            if (array_key_exists('imagem', $data)) {
                $fields .= ', imagem = ?';
                $params[] = $data['imagem'];
            }
            $params[] = $id;
            $this->execute("UPDATE produtos SET $fields WHERE id = ?", $params);
            $this->syncCategories($id, $categoryIds);
            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Remove o produto. Se ele já aparece em pedidos, apenas o desativa
     * para preservar o histórico (integridade referencial).
     *
     * @return string 'excluido' ou 'desativado'
     */
    public function remove(int $id): string
    {
        $used = $this->fetch('SELECT 1 FROM itens_pedido WHERE produto_id = ? LIMIT 1', [$id]) !== null;
        if ($used) {
            $this->execute('UPDATE produtos SET ativo = 0 WHERE id = ?', [$id]);
            return 'desativado';
        }
        $this->execute('DELETE FROM produtos WHERE id = ?', [$id]);
        return 'excluido';
    }

    /** @param int[] $categoryIds */
    private function syncCategories(int $productId, array $categoryIds): void
    {
        $this->execute('DELETE FROM produto_categoria WHERE produto_id = ?', [$productId]);
        foreach (array_unique($categoryIds) as $categoryId) {
            $this->execute(
                'INSERT INTO produto_categoria (produto_id, categoria_id) VALUES (?, ?)',
                [$productId, (int) $categoryId]
            );
        }
    }

    /** Produtos por id (usado para montar o carrinho). @param int[] $ids */
    public function findMany(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->fetchAll("SELECT * FROM produtos WHERE id IN ($placeholders)", array_values($ids));
        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row['id']] = $row;
        }
        return $byId;
    }
}
