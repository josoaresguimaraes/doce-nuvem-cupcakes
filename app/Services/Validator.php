<?php

declare(strict_types=1);

namespace App\Services;

/** Regras de validação reutilizáveis (funções puras, cobertas por testes unitários). */
final class Validator
{
    public const MESSAGE_MAX = 50;
    public const DESCRIPTION_MAX = 100;

    public static function email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && mb_strlen($email) <= 150;
    }

    /** US11 RN#1: mínimo de 8 caracteres, com letras e números. */
    public static function password(string $password): bool
    {
        return mb_strlen($password) >= 8
            && preg_match('/\p{L}/u', $password) === 1
            && preg_match('/\d/', $password) === 1;
    }

    /** Remove tudo que não é dígito. */
    public static function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    /** US15 CA#2: CEP com 8 dígitos (aceita "18035-000" ou "18035000"). */
    public static function cep(string $cep): bool
    {
        return preg_match('/^\d{5}-?\d{3}$/', trim($cep)) === 1;
    }

    /** US03 RN#1: mensagem opcional, até 50 caracteres alfanuméricos (letras acentuadas e espaços permitidos). */
    public static function cupcakeMessage(string $message): bool
    {
        return $message === ''
            || (mb_strlen($message) <= self::MESSAGE_MAX && preg_match('/^[\p{L}\p{N} ]+$/u', $message) === 1);
    }

    public static function length(string $value, int $min, int $max): bool
    {
        $len = mb_strlen(trim($value));
        return $len >= $min && $len <= $max;
    }

    /** Converte "12,90" ou "12.90" em float; retorna null se inválido. */
    public static function money(string $value): ?float
    {
        $normalized = trim($value);
        if (str_contains($normalized, ',')) {
            // formato brasileiro: 1.234,56
            $normalized = str_replace(',', '.', str_replace('.', '', $normalized));
        }
        return preg_match('/^\d+(\.\d{1,2})?$/', $normalized) === 1 ? (float) $normalized : null;
    }

    public static function uf(string $uf): bool
    {
        $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE',
            'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
        return in_array(strtoupper($uf), $ufs, true);
    }

    /**
     * Valida os campos de um endereço e devolve [dadosNormalizados, erros].
     * @return array{0: array<string,string>, 1: array<string,string>}
     */
    public static function address(array $input): array
    {
        $data = [
            'apelido' => trim((string) ($input['apelido'] ?? '')),
            'cep' => self::digits((string) ($input['cep'] ?? '')),
            'logradouro' => trim((string) ($input['logradouro'] ?? '')),
            'numero' => trim((string) ($input['numero'] ?? '')),
            'complemento' => trim((string) ($input['complemento'] ?? '')),
            'bairro' => trim((string) ($input['bairro'] ?? '')),
            'cidade' => trim((string) ($input['cidade'] ?? '')),
            'uf' => strtoupper(trim((string) ($input['uf'] ?? ''))),
        ];
        $errors = [];
        if (!self::length($data['apelido'], 2, 30)) {
            $errors['apelido'] = 'Informe um apelido de 2 a 30 caracteres (ex.: Casa, Trabalho).';
        }
        if (!self::cep((string) ($input['cep'] ?? ''))) {
            $errors['cep'] = 'CEP inválido. Use 8 dígitos, por exemplo 18035-000.';
        }
        if (!self::length($data['logradouro'], 3, 120)) {
            $errors['logradouro'] = 'Informe a rua ou avenida.';
        }
        if (!self::length($data['numero'], 1, 10)) {
            $errors['numero'] = 'Informe o número (ou "s/n").';
        }
        if (mb_strlen($data['complemento']) > 60) {
            $errors['complemento'] = 'O complemento pode ter no máximo 60 caracteres.';
        }
        if (!self::length($data['bairro'], 2, 60)) {
            $errors['bairro'] = 'Informe o bairro.';
        }
        if (!self::length($data['cidade'], 2, 60)) {
            $errors['cidade'] = 'Informe a cidade.';
        }
        if (!self::uf($data['uf'])) {
            $errors['uf'] = 'Selecione uma UF válida.';
        }
        return [$data, $errors];
    }
}
