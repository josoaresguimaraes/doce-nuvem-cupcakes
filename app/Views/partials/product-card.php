<?php $available = (int) $p['estoque'] > 0; ?>
<article class="product-card<?= $available ? '' : ' is-unavailable' ?>">
    <a href="<?= url('produto/' . $p['id']) ?>" class="product-link">
        <div class="product-image">
            <img src="<?= e(product_image($p['imagem'])) ?>" alt="Cupcake <?= e($p['nome']) ?>" loading="lazy" width="300" height="300">
            <?php if (!$available): ?><span class="tag tag-muted">Indisponível</span><?php endif; ?>
        </div>
        <div class="product-body">
            <h3><?= e($p['nome']) ?></h3>
            <p class="product-desc"><?= e($p['descricao']) ?></p>
            <?php if (!empty($p['categorias'])): ?><p class="product-cats"><?= e($p['categorias']) ?></p><?php endif; ?>
            <p class="price"><?= money($p['preco']) ?></p>
        </div>
    </a>
</article>
