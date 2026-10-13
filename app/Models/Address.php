<?php

declare(strict_types=1);

namespace App\Models;

/** Endereços de entrega do cliente (US15). Todas as consultas filtram pelo dono (RN#1). */
final class Address extends Model
{
    public const MAX_PER_USER = 3;

    public function forUser(int $userId): array
    {
        return $this->fetchAll('SELECT * FROM enderecos WHERE usuario_id = ? ORDER BY id', [$userId]);
    }

    public function findForUser(int $id, int $userId): ?array
    {
        return $this->fetch('SELECT * FROM enderecos WHERE id = ? AND usuario_id = ?', [$id, $userId]);
    }

    public function countForUser(int $userId): int
    {
        return (int) $this->fetch('SELECT COUNT(*) AS t FROM enderecos WHERE usuario_id = ?', [$userId])['t'];
    }

    public function create(int $userId, array $data): int
    {
        return $this->insert(
            'INSERT INTO enderecos (usuario_id, apelido, cep, logradouro, numero, complemento, bairro, cidade, uf)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$userId, $data['apelido'], $data['cep'], $data['logradouro'], $data['numero'],
             $data['complemento'] ?: null, $data['bairro'], $data['cidade'], $data['uf']]
        );
    }

    public function update(int $id, int $userId, array $data): bool
    {
        return $this->execute(
            'UPDATE enderecos SET apelido = ?, cep = ?, logradouro = ?, numero = ?, complemento = ?,
                    bairro = ?, cidade = ?, uf = ?
              WHERE id = ? AND usuario_id = ?',
            [$data['apelido'], $data['cep'], $data['logradouro'], $data['numero'], $data['complemento'] ?: null,
             $data['bairro'], $data['cidade'], $data['uf'], $id, $userId]
        ) >= 0;
    }

    public function delete(int $id, int $userId): bool
    {
        return $this->execute('DELETE FROM enderecos WHERE id = ? AND usuario_id = ?', [$id, $userId]) > 0;
    }

    public static function format(array $a): string
    {
        $line = "{$a['logradouro']}, {$a['numero']}";
        if (!empty($a['complemento'])) {
            $line .= " – {$a['complemento']}";
        }
        return "{$line} – {$a['bairro']}, {$a['cidade']}/{$a['uf']} – CEP " . format_cep($a['cep']);
    }
}
