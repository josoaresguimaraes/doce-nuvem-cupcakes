<h1>Meus endereços</h1>
<p class="muted">Você pode salvar até <?= (int) $max ?> endereços. Eles só aparecem para você.</p>

<div class="address-list">
    <?php foreach ($addresses as $a): ?>
        <article class="card address-card">
            <h2><?= e($a['apelido']) ?></h2>
            <p><?= e(App\Models\Address::format($a)) ?></p>
            <?php if ($a['zona']): ?>
                <p class="tag tag-ok">Entregamos aqui · taxa <?= money($a['zona']['taxa']) ?></p>
            <?php else: ?>
                <p class="tag tag-warn">Fora da área de entrega</p>
            <?php endif; ?>
            <div class="actions">
                <a class="btn btn-small btn-secondary" href="<?= url('enderecos/' . $a['id'] . '/editar') ?>">Editar</a>
                <form method="post" action="<?= url('enderecos/' . $a['id'] . '/excluir') ?>" data-confirm="Excluir o endereço “<?= e($a['apelido']) ?>”?">
                    <?= csrf_field() ?>
                    <button type="submit" class="link-button danger">Excluir</button>
                </form>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if ($addresses === []): ?>
        <p class="empty">Nenhum endereço salvo ainda.</p>
    <?php endif; ?>
</div>

<?php if (count($addresses) < $max): ?>
    <section class="card form-card wide" aria-labelledby="novo-endereco">
        <h2 id="novo-endereco">Novo endereço</h2>
        <form method="post" action="<?= url('enderecos') ?>" class="form" novalidate>
            <?= csrf_field() ?>
            <?php if ($return): ?><input type="hidden" name="voltar" value="checkout"><?php endif; ?>
            <?= App\Core\View::partial('address-fields', ['values' => $old, 'errors' => $errors]) ?>
            <button type="submit" class="btn">Salvar endereço</button>
        </form>
    </section>
<?php else: ?>
    <p class="notice">Você atingiu o limite de <?= (int) $max ?> endereços. Exclua um para cadastrar outro.</p>
<?php endif; ?>
