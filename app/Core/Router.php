<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Roteador simples: associa método + caminho a um "Controller@acao".
 * Suporta parâmetros numéricos, por exemplo /produto/{id}.
 */
final class Router
{
    /** @var array<int, array{method:string, pattern:string, handler:string, middleware:array<int,string>}> */
    private array $routes = [];

    public function get(string $path, string $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, string $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    private function add(string $method, string $path, string $handler, array $middleware): void
    {
        $pattern = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>\d+)', rtrim($path, '/') ?: '/') . '$#';
        $this->routes[] = compact('method', 'pattern', 'handler', 'middleware');
    }

    public function dispatch(Request $request): void
    {
        $pathMatched = false;
        foreach ($this->routes as $route) {
            if (!preg_match($route['pattern'], $request->path, $matches)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $request->method) {
                continue;
            }

            foreach ($route['middleware'] as $middleware) {
                Middleware::run($middleware, $request);
            }

            $params = array_map('intval', array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY));
            [$controllerName, $action] = explode('@', $route['handler']);
            $class = 'App\\Controllers\\' . $controllerName;
            $controller = new $class($request);
            $controller->{$action}(...array_values($params));
            return;
        }

        throw new HttpException($pathMatched ? 405 : 404);
    }
}
