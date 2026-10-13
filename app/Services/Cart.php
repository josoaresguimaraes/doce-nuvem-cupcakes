<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Carrinho guardado na sessão (US03). Cada linha é única pela combinação
 * produto + cobertura + mensagem; adicionar a mesma combinação soma a quantidade.
 *
 * O armazenamento é recebido por referência para permitir testes sem sessão HTTP.
 */
final class Cart
{
    public const MAX_QTY_PER_LINE = 50;

    /** @var array<string, array{produto_id:int, cobertura_id:?int, mensagem:string, quantidade:int}> */
    private array $lines;

    public function __construct(array &$storage)
    {
        $storage['cart'] ??= [];
        $this->lines = &$storage['cart'];
    }

    public static function fromSession(): self
    {
        return new self($_SESSION);
    }

    public static function key(int $productId, ?int $coveringId, string $message): string
    {
        return substr(sha1($productId . '|' . ($coveringId ?? 0) . '|' . mb_strtolower(trim($message))), 0, 12);
    }

    public function add(int $productId, ?int $coveringId, string $message, int $quantity): string
    {
        $quantity = max(1, $quantity);
        $key = self::key($productId, $coveringId, $message);
        if (isset($this->lines[$key])) {
            $this->lines[$key]['quantidade'] = min(self::MAX_QTY_PER_LINE, $this->lines[$key]['quantidade'] + $quantity);
        } else {
            $this->lines[$key] = [
                'produto_id' => $productId,
                'cobertura_id' => $coveringId,
                'mensagem' => trim($message),
                'quantidade' => min(self::MAX_QTY_PER_LINE, $quantity),
            ];
        }
        return $key;
    }

    public function update(string $key, int $quantity): void
    {
        if (!isset($this->lines[$key])) {
            return;
        }
        if ($quantity <= 0) {
            unset($this->lines[$key]);
            return;
        }
        $this->lines[$key]['quantidade'] = min(self::MAX_QTY_PER_LINE, $quantity);
    }

    public function remove(string $key): void
    {
        unset($this->lines[$key]);
    }

    public function clear(): void
    {
        foreach (array_keys($this->lines) as $key) {
            unset($this->lines[$key]);
        }
    }

    /** @return array<string, array{produto_id:int, cobertura_id:?int, mensagem:string, quantidade:int}> */
    public function lines(): array
    {
        return $this->lines;
    }

    public function count(): int
    {
        return array_sum(array_column($this->lines, 'quantidade'));
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }
}
