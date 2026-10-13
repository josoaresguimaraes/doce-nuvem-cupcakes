<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\DeliveryScheduler;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

/** US06: seleção de data e horário de entrega. */
final class DeliverySchedulerTest extends TestCase
{
    private DeliveryScheduler $scheduler;
    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->scheduler = new DeliveryScheduler([
            'hora_inicio' => '09:00', 'hora_fim' => '18:00', 'antecedencia_horas' => '2', 'dias_maximos' => '30',
        ]);
        // Terça-feira, 13/10/2026, 13h20
        $this->now = new DateTimeImmutable('2026-10-13 13:20:00', new DateTimeZone('America/Sao_Paulo'));
    }

    public function testDiaSeguinteTemTodosOsHorariosDoExpediente(): void
    {
        $slots = $this->scheduler->availableSlots('2026-10-14', $this->now);
        self::assertSame(['09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'], $slots);
    }

    /** CA#2: impede horários indisponíveis (antecedência mínima de 2 h). */
    public function testMesmoDiaRespeitaAntecedencia(): void
    {
        self::assertSame(['16:00', '17:00'], $this->scheduler->availableSlots('2026-10-13', $this->now));
    }

    public function testDatasForaDoIntervaloNaoTemHorario(): void
    {
        self::assertSame([], $this->scheduler->availableSlots('2026-10-12', $this->now), 'data passada');
        self::assertSame([], $this->scheduler->availableSlots('2026-11-13', $this->now), 'mais de 30 dias');
        self::assertSame([], $this->scheduler->availableSlots('13/10/2026', $this->now), 'formato inválido');
        self::assertSame([], $this->scheduler->availableSlots('2026-02-30', $this->now), 'data inexistente');
    }

    public function testDepoisDoExpedienteNaoHaHorarioHoje(): void
    {
        $late = new DateTimeImmutable('2026-10-13 16:30:00', new DateTimeZone('America/Sao_Paulo'));
        self::assertSame([], $this->scheduler->availableSlots('2026-10-13', $late));
    }

    public function testValidacao(): void
    {
        self::assertNull($this->scheduler->validate('2026-10-14', '10:00', $this->now));
        self::assertStringContainsString('Horário indisponível', (string) $this->scheduler->validate('2026-10-13', '14:00', $this->now));
        self::assertStringContainsString('Horário indisponível', (string) $this->scheduler->validate('2026-10-14', '19:00', $this->now));
        self::assertNotNull($this->scheduler->validate('2026-10-01', '10:00', $this->now));
        self::assertNotNull($this->scheduler->validate('', '', $this->now));
    }

    /** RN#2: entrega no mesmo dia é urgente. */
    public function testUrgencia(): void
    {
        self::assertTrue($this->scheduler->isUrgent('2026-10-13', $this->now));
        self::assertFalse($this->scheduler->isUrgent('2026-10-14', $this->now));
    }
}
