<?php

declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

// Autoload das classes de teste (namespace Tests\)
spl_autoload_register(static function (string $class): void {
    if (str_starts_with($class, 'Tests\\')) {
        $file = __DIR__ . '/' . str_replace('\\', '/', substr($class, 6)) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});
