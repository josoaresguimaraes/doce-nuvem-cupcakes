<?php

declare(strict_types=1);

namespace App\Core;

/** Classe base dos controladores (camada Controller do MVC). */
abstract class Controller
{
    public function __construct(protected Request $request)
    {
    }

    /** Renderiza uma view dentro do layout escolhido. */
    protected function view(string $view, array $data = [], string $layout = 'layouts/main'): void
    {
        echo View::render($view, $data, $layout);
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /** Volta ao formulário com erros e dados digitados. */
    protected function backWithErrors(string $to, array $errors): never
    {
        Session::withErrors($errors, $this->request->all());
        Session::flash('error', 'Verifique os campos destacados.');
        redirect($to);
    }
}
