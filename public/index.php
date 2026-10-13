<?php

declare(strict_types=1);

/**
 * Front controller: toda requisição passa por aqui.
 * Erros são tratados de forma amigável e registrados em storage/logs/app.log.
 */

use App\Core\Config;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Router;
use App\Core\Session;
use App\Core\View;

require dirname(__DIR__) . '/app/bootstrap.php';

Session::start();
$request = Request::fromGlobals();

try {
    $router = new Router();
    require ROOT_PATH . '/config/routes.php';
    $router->dispatch($request);
} catch (HttpException $e) {
    http_response_code($e->status);
    if ($request->wantsJson()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
    } else {
        echo View::render('errors/http', ['status' => $e->status, 'message' => $e->getMessage()]);
    }
} catch (Throwable $e) {
    $log = sprintf("[%s] %s: %s em %s:%d\n%s\n\n", date('Y-m-d H:i:s'), get_class($e), $e->getMessage(), $e->getFile(), $e->getLine(), $e->getTraceAsString());
    @file_put_contents(ROOT_PATH . '/storage/logs/app.log', $log, FILE_APPEND);

    http_response_code(500);
    $message = Config::bool('APP_DEBUG') ? $e->getMessage() : 'Algo deu errado do nosso lado. Já registramos o problema; tente novamente em instantes.';
    if ($request->wantsJson()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => $message], JSON_UNESCAPED_UNICODE);
    } else {
        try {
            echo View::render('errors/http', ['status' => 500, 'message' => $message]);
        } catch (Throwable) {
            echo '<h1>Erro 500</h1><p>' . e($message) . '</p>';
        }
    }
}
