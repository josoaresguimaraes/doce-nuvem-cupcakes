<?php

declare(strict_types=1);

namespace App\Models;

/** Faixas de CEP atendidas e taxa de entrega de cada uma (US04 RN#1). */
final class DeliveryZone extends Model
{
    public function findByCep(string $cep): ?array
    {
        return $this->fetch(
            'SELECT * FROM zonas_entrega WHERE ? BETWEEN cep_inicio AND cep_fim ORDER BY taxa LIMIT 1',
            [$cep]
        );
    }

    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM zonas_entrega ORDER BY cep_inicio');
    }
}
