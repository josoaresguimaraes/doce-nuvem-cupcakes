<section class="hero">
    <div>
        <h1>Cupcakes feitos hoje, entregues na hora que você escolher</h1>
        <p>Escolha o sabor, personalize a cobertura e a mensagem, e agende a entrega.</p>
    </div>
</section>

<section class="shop" aria-labelledby="titulo-vitrine">
    <aside class="filters" aria-labelledby="titulo-filtros">
        <h2 id="titulo-filtros">Filtrar por categoria</h2>
        <form id="filter-form" method="get" action="<?= url('/') ?>">
            <?php foreach ($categories as $c): ?>
                <label class="check">
                    <input type="checkbox" name="categorias[]" value="<?= (int) $c['id'] ?>" <?= in_array((int) $c['id'], $selected, true) ? 'checked' : '' ?>>
                    <?= e($c['nome']) ?>
                </label>
            <?php endforeach; ?>
            <p class="hint">Marcando mais de uma, mostramos os cupcakes que atendem a todas.</p>
            <noscript><button type="submit" class="btn btn-small">Aplicar</button></noscript>
            <button type="button" id="clear-filters" class="link-button" <?= $selected ? '' : 'hidden' ?>>Limpar filtros</button>
        </form>
    </aside>

    <div class="shop-main">
        <div class="shop-head">
            <h2 id="titulo-vitrine">Nossa vitrine</h2>
            <p id="result-count" class="muted" aria-live="polite"><?= (int) $page['total'] ?> cupcake(s)</p>
        </div>
        <div id="product-grid" class="product-grid" data-api="<?= e(url('api/produtos')) ?>" data-page="1">
            <?php foreach ($page['items'] as $p): ?>
                <?= App\Core\View::partial('product-card', ['p' => $p]) ?>
            <?php endforeach; ?>
        </div>
        <p id="empty-state" class="empty" <?= $page['total'] > 0 ? 'hidden' : '' ?>>Nenhum cupcake encontrado com esses filtros.</p>
        <div class="load-more">
            <button id="load-more" class="btn btn-secondary" <?= $page['has_more'] ? '' : 'hidden' ?>>Carregar mais</button>
            <div id="scroll-sentinel" aria-hidden="true"></div>
        </div>
    </div>
</section>
