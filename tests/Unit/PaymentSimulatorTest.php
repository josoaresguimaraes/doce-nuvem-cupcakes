<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\PaymentSimulator;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/** US05: métodos de pagamento (simulados). */
final class PaymentSimulatorTest extends TestCase
{
    private PaymentSimulator $payments;
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->payments = new PaymentSimulator();
        $this->now = new DateTimeImmutable('2026-10-16 10:00:00'); // sexta-feira
    }

    private function card(array $override = []): array
    {
        return $override + ['numero_cartao' => '4111 1111 1111 1111', 'nome_cartao' => 'ANA SILVA', 'validade_cartao' => '12/30', 'cvv' => '123'];
    }

    public function testAlgoritmoDeLuhn(): void
    {
        self::assertTrue(PaymentSimulator::luhn('4111111111111111'));
        self::assertTrue(PaymentSimulator::luhn('5555555555554444'));
        self::assertFalse(PaymentSimulator::luhn('4111111111111112'));
        self::assertFalse(PaymentSimulator::luhn('1234'));
    }

    /** CA#3: dado sensível não é guardado — apenas os 4 últimos dígitos. */
    public function testCartaoAprovadoGuardaSomenteFinal(): void
    {
        $r = $this->payments->process('cartao', $this->card(), 94.40, $this->now);
        self::assertTrue($r['ok']);
        self::assertSame('aprovado', $r['status_pagamento']);
        self::assertSame('1111', $r['cartao_final']);
        self::assertStringNotContainsString('4111111111111111', json_encode($r));
    }

    public function testCartaoRecusadoPelaOperadora(): void
    {
        $r = $this->payments->process('cartao', $this->card(['numero_cartao' => '4000 0000 0000 0002']), 50, $this->now);
        self::assertFalse($r['ok']);
        self::assertStringContainsString('recusado', $r['erro']);
    }

    public function testCartaoComDadosInvalidos(): void
    {
        self::assertSame('numero_cartao', $this->payments->process('cartao', $this->card(['numero_cartao' => '4111 1111 1111 1112']), 50, $this->now)['campo']);
        self::assertSame('validade_cartao', $this->payments->process('cartao', $this->card(['validade_cartao' => '09/26']), 50, $this->now)['campo'], 'vencido');
        self::assertSame('validade_cartao', $this->payments->process('cartao', $this->card(['validade_cartao' => '13/30']), 50, $this->now)['campo'], 'mês inválido');
        self::assertSame('cvv', $this->payments->process('cartao', $this->card(['cvv' => '12']), 50, $this->now)['campo']);
        self::assertSame('nome_cartao', $this->payments->process('cartao', $this->card(['nome_cartao' => '']), 50, $this->now)['campo']);
    }

    public function testCartaoValidoAteOFimDoMesDeValidade(): void
    {
        self::assertTrue(PaymentSimulator::validExpiry('10/26', $this->now));
        self::assertFalse(PaymentSimulator::validExpiry('09/26', $this->now));
    }

    /** RN#1: Pix confirmado em até 10 minutos. */
    public function testPixGeraCodigoComValidadeDeDezMinutos(): void
    {
        $r = $this->payments->process('pix', [], 42.80, $this->now);
        self::assertTrue($r['ok']);
        self::assertSame('aguardando', $r['status_pagamento']);
        self::assertSame('2026-10-16 10:10:00', $r['vencimento_pagamento']);
        self::assertStringContainsString('42.80', $r['codigo_pagamento']);
    }

    /** RN#2: boleto vence em 2 dias úteis (sexta → terça). */
    public function testBoletoVenceEmDoisDiasUteis(): void
    {
        $r = $this->payments->process('boleto', [], 80, $this->now);
        self::assertTrue($r['ok']);
        self::assertSame('2026-10-20 23:59:59', $r['vencimento_pagamento']);
        self::assertMatchesRegularExpression('/^\d{5}\.\d{5} \d{5}\.\d{6} \d{5}\.\d{6} \d \d{14}$/', $r['codigo_pagamento']);
    }

    public function testMetodoInexistente(): void
    {
        self::assertFalse($this->payments->process('cheque', [], 10, $this->now)['ok']);
    }
}
