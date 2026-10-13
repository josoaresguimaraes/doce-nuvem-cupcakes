<?php

declare(strict_types=1);

namespace App\Core;

/** Camada View do MVC: arquivos PHP simples em app/Views. */
final class View
{
    public static function render(string $view, array $data = [], ?string $layout = 'layouts/main'): string
    {
        $data['errors'] ??= Session::pullErrors();
        $data['old'] ??= Session::pullOld();
        $content = self::renderFile($view, $data);

        if ($layout === null) {
            return $content;
        }
        return self::renderFile($layout, $data + ['content' => $content]);
    }

    private static function renderFile(string $view, array $data): string
    {
        $file = APP_PATH . '/Views/' . $view . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        try {
            require $file;
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }

    public static function partial(string $view, array $data = []): string
    {
        return self::renderFile('partials/' . $view, $data);
    }
}
