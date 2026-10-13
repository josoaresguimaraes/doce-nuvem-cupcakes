<?php
$editing = $product !== null;
$val = static fn (string $k, $default = '') => e($old[$k] ?? ($product[$k] ?? $default));
$checkedCats = isset($old['categorias']) ? array_map('intval', (array) $old['categorias']) : ($product['categoria_ids'] ?? []);
$active = $old ? !empty($old['ativo']) : (!$editing || (int) $product['ativo'] === 1);
?>
<nav class="breadcrumb"><a href="<?= url('admin/produtos') ?>">Produtos</a> › <?= $editing ? 'Editar' : 'Novo' ?></nav>
<section class="card form-card wide">
    <h1><?= $editing ? 'Editar “' . e($product['nome']) . '”' : 'Novo produto' ?></h1>
    <form method="post" enctype="multipart/form-data" class="form" novalidate
          action="<?= url($editing ? 'admin/produtos/' . $product['id'] : 'admin/produtos') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= App\Services\ImageUploader::MAX_BYTES ?>">
        <div class="field">
            <label for="nome">Nome</label>
            <input id="nome" name="nome" maxlength="80" value="<?= $val('nome') ?>" required>
            <?= field_error($errors, 'nome') ?>
        </div>
        <div class="field">
            <label for="descricao">Descrição curta</label>
            <textarea id="descricao" name="descricao" maxlength="100" rows="2" data-counter="descricao-contador" required><?= $val('descricao') ?></textarea>
            <p class="hint"><span id="descricao-contador">0</span>/100 caracteres</p>
            <?= field_error($errors, 'descricao') ?>
        </div>
        <div class="grid-2">
            <div class="field">
                <label for="preco">Preço (R$)</label>
                <input id="preco" name="preco" inputmode="decimal" placeholder="12,90" value="<?= isset($old['preco']) ? e($old['preco']) : ($editing ? e(number_format((float) $product['preco'], 2, ',', '')) : '') ?>" required>
                <?= field_error($errors, 'preco') ?>
            </div>
            <div class="field">
                <label for="estoque">Estoque (unidades)</label>
                <input id="estoque" name="estoque" type="number" min="0" value="<?= $val('estoque', '0') ?>" required>
                <p class="hint">Com estoque 0, o cupcake aparece como “indisponível”.</p>
                <?= field_error($errors, 'estoque') ?>
            </div>
        </div>
        <fieldset class="field">
            <legend>Categorias</legend>
            <div class="check-grid">
                <?php foreach ($categories as $c): ?>
                    <label class="check"><input type="checkbox" name="categorias[]" value="<?= (int) $c['id'] ?>" <?= in_array((int) $c['id'], $checkedCats, true) ? 'checked' : '' ?>> <?= e($c['nome']) ?></label>
                <?php endforeach; ?>
            </div>
            <?= field_error($errors, 'categorias') ?>
        </fieldset>
        <div class="field">
            <label for="imagem">Imagem <?= $editing ? '<span class="muted">(deixe em branco para manter a atual)</span>' : '' ?></label>
            <?php if ($editing): ?><img class="thumb" src="<?= e(product_image($product['imagem'])) ?>" alt="Imagem atual" width="96" height="96"><?php endif; ?>
            <input id="imagem" name="imagem" type="file" accept="image/jpeg,image/png" data-max-bytes="<?= App\Services\ImageUploader::MAX_BYTES ?>">
            <p class="hint">JPG ou PNG, até 2 MB.</p>
            <?= field_error($errors, 'imagem') ?>
        </div>
        <label class="check"><input type="checkbox" name="ativo" value="1" <?= $active ? 'checked' : '' ?>> Exibir na vitrine</label>
        <div class="actions">
            <button class="btn"><?= $editing ? 'Salvar alterações' : 'Cadastrar produto' ?></button>
            <a class="link-button" href="<?= url('admin/produtos') ?>">Cancelar</a>
        </div>
    </form>
</section>
