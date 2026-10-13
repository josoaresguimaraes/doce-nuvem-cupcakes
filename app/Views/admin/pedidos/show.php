<?php use App\Models\Order; $next = Order::TRANSITIONS[$order['status']]; ?>
<nav class="breadcrumb"><a href="<?= url('admin') ?>">Pedidos</a> › #<?= (int) $order['id'] ?></nav>
<h1>Pedido #<?= (int) $order['id'] ?> · <?= e($order['cliente_nome']) ?></h1>
<p class="muted"><?= e($order['cliente_email']) ?> · feito em <?= e(format_date($order['criado_em'], true)) ?></p>

<section class="card">
    <h2>Status: <span class="status status-<?= e($order['status']) ?>"><?= e(status_label($order['status'])) ?></span></h2>
    <?= App\Core\View::partial('timeline', ['order' => $order, 'history' => $history]) ?>
    <div class="actions">
        <?php if ($order['status_pagamento'] === 'aguardando' && $order['status'] !== 'cancelado'): ?>
            <form method="post" action="<?= url('admin/pedidos/' . $order['id'] . '/pagamento') ?>">
                <?= csrf_field() ?>
                <button class="btn btn-secondary">Confirmar pagamento recebido</button>
            </form>
        <?php endif; ?>
        <?php foreach ($next as $status): ?>
            <form method="post" action="<?= url('admin/pedidos/' . $order['id'] . '/status') ?>" <?= $status === 'cancelado' ? 'data-confirm="Cancelar este pedido? Os itens voltam ao estoque."' : '' ?>>
                <?= csrf_field() ?>
                <input type="hidden" name="status" value="<?= e($status) ?>">
                <button class="btn <?= $status === 'cancelado' ? 'btn-danger' : '' ?>">Marcar como “<?= e(status_label($status)) ?>”</button>
            </form>
        <?php endforeach; ?>
        <?php if ($next === []): ?><p class="muted">Pedido finalizado.</p><?php endif; ?>
    </div>
</section>

<?= App\Core\View::partial('order-details', ['order' => $order, 'items' => $items]) ?>
