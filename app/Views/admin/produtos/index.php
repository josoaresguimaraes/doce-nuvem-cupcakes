<div class="page-head">
    <h1>Produtos</h1>
    <a class="btn" href="<?= url('admin/produtos/novo') ?>">+ Novo produto</a>
</div>

<table class="table">
    <thead><tr><th scope="col">Produto</th><th scope="col">Categorias</th><th scope="col">Preço</th><th scope="col">Estoque</th><th scope="col">Vitrine</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead>
    <tbody>
    <?php foreach ($products as $p): ?>
        <tr class="<?= (int) $p['ativo'] ? '' : 'row-muted' ?>">
            <td data-label="Produto">
                <div class="cart-product">
                    <img src="<?= e(product_image($p['imagem'])) ?>" alt="" width="44" height="44">
                    <strong><?= e($p['nome']) ?></strong>
                </div>
            </td>
            <td data-label="Categorias"><?= e($p['categorias'] ?? '—') ?></td>
            <td data-label="Preço"><?= money($p['preco']) ?></td>
            <td data-label="Estoque"><?= (int) $p['estoque'] === 0 ? '<span class="tag tag-warn">0 · indisponível</span>' : (int) $p['estoque'] ?></td>
            <td data-label="Vitrine"><?= (int) $p['ativo'] ? 'Ativo' : 'Oculto' ?></td>
            <td class="row-actions">
                <a href="<?= url('admin/produtos/' . $p['id'] . '/editar') ?>">Editar</a>
                <form method="post" action="<?= url('admin/produtos/' . $p['id'] . '/excluir') ?>" data-confirm="Remover “<?= e($p['nome']) ?>”?">
                    <?= csrf_field() ?>
                    <button class="link-button danger">Remover</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
