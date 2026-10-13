<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\PricingService;
use PHPUnit\Framework\TestCase;

/** US04: cálculo do custo total do pedido. */
final class PricingServiceTest extends TestCase
{
    private const SETTINGS = ['valor_minimo' => '50.00', 'taxa_pedido_minimo' => '5.00', 'taxa_urgencia' => '10.00'];

    public function testSomaItensEEntrega(): void
    {
        $lines = [
            ['preco_unitario' => 14.40, 'quantidade' => 6], // 86,40
            ['preco_unitario' => 9.90, 'quantidade' => 2],  // 19,80
        ];
        $q = (new PricingService())->quote($lines, 8.00, self::SETTINGS, false);

        self::assertSame(106.2, $q['subtotal']);
        self::assertSame(8.0, $q['taxa_entrega']);
        self::assertSame(0.0, $q['taxa_pedido_minimo']);
        self::assertSame(0.0, $q['taxa_urgencia']);
        self::assertSame(114.2, $q['total']);
    }

    /** RN#2: pedidos abaixo do mínimo pagam taxa extra. */
    public function testTaxaDePedidoMinimo(): void
    {
        $service = new PricingService();
        $below = $service->quote([['preco_unitario' => 9.90, 'quantidade' => 5]], 8.0, self::SETTINGS, false); // 49,50
        $exact = $service->quote([['preco_unitario' => 10.00, 'quantidade' => 5]], 8.0, self::SETTINGS, false); // 50,00

        self::assertSame(5.0, $below['taxa_pedido_minimo']);
        self::assertSame(62.5, $below['total']);
        self::assertSame(0.0, $exact['taxa_pedido_minimo'], 'no limite exato não há taxa');
    }

    /** US06 RN#2: entrega no mesmo dia paga taxa de urgência. */
    public function testTaxaDeUrgencia(): void
    {
        $q = (new PricingService())->quote([['preco_unitario' => 20.0, 'quantidade' => 3]], 10.0, self::SETTINGS, true);
        self::assertSame(10.0, $q['taxa_urgencia']);
        self::assertSame(80.0, $q['total']);
    }

    public function testSemEnderecoNaoCobraEntrega(): void
    {
        $q = (new PricingService())->quote([['preco_unitario' => 30.0, 'quantidade' => 2]], null, self::SETTINGS, false);
        self::assertSame(0.0, $q['taxa_entrega']);
        self::assertSame(60.0, $q['total']);
    }

    public function testCarrinhoVazioNaoCobraTaxaMinima(): void
    {
        $q = (new PricingService())->quote([], 8.0, self::SETTINGS, false);
        self::assertSame(0.0, $q['taxa_pedido_minimo']);
    }

    public function testPrecoUnitarioComCobertura(): void
    {
        self::assertSame(14.4, PricingService::unitPrice(11.90, 2.50));
        self::assertSame(11.9, PricingService::unitPrice(11.90, null));
    }
}
