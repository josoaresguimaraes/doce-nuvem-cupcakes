<h1>Seu carrinho</h1>
<p id="cart-status" class="sr-only" aria-live="polite"></p>

<?php if ($lines === []): ?>
    <div class="empty card">
        <p>Seu carrinho está vazio.</p>
        <a class="btn" href="<?= url('/') ?>">Ver a vitrine</a>
    </div>
<?php else: ?>
    <?= field_error($errors, 'carrinho') ?>
    <div class="cart-layout">
        <form method="post" action="<?= url('carrinho/atualizar') ?>" class="cart-items">
            <?= csrf_field() ?>
            <table class="table cart-table">
                <caption class="sr-only">Itens do carrinho</caption>
                <thead>
                <tr><th scope="col">Cupcake</th><th scope="col">Preço un.</th><th scope="col">Qtd.</th><th scope="col">Subtotal</th><th scope="col"><span class="sr-only">Ações</span></th></tr>
                </thead>
                <tbody>
                <?php foreach ($lines as $l): ?>
                    <tr>
                        <td data-label="Cupcake">
                            <div class="cart-product">
                                <img src="<?= e(product_image($l['imagem'])) ?>" alt="" width="56" height="56">
                                <div>
                                    <strong><?= e($l['nome_produto']) ?></strong>
                                    <small>Cobertura: <?= e($l['nome_cobertura'] ?? 'padrão da receita') ?></small>
                                    <?php if ($l['mensagem'] !== ''): ?><small>Mensagem: “<?= e($l['mensagem']) ?>”</small><?php endif; ?>
                                    <?php if ($l['quantidade'] > $l['estoque']): ?><small class="field-error">Só restam <?= (int) $l['estoque'] ?> unidade(s).</small><?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td data-label="Preço un."><?= money($l['preco_unitario']) ?></td>
                        <td data-label="Qtd.">
                            <label class="sr-only" for="qtd-<?= e($l['key']) ?>">Quantidade de <?= e($l['nome_produto']) ?></label>
                            <input class="qty-input" id="qtd-<?= e($l['key']) ?>" type="number" min="0" max="<?= min(50, $l['estoque']) ?>" name="quantidades[<?= e($l['key']) ?>]" value="<?= (int) $l['quantidade'] ?>">
                        </td>
                        <td data-label="Subtotal"><?= money($l['subtotal']) ?></td>
                        <td>
                            <button type="submit" form="remove-<?= e($l['key']) ?>" class="link-button danger">Remover</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <div class="actions">
                <?php /* Sem JavaScript o botão continua funcionando; com JavaScript a atualização é automática (D02). */ ?>
                <button type="submit" class="btn btn-secondary" data-cart-update>Atualizar quantidades</button>
                <p class="hint" data-cart-auto hidden>Os valores são atualizados automaticamente ao mudar a quantidade.</p>
                <a href="<?= url('/') ?>" class="link-button">Continuar comprando</a>
            </div>
        </form>
        <?php foreach ($lines as $l): ?>
            <form id="remove-<?= e($l['key']) ?>" method="post" action="<?= url('carrinho/remover') ?>" hidden>
                <?= csrf_field() ?><input type="hidden" name="key" value="<?= e($l['key']) ?>">
            </form>
        <?php endforeach; ?>

        <aside class="card summary" aria-labelledby="resumo">
            <h2 id="resumo">Resumo</h2>
            <dl class="summary-list">
                <div><dt>Subtotal</dt><dd><?= money($values['subtotal']) ?></dd></div>
                <?php if ($values['taxa_pedido_minimo'] > 0): ?>
                    <div><dt>Taxa de pedido mínimo</dt><dd><?= money($values['taxa_pedido_minimo']) ?></dd></div>
                <?php endif; ?>
                <div><dt>Entrega</dt><dd class="muted">calculada no checkout</dd></div>
                <div class="summary-total"><dt>Total parcial</dt><dd><?= money($values['total']) ?></dd></div>
            </dl>
            <?php if ($values['taxa_pedido_minimo'] > 0): ?>
                <p class="hint">Pedidos abaixo de <?= money($settings['valor_minimo']) ?> pagam taxa de <?= money($settings['taxa_pedido_minimo']) ?>. Faltam <?= money((float) $settings['valor_minimo'] - $values['subtotal']) ?>.</p>
            <?php endif; ?>
            <a href="<?= url('checkout') ?>" class="btn btn-block">Finalizar pedido</a>
        </aside>
    </div>
<?php endif; ?>
