<h1>Categorias e coberturas</h1>
<div class="grid-2 align-start">
    <section class="card">
        <h2>Categorias</h2>
        <p class="hint">Aparecem como filtros na vitrine.</p>
        <ul class="simple-list">
            <?php foreach ($categories as $c): ?>
                <li>
                    <span><?= e($c['nome']) ?> <small class="muted">(<?= (int) $c['total_produtos'] ?> produto(s))</small></span>
                    <form method="post" action="<?= url('admin/categorias/' . $c['id'] . '/excluir') ?>" data-confirm="Excluir a categoria “<?= e($c['nome']) ?>”?">
                        <?= csrf_field() ?><button class="link-button danger">Excluir</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
        <form method="post" action="<?= url('admin/categorias') ?>" class="form inline-add" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="nome">Nova categoria</label>
                <input id="nome" name="nome" maxlength="50" value="<?= old($old, 'nome') ?>">
                <?= field_error($errors, 'nome') ?>
            </div>
            <button class="btn btn-small">Adicionar</button>
        </form>
    </section>

    <section class="card">
        <h2>Coberturas</h2>
        <p class="hint">Opções que o cliente escolhe ao personalizar o cupcake.</p>
        <ul class="simple-list">
            <?php foreach ($coverings as $c): ?>
                <li class="<?= (int) $c['ativa'] ? '' : 'row-muted' ?>">
                    <span><?= e($c['nome']) ?> <small class="muted">+ <?= money($c['preco_adicional']) ?><?= (int) $c['ativa'] ? '' : ' · inativa' ?></small></span>
                    <form method="post" action="<?= url('admin/coberturas/' . $c['id'] . '/alternar') ?>">
                        <?= csrf_field() ?><button class="link-button"><?= (int) $c['ativa'] ? 'Desativar' : 'Ativar' ?></button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
        <form method="post" action="<?= url('admin/coberturas') ?>" class="form inline-add" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="nome_cobertura">Nova cobertura</label>
                <input id="nome_cobertura" name="nome_cobertura" maxlength="50" value="<?= old($old, 'nome_cobertura') ?>">
                <?= field_error($errors, 'nome_cobertura') ?>
            </div>
            <div class="field">
                <label for="preco_adicional">Adicional (R$)</label>
                <input id="preco_adicional" name="preco_adicional" inputmode="decimal" value="<?= old($old, 'preco_adicional', '0,00') ?>">
                <?= field_error($errors, 'preco_adicional') ?>
            </div>
            <button class="btn btn-small">Adicionar</button>
        </form>
    </section>
</div>
