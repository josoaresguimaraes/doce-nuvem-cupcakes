<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** Erro HTTP controlado (404, 403, 405, 419...). */
final class HttpException extends RuntimeException
{
    public function __construct(public readonly int $status, string $message = '')
    {
        parent::__construct($message !== '' ? $message : self::defaultMessage($status), $status);
    }

    private static function defaultMessage(int $status): string
    {
        return match ($status) {
            403 => 'Você não tem permissão para acessar esta página.',
            404 => 'Página não encontrada.',
            405 => 'Método não permitido.',
            419 => 'Sua sessão expirou. Recarregue a página e tente novamente.',
            default => 'Ocorreu um erro inesperado.',
        };
    }
}
