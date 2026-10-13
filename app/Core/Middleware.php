<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Filtros executados antes das ações:
 *  - auth:    exige usuário logado
 *  - gerente: exige perfil de gerente (RN: apenas gerentes acessam a área administrativa)
 *  - csrf:    valida o token de formulários POST
 */
final class Middleware
{
    public static function run(string $name, Request $request): void
    {
        match ($name) {
            'auth' => self::auth($request),
            'gerente' => self::manager($request),
            'csrf' => self::csrf($request),
            default => throw new \InvalidArgumentException("Middleware desconhecido: {$name}"),
        };
    }

    private static function auth(Request $request): void
    {
        if (Auth::check()) {
            return;
        }
        if ($request->wantsJson()) {
            throw new HttpException(401, 'Faça login para continuar.');
        }
        Session::set('_intended', $request->path);
        Session::flash('info', 'Faça login para continuar.');
        redirect('/login');
    }

    private static function manager(Request $request): void
    {
        self::auth($request);
        if (!Auth::isManager()) {
            throw new HttpException(403);
        }
    }

    private static function csrf(Request $request): void
    {
        $token = (string) $request->input('_token', $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!hash_equals(Session::csrfToken(), $token)) {
            throw new HttpException(419);
        }
    }
}
