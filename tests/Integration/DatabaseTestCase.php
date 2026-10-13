<?php

declare(strict_types=1);

namespace Tests\Integration;

use App\Core\Database;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Base dos testes de integração: recria o banco de testes (schema + seed)
 * antes de cada teste, garantindo isolamento entre eles.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        Database::setConnection(null);
        try {
            $this->pdo = Database::connection();
        } catch (\PDOException $e) {
            self::markTestSkipped('Banco de testes indisponível: ' . $e->getMessage());
        }
        $this->runSqlFile(ROOT_PATH . '/database/schema.sql');
        $this->runSqlFile(ROOT_PATH . '/database/seed.sql');
    }

    private function runSqlFile(string $file): void
    {
        $sql = (string) file_get_contents($file);
        $sql = preg_replace('/^\s*--.*$/m', '', $sql);
        foreach (array_filter(array_map('trim', explode(";\n", (string) $sql))) as $statement) {
            $this->pdo->exec($statement);
        }
    }

    protected function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}
