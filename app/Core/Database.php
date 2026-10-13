<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Conexão única (singleton) com o MySQL via PDO.
 * Usa prepared statements em todas as consultas para evitar SQL injection.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                Config::get('DB_HOST', '127.0.0.1'),
                Config::get('DB_PORT', '3306'),
                Config::get('DB_NAME', 'cupcakes')
            );
            self::$pdo = new PDO($dsn, Config::get('DB_USER', 'root'), Config::get('DB_PASS', ''), [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }
        return self::$pdo;
    }

    /** Permite injetar outra conexão (usado nos testes). */
    public static function setConnection(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }
}
