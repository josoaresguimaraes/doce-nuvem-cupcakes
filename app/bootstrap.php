<?php

declare(strict_types=1);

/**
 * Inicialização da aplicação: autoloader, configuração, sessão e tratamento de erros.
 * Não depende do Composer em produção (o Composer é usado apenas para o PHPUnit).
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file = APP_PATH . '/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require APP_PATH . '/Core/helpers.php';

App\Core\Config::load(ROOT_PATH . '/.env');

date_default_timezone_set(App\Core\Config::get('APP_TIMEZONE', 'America/Sao_Paulo'));
mb_internal_encoding('UTF-8');
