<nav class="breadcrumb"><a href="<?= url('enderecos') ?>">Meus endereços</a> › Editar</nav>
<section class="card form-card wide">
    <h1>Editar endereço</h1>
    <form method="post" action="<?= url('enderecos/' . $address['id']) ?>" class="form" novalidate>
        <?= csrf_field() ?>
        <?= App\Core\View::partial('address-fields', ['values' => $old ?: $address, 'errors' => $errors]) ?>
        <div class="actions">
            <button type="submit" class="btn">Salvar alterações</button>
            <a href="<?= url('enderecos') ?>" class="link-button">Cancelar</a>
        </div>
    </form>
</section>
