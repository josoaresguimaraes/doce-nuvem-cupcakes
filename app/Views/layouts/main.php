<?php

use App\Core\Auth;
use App\Core\Session;
use App\Services\Cart;

$user = Auth::user();
$cartCount = Cart::fromSession()->count();
$current = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(Session::csrfToken()) ?>">
    <title><?= e(($title ?? 'Cupcakes') . ' · Doce Nuvem Cupcakes') ?></title>
    <link rel="icon" href="<?= asset('img/logo.svg') ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body data-base="<?= e(url('/')) ?>">
<a class="skip-link" href="#conteudo">Pular para o conteúdo</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="<?= url('/') ?>">
            <img src="<?= asset('img/logo.svg') ?>" alt="" width="36" height="36">
            <span>Doce Nuvem <small>Cupcakes</small></span>
        </a>
        <button class="menu-toggle" aria-expanded="false" aria-controls="menu-principal">Menu</button>
        <nav id="menu-principal" class="main-nav" aria-label="Principal">
            <a href="<?= url('/') ?>" <?= $current === url('/') ? 'aria-current="page"' : '' ?>>Vitrine</a>
            <a href="<?= url('sobre') ?>" <?= $current === url('sobre') ? 'aria-current="page"' : '' ?>>Sobre nós</a>
            <?php if ($user): ?>
                <a href="<?= url('pedidos') ?>">Meus pedidos</a>
                <a href="<?= url('enderecos') ?>">Endereços</a>
                <?php if ($user['perfil'] === 'gerente'): ?>
                    <a href="<?= url('admin') ?>" class="nav-admin">Área do gerente</a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="<?= url('carrinho') ?>" class="nav-cart">
                Carrinho <span class="badge" aria-label="<?= $cartCount ?> itens"><?= $cartCount ?></span>
            </a>
            <?php if ($user): ?>
                <form method="post" action="<?= url('logout') ?>" class="inline-form">
                    <?= csrf_field() ?>
                    <button type="submit" class="link-button">Sair (<?= e(explode(' ', $user['nome'])[0]) ?>)</button>
                </form>
            <?php else: ?>
                <a href="<?= url('login') ?>">Entrar</a>
                <a href="<?= url('cadastro') ?>" class="btn btn-small">Criar conta</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main id="conteudo" class="container">
    <?= App\Core\View::partial('flash', ['messages' => Session::pullFlash()]) ?>
    <?= $content ?>
</main>

<footer class="site-footer">
    <div class="container">
        <p><a href="<?= url('sobre') ?>">Sobre nós e dúvidas frequentes</a></p>
        <p>Doce Nuvem Cupcakes · Projeto acadêmico (PIT II – Engenharia de Software). Pagamentos são simulados.</p>
    </div>
</footer>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
