<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Cálculo do custo total do pedido (US04). Classe sem acesso a banco, para ser testada isoladamente.
 *
 * Regras:
 *  - subtotal = soma de quantidade × (preço do cupcake + adicional da cobertura)
 *  - taxa de entrega = taxa da zona do CEP (RN#1)
 *  - pedidos abaixo do valor mínimo pagam taxa extra (RN#2)
 *  - entregas no mesmo dia pagam taxa de urgência (US06 RN#2)
 */
final class PricingService
{
    /**
     * @param array<int, array{preco_unitario: float, quantidade: int}> $lines
     * @param array<string, string> $settings
     * @return array{subtotal: float, taxa_entrega: float, taxa_pedido_minimo: float, taxa_urgencia: float, total: float}
     */
    public function quote(array $lines, ?float $zoneFee, array $settings, bool $urgent): array
    {
        $subtotal = 0.0;
        foreach ($lines as $line) {
            $subtotal += round((float) $line['preco_unitario'] * (int) $line['quantidade'], 2);
        }
        $subtotal = round($subtotal, 2);

        $minimum = (float) ($settings['valor_minimo'] ?? 0);
        $minimumFee = ($subtotal > 0 && $subtotal < $minimum) ? (float) ($settings['taxa_pedido_minimo'] ?? 0) : 0.0;
        $urgencyFee = $urgent ? (float) ($settings['taxa_urgencia'] ?? 0) : 0.0;
        $delivery = $zoneFee ?? 0.0;

        return [
            'subtotal' => $subtotal,
            'taxa_entrega' => round($delivery, 2),
            'taxa_pedido_minimo' => round($minimumFee, 2),
            'taxa_urgencia' => round($urgencyFee, 2),
            'total' => round($subtotal + $delivery + $minimumFee + $urgencyFee, 2),
        ];
    }

    /** Preço de uma unidade personalizada. */
    public static function unitPrice(float $productPrice, ?float $coveringExtra): float
    {
        return round($productPrice + ($coveringExtra ?? 0.0), 2);
    }
}
