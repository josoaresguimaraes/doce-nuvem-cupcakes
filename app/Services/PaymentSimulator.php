<?php

declare(strict_types=1);

namespace App\Services;

use DateTimeImmutable;

/**
 * Simulação de pagamento (US05). NÃO há integração com operadora real: o objetivo
 * acadêmico é demonstrar o fluxo, as validações e o tratamento de erros.
 *
 *  - Cartão: valida número (algoritmo de Luhn), validade e CVV. Guarda só os 4 últimos dígitos.
 *            O cartão de teste 4000 0000 0000 0002 simula recusa da operadora.
 *  - Pix:    gera código "copia e cola" fictício com validade de 10 minutos (RN#1).
 *  - Boleto: gera linha digitável fictícia com vencimento em 2 dias úteis (RN#2).
 */
final class PaymentSimulator
{
    public const DECLINED_TEST_CARD = '4000000000000002';

    /**
     * @return array{ok: bool, erro?: string, campo?: string, status_pagamento?: string,
     *               codigo_pagamento?: ?string, cartao_final?: ?string, vencimento_pagamento?: ?string}
     */
    public function process(string $method, array $input, float $amount, DateTimeImmutable $now): array
    {
        return match ($method) {
            'cartao' => $this->card($input, $now),
            'pix' => [
                'ok' => true,
                'status_pagamento' => 'aguardando',
                'codigo_pagamento' => $this->fakePixCode($amount),
                'cartao_final' => null,
                'vencimento_pagamento' => $now->modify('+10 minutes')->format('Y-m-d H:i:s'),
            ],
            'boleto' => [
                'ok' => true,
                'status_pagamento' => 'aguardando',
                'codigo_pagamento' => $this->fakeBoletoLine(),
                'cartao_final' => null,
                'vencimento_pagamento' => self::addBusinessDays($now, 2)->format('Y-m-d 23:59:59'),
            ],
            default => ['ok' => false, 'campo' => 'metodo_pagamento', 'erro' => 'Escolha uma forma de pagamento.'],
        };
    }

    private function card(array $input, DateTimeImmutable $now): array
    {
        $number = Validator::digits((string) ($input['numero_cartao'] ?? ''));
        $name = trim((string) ($input['nome_cartao'] ?? ''));
        $expiry = trim((string) ($input['validade_cartao'] ?? ''));
        $cvv = Validator::digits((string) ($input['cvv'] ?? ''));

        if (!self::luhn($number)) {
            return ['ok' => false, 'campo' => 'numero_cartao', 'erro' => 'Número de cartão inválido.'];
        }
        if (!Validator::length($name, 3, 60)) {
            return ['ok' => false, 'campo' => 'nome_cartao', 'erro' => 'Informe o nome impresso no cartão.'];
        }
        if (!self::validExpiry($expiry, $now)) {
            return ['ok' => false, 'campo' => 'validade_cartao', 'erro' => 'Validade inválida ou cartão vencido (use MM/AA).'];
        }
        if (preg_match('/^\d{3,4}$/', $cvv) !== 1) {
            return ['ok' => false, 'campo' => 'cvv', 'erro' => 'CVV deve ter 3 ou 4 dígitos.'];
        }
        if ($number === self::DECLINED_TEST_CARD) {
            return ['ok' => false, 'campo' => 'numero_cartao', 'erro' => 'Pagamento recusado pela operadora. Tente outro cartão.'];
        }

        return [
            'ok' => true,
            'status_pagamento' => 'aprovado',
            'codigo_pagamento' => 'AUT-' . strtoupper(bin2hex(random_bytes(4))),
            'cartao_final' => substr($number, -4),
            'vencimento_pagamento' => null,
        ];
    }

    public static function luhn(string $number): bool
    {
        if (preg_match('/^\d{13,19}$/', $number) !== 1) {
            return false;
        }
        $sum = 0;
        $double = false;
        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $digit = (int) $number[$i];
            if ($double) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $double = !$double;
        }
        return $sum % 10 === 0;
    }

    public static function validExpiry(string $expiry, DateTimeImmutable $now): bool
    {
        if (preg_match('/^(0[1-9]|1[0-2])\/?(\d{2})$/', $expiry, $m) !== 1) {
            return false;
        }
        $lastDay = DateTimeImmutable::createFromFormat('!Y-m-d', '20' . $m[2] . '-' . $m[1] . '-01')
            ->modify('last day of this month')->setTime(23, 59, 59);
        return $lastDay >= $now;
    }

    public static function addBusinessDays(DateTimeImmutable $date, int $days): DateTimeImmutable
    {
        while ($days > 0) {
            $date = $date->modify('+1 day');
            if ((int) $date->format('N') < 6) {
                $days--;
            }
        }
        return $date;
    }

    private function fakePixCode(float $amount): string
    {
        return sprintf('00020126PIXSIMULADO%s5204000053039865406%.2f5802BR', strtoupper(bin2hex(random_bytes(6))), $amount);
    }

    private function fakeBoletoLine(): string
    {
        $digits = '';
        for ($i = 0; $i < 47; $i++) {
            $digits .= (string) random_int(0, 9);
        }
        return vsprintf('%s.%s %s.%s %s.%s %s %s', [
            substr($digits, 0, 5), substr($digits, 5, 5), substr($digits, 10, 5), substr($digits, 15, 6),
            substr($digits, 21, 5), substr($digits, 26, 6), substr($digits, 32, 1), substr($digits, 33, 14),
        ]);
    }
}
