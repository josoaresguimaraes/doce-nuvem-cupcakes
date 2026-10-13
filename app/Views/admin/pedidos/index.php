<?php use App\Models\Order; ?>
<div class="page-head">
    <h1>Pedidos</h1>
    <form method="get" action="<?= url('admin') ?>" class="inline-filter">
        <label for="f-status">Status</label>
        <select id="f-status" name="status" data-autosubmit>
            <option value="">Todos</option>
            <?php foreach (Order::STATUS_LABELS as $key => $label): ?>
                <option value="<?= e($key) ?>" <?= $status === $key ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <noscript><button class="btn btn-small">Filtrar</button></noscript>
    </form>
</div>

<?php if ($orders === []): ?>
    <p class="empty">Nenhum pedido encontrado.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th scope="col">#</th><th scope="col">Cliente</th><th scope="col">Entrega</th><th scope="col">Total</th><th scope="col">Pagamento</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Ação</span></th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td data-label="#"><?= (int) $o['id'] ?></td>
                <td data-label="Cliente"><?= e($o['cliente_nome']) ?></td>
                <td data-label="Entrega"><?= e(format_date($o['data_entrega'])) ?> <?= e(substr($o['hora_entrega'], 0, 5)) ?></td>
                <td data-label="Total"><?= money($o['total']) ?></td>
                <td data-label="Pagamento"><?= e(payment_label($o['metodo_pagamento'])) ?> · <span class="status status-pag-<?= e($o['status_pagamento']) ?>"><?= e(Order::PAYMENT_STATUS_LABELS[$o['status_pagamento']]) ?></span></td>
                <td data-label="Status"><span class="status status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
                <td><a href="<?= url('admin/pedidos/' . $o['id']) ?>">Gerenciar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
