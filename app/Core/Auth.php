<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

/** Usuário autenticado na sessão. */
final class Auth
{
    private static ?array $cached = null;

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id', (int) $user['id']);
        self::$cached = $user;
    }

    public static function logout(): void
    {
        self::$cached = null;
        Session::destroy();
    }

    public static function user(): ?array
    {
        $id = Session::get('user_id');
        if ($id === null) {
            return null;
        }
        if (self::$cached === null || (int) self::$cached['id'] !== (int) $id) {
            self::$cached = (new User())->find((int) $id);
        }
        return self::$cached;
    }

    public static function id(): ?int
    {
        $user = self::user();
        return $user ? (int) $user['id'] : null;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function isManager(): bool
    {
        return (self::user()['perfil'] ?? null) === 'gerente';
    }
}
