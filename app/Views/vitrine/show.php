<?php $available = (int) $product['estoque'] > 0; $max = min(50, (int) $product['estoque']); ?>
<nav class="breadcrumb" aria-label="Você está em"><a href="<?= url('/') ?>">Vitrine</a> › <?= e($product['nome']) ?></nav>

<article class="product-detail">
    <div class="product-detail-image">
        <img src="<?= e(product_image($product['imagem'])) ?>" alt="Cupcake <?= e($product['nome']) ?>" width="420" height="420">
    </div>
    <div class="product-detail-body">
        <h1><?= e($product['nome']) ?></h1>
        <p class="lead"><?= e($product['descricao']) ?></p>
        <p class="price price-big" id="unit-price" data-base="<?= e($product['preco']) ?>"><?= money($product['preco']) ?></p>

        <?php if (!$available): ?>
            <p class="notice notice-warning">Este sabor está indisponível no momento. Volte em breve!</p>
        <?php else: ?>
            <p class="muted"><?= (int) $product['estoque'] ?> unidade(s) disponível(is)</p>
            <form method="post" action="<?= url('carrinho/adicionar') ?>" class="card form" id="add-to-cart" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="produto_id" value="<?= (int) $product['id'] ?>">

                <div class="field">
                    <label for="quantidade">Quantidade</label>
                    <input type="number" id="quantidade" name="quantidade" min="1" max="<?= $max ?>" value="<?= old($old, 'quantidade', 1) ?>" required>
                    <?= field_error($errors, 'quantidade') ?>
                </div>

                <fieldset class="field">
                    <legend>Cobertura</legend>
                    <label class="radio"><input type="radio" name="cobertura_id" value="" data-extra="0" <?= empty($old['cobertura_id']) ? 'checked' : '' ?>> Padrão da receita (sem custo)</label>
                    <?php foreach ($coverings as $c): ?>
                        <label class="radio">
                            <input type="radio" name="cobertura_id" value="<?= (int) $c['id'] ?>" data-extra="<?= e($c['preco_adicional']) ?>" <?= (int) ($old['cobertura_id'] ?? 0) === (int) $c['id'] ? 'checked' : '' ?>>
                            <?= e($c['nome']) ?> <span class="muted">(+ <?= money($c['preco_adicional']) ?>)</span>
                        </label>
                    <?php endforeach; ?>
                    <?= field_error($errors, 'cobertura_id') ?>
                </fieldset>

                <div class="field">
                    <label for="mensagem">Mensagem no topper <span class="muted">(opcional)</span></label>
                    <input type="text" id="mensagem" name="mensagem" maxlength="50" value="<?= old($old, 'mensagem') ?>"
                           pattern="[A-Za-zÀ-ÿ0-9 .,!?'\-:\(\)&amp;]*" title="Use <?= e(App\Services\Validator::MESSAGE_RULE_TEXT) ?>" aria-describedby="mensagem-ajuda" placeholder="Ex.: Feliz aniversário, Ana!">
                    <p id="mensagem-ajuda" class="hint"><span id="mensagem-contador">0</span>/50 · <?= e(App\Services\Validator::MESSAGE_RULE_TEXT) ?></p>
                    <?= field_error($errors, 'mensagem') ?>
                </div>

                <p class="line-total">Total deste item: <strong id="line-total"><?= money($product['preco']) ?></strong></p>
                <div class="actions">
                    <button type="submit" class="btn">Adicionar ao carrinho</button>
                    <button type="submit" name="continuar" value="1" class="btn btn-secondary">Adicionar e continuar comprando</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</article>
