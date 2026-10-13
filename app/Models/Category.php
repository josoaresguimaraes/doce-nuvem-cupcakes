<?php

declare(strict_types=1);

namespace App\Models;

final class Category extends Model
{
    public function all(): array
    {
        return $this->fetchAll(
            'SELECT c.id, c.nome, COUNT(pc.produto_id) AS total_produtos
               FROM categorias c
          LEFT JOIN produto_categoria pc ON pc.categoria_id = c.id
           GROUP BY c.id, c.nome
           ORDER BY c.nome'
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetch('SELECT * FROM categorias WHERE id = ?', [$id]);
    }

    public function nameExists(string $name): bool
    {
        return $this->fetch('SELECT 1 FROM categorias WHERE nome = ?', [$name]) !== null;
    }

    public function create(string $name): int
    {
        return $this->insert('INSERT INTO categorias (nome) VALUES (?)', [$name]);
    }

    public function delete(int $id): bool
    {
        return $this->execute('DELETE FROM categorias WHERE id = ?', [$id]) > 0;
    }

    /** @param int[] $ids */
    public function existingIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $rows = $this->fetchAll("SELECT id FROM categorias WHERE id IN ($placeholders)", array_values($ids));
        return array_map(static fn ($r) => (int) $r['id'], $rows);
    }
}
