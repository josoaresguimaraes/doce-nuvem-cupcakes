<?php

declare(strict_types=1);

namespace App\Models;

/** Coberturas disponíveis para personalização (US03 RN#2). */
final class Covering extends Model
{
    public function active(): array
    {
        return $this->fetchAll('SELECT * FROM coberturas WHERE ativa = 1 ORDER BY nome');
    }

    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM coberturas ORDER BY nome');
    }

    public function find(int $id): ?array
    {
        return $this->fetch('SELECT * FROM coberturas WHERE id = ?', [$id]);
    }

    public function findMany(array $ids): array
    {
        if ($ids === []) {
            return [];
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $byId = [];
        foreach ($this->fetchAll("SELECT * FROM coberturas WHERE id IN ($placeholders)", array_values($ids)) as $row) {
            $byId[(int) $row['id']] = $row;
        }
        return $byId;
    }

    public function nameExists(string $name): bool
    {
        return $this->fetch('SELECT 1 FROM coberturas WHERE nome = ?', [$name]) !== null;
    }

    public function create(string $name, float $extra): int
    {
        return $this->insert('INSERT INTO coberturas (nome, preco_adicional) VALUES (?, ?)', [$name, $extra]);
    }

    public function toggle(int $id): void
    {
        $this->execute('UPDATE coberturas SET ativa = 1 - ativa WHERE id = ?', [$id]);
    }
}
