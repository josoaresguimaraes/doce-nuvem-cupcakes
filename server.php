<?php

/**
 * Roteador para o servidor embutido do PHP (apenas desenvolvimento):
 *   php -S localhost:8000 -t public server.php
 */
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
if ($path !== '/' && is_file(__DIR__ . '/public' . $path)) {
    return false;
}
require __DIR__ . '/public/index.php';
