<?php

declare(strict_types=1);

namespace App\Models;

final class User extends Model
{
    public function find(int $id): ?array
    {
        return $this->fetch('SELECT id, nome, email, perfil, criado_em FROM usuarios WHERE id = ?', [$id]);
    }

    /** Inclui o hash da senha: usado apenas no login. */
    public function findByEmail(string $email): ?array
    {
        return $this->fetch('SELECT * FROM usuarios WHERE email = ?', [mb_strtolower($email)]);
    }

    public function emailExists(string $email): bool
    {
        return $this->fetch('SELECT 1 FROM usuarios WHERE email = ?', [mb_strtolower($email)]) !== null;
    }

    public function create(string $name, string $email, string $password, string $role = 'cliente'): int
    {
        return $this->insert(
            'INSERT INTO usuarios (nome, email, senha_hash, perfil) VALUES (?, ?, ?, ?)',
            [$name, mb_strtolower($email), password_hash($password, PASSWORD_DEFAULT), $role]
        );
    }
}
