<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Session;

/** Escapa texto para HTML (proteção contra XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Gera uma URL respeitando o BASE_PATH configurado. */
function url(string $path = '/'): string
{
    $base = rtrim((string) Config::get('BASE_PATH', ''), '/');
    return $base . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

/** Endereço público de uma imagem de produto (seed em assets ou upload). */
function product_image(?string $image): string
{
    if ($image === null || $image === '') {
        return asset('img/produtos/sem-imagem.svg');
    }
    return str_starts_with($image, 'uploads/') ? url($image) : asset('img/produtos/' . $image);
}

function redirect(string $path): never
{
    header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)));
    exit;
}

function money(float|int|string $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Session::csrfToken()) . '">';
}

function old(array $old, string $key, mixed $default = ''): string
{
    return e($old[$key] ?? $default);
}

function field_error(array $errors, string $key): string
{
    return isset($errors[$key])
        ? '<p class="field-error" role="alert">' . e($errors[$key]) . '</p>'
        : '';
}

function format_cep(string $cep): string
{
    return strlen($cep) === 8 ? substr($cep, 0, 5) . '-' . substr($cep, 5) : $cep;
}

function format_date(?string $date, bool $withTime = false): string
{
    if (!$date) {
        return '';
    }
    $ts = strtotime($date);
    return $ts ? date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $ts) : $date;
}

function status_label(string $status): string
{
    return \App\Models\Order::STATUS_LABELS[$status] ?? $status;
}

function payment_label(string $method): string
{
    return \App\Models\Order::PAYMENT_LABELS[$method] ?? $method;
}
