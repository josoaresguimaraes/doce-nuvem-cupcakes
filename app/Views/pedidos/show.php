<nav class="breadcrumb"><a href="<?= url('pedidos') ?>">Meus pedidos</a> › Pedido #<?= (int) $order['id'] ?></nav>

<?php if ($isNew): ?>
    <div class="card success-banner">
        <h1>Pedido #<?= (int) $order['id'] ?> confirmado! 🧁</h1>
        <p>Você pode acompanhar cada etapa por aqui. A página se atualiza sozinha.</p>
    </div>
<?php else: ?>
    <h1>Pedido #<?= (int) $order['id'] ?></h1>
<?php endif; ?>

<section class="card" aria-labelledby="titulo-status" id="order-tracker"
         data-status-url="<?= e(url('api/pedidos/' . $order['id'] . '/status')) ?>" data-status="<?= e($order['status']) ?>">
    <h2 id="titulo-status">Status: <span id="status-label" class="status status-<?= e($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></h2>
    <?= App\Core\View::partial('timeline', ['order' => $order, 'history' => $history]) ?>
    <p class="hint" id="tracker-hint" aria-live="polite">Atualização automática a cada 5 segundos.</p>
</section>

<?= App\Core\View::partial('order-details', ['order' => $order, 'items' => $items]) ?>
