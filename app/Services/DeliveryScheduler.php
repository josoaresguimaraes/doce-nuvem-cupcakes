<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

/**
 * Agendamento de entrega (US06). Recebe "agora" como parâmetro para ser testável.
 *
 *  - Horários de hora em hora, entre o início e o fim do expediente (RN#1)
 *  - Antecedência mínima configurável (CA#2: impede horários indisponíveis)
 *  - Agendamento até N dias à frente
 *  - Entrega no mesmo dia é considerada urgente (RN#2: taxa adicional)
 */
final class DeliveryScheduler
{
    /** @param array<string, string> $settings */
    public function __construct(private readonly array $settings)
    {
    }

    /** @return string[] horários no formato HH:MM disponíveis para a data */
    public function availableSlots(string $date, DateTimeImmutable $now): array
    {
        $day = DateTimeImmutable::createFromFormat('!Y-m-d', $date, $now->getTimezone());
        if ($day === false || $day->format('Y-m-d') !== $date) {
            return [];
        }
        $today = $now->setTime(0, 0);
        $maxDays = (int) ($this->settings['dias_maximos'] ?? 30);
        if ($day < $today || $day > $today->modify("+{$maxDays} days")) {
            return [];
        }

        $startHour = (int) substr($this->settings['hora_inicio'] ?? '09:00', 0, 2);
        $endHour = (int) substr($this->settings['hora_fim'] ?? '18:00', 0, 2);
        $earliest = $now->modify('+' . (int) ($this->settings['antecedencia_horas'] ?? 2) . ' hours');

        $slots = [];
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            $slot = $day->setTime($hour, 0);
            if ($slot >= $earliest) {
                $slots[] = $slot->format('H:i');
            }
        }
        return $slots;
    }

    /** @return string|null mensagem de erro, ou null se válido */
    public function validate(string $date, string $time, DateTimeImmutable $now): ?string
    {
        if ($date === '' || $time === '') {
            return 'Escolha a data e o horário de entrega.';
        }
        $slots = $this->availableSlots($date, $now);
        if ($slots === []) {
            return 'Não há horários de entrega disponíveis nessa data. Escolha outra.';
        }
        if (!in_array($time, $slots, true)) {
            return sprintf(
                'Horário indisponível. Entregamos das %s às %s, com %s h de antecedência.',
                $this->settings['hora_inicio'] ?? '09:00',
                $this->settings['hora_fim'] ?? '18:00',
                $this->settings['antecedencia_horas'] ?? '2'
            );
        }
        return null;
    }

    public function isUrgent(string $date, DateTimeImmutable $now): bool
    {
        return $date === $now->format('Y-m-d');
    }
}
