<?php

declare(strict_types=1);

namespace App\Models;

/** Parâmetros de negócio (horários, valor mínimo, taxas) definidos pelo gerente. */
final class Setting extends Model
{
    public const DEFAULTS = [
        'hora_inicio' => '09:00',
        'hora_fim' => '18:00',
        'antecedencia_horas' => '2',
        'dias_maximos' => '30',
        'valor_minimo' => '50.00',
        'taxa_pedido_minimo' => '5.00',
        'taxa_urgencia' => '10.00',
    ];

    /** @return array<string, string> */
    public function values(): array
    {
        $values = self::DEFAULTS;
        foreach ($this->fetchAll('SELECT chave, valor FROM configuracoes') as $row) {
            $values[$row['chave']] = $row['valor'];
        }
        return $values;
    }

    public function withDescriptions(): array
    {
        return $this->fetchAll('SELECT * FROM configuracoes ORDER BY chave');
    }

    /** @param array<string, string> $values */
    public function save(array $values): void
    {
        foreach ($values as $key => $value) {
            if (array_key_exists($key, self::DEFAULTS)) {
                $this->execute('UPDATE configuracoes SET valor = ? WHERE chave = ?', [$value, $key]);
            }
        }
    }
}
