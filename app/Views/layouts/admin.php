<?php

use App\Core\Auth;
use App\Core\Session;

$user = Auth::user();
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$active = static fn (string $prefix): string => str_starts_with((string) $current, url($prefix)) ? 'aria-current="page"' : '';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Session::csrfToken()) ?>">
    <title><?= e(($title ?? 'Gerência') . ' · Área do gerente') ?></title>
    <link rel="icon" href="<?= asset('img/logo.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="admin" data-base="<?= e(url('/')) ?>">
<header class="site-header admin-header">
    <div class="container header-inner">
        <a class="brand" href="<?= url('admin') ?>">
            <img src="<?= asset('img/logo.svg') ?>" alt="" width="36" height="36">
            <span>Doce Nuvem <small>Área do gerente</small></span>
        </a>
        <button class="menu-toggle" aria-expanded="false" aria-controls="menu-admin">Menu</button>
        <nav id="menu-admin" class="main-nav" aria-label="Gerência">
            <a href="<?= url('admin') ?>" <?= ($current === url('admin') || str_starts_with((string) $current, url('admin/pedidos'))) ? 'aria-current="page"' : '' ?>>Pedidos</a>
            <a href="<?= url('admin/produtos') ?>" <?= $active('admin/produtos') ?>>Produtos</a>
            <a href="<?= url('admin/categorias') ?>" <?= $active('admin/categorias') ?>>Categorias e coberturas</a>
            <a href="<?= url('admin/relatorios') ?>" <?= $active('admin/relatorios') ?>>Relatórios</a>
            <a href="<?= url('admin/configuracoes') ?>" <?= $active('admin/configuracoes') ?>>Configurações</a>
            <a href="<?= url('/') ?>">Ver loja</a>
            <form method="post" action="<?= url('logout') ?>" class="inline-form">
                <?= csrf_field() ?>
                <button type="submit" class="link-button">Sair (<?= e(explode(' ', $user['nome'] ?? '')[0]) ?>)</button>
            </form>
        </nav>
    </div>
</header>
<main id="conteudo" class="container">
    <?= App\Core\View::partial('flash', ['messages' => Session::pullFlash()]) ?>
    <?= $content ?>
</main>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
