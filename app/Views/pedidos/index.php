<h1>Meus pedidos</h1>

<?php if ($orders === []): ?>
    <div class="empty card">
        <p>Você ainda não fez nenhum pedido.</p>
        <a class="btn" href="<?= url('/') ?>">Ver a vitrine</a>
    </div>
<?php else: ?>
    <table class="table">
        <thead>
        <tr><th scope="col">Pedido</th><th scope="col">Feito em</th><th scope="col">Entrega</th><th scope="col">Itens</th><th scope="col">Total</th><th scope="col">Status</th><th scope="col"><span class="sr-only">Ação</span></th></tr>
        </thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td data-label="Pedido">#<?= (int) $o['id'] ?></td>
                <td data-label="Feito em"><?= e(format_date($o['criado_em'], true)) ?></td>
                <td data-label="Entrega"><?= e(format_date($o['data_entrega'])) ?> às <?= e(substr($o['hora_entrega'], 0, 5)) ?></td>
                <td data-label="Itens"><?= (int) $o['total_itens'] ?></td>
                <td data-label="Total"><?= money($o['total']) ?></td>
                <td data-label="Status"><span class="status status-<?= e($o['status']) ?>"><?= e(status_label($o['status'])) ?></span></td>
                <td><a href="<?= url('pedidos/' . $o['id']) ?>">Acompanhar</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
