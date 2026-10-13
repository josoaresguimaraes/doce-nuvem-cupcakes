<h1>Finalizar pedido</h1>
<?= field_error($errors, 'carrinho') ?>

<form method="post" action="<?= url('checkout') ?>" id="checkout-form" class="checkout-layout" novalidate
      data-quote="<?= e(url('api/checkout/cotacao')) ?>" data-slots="<?= e(url('api/checkout/horarios')) ?>">
    <?= csrf_field() ?>
    <div class="checkout-steps">
        <section class="card" aria-labelledby="passo-endereco">
            <h2 id="passo-endereco"><span class="step">1</span> Endereço de entrega</h2>
            <?php if ($addresses === []): ?>
                <p>Você ainda não tem endereço salvo.</p>
                <a class="btn btn-secondary" href="<?= url('enderecos?voltar=checkout') ?>">Cadastrar endereço</a>
            <?php else: ?>
                <?php foreach ($addresses as $i => $a): ?>
                    <label class="option-card">
                        <input type="radio" name="endereco_id" value="<?= (int) $a['id'] ?>" <?= ((int) ($old['endereco_id'] ?? 0) === (int) $a['id'] || (!isset($old['endereco_id']) && $i === 0)) ? 'checked' : '' ?>>
                        <span><strong><?= e($a['apelido']) ?></strong><br><small><?= e(App\Models\Address::format($a)) ?></small></span>
                    </label>
                <?php endforeach; ?>
                <?php if (count($addresses) < App\Models\Address::MAX_PER_USER): ?>
                    <a class="link-button" href="<?= url('enderecos?voltar=checkout') ?>">+ Adicionar outro endereço</a>
                <?php endif; ?>
            <?php endif; ?>
            <p id="address-error" class="field-error" role="alert" <?= isset($errors['endereco_id']) ? '' : 'hidden' ?>><?= e($errors['endereco_id'] ?? '') ?></p>
        </section>

        <section class="card" aria-labelledby="passo-entrega">
            <h2 id="passo-entrega"><span class="step">2</span> Data e horário</h2>
            <p class="hint">Entregamos das <?= e($settings['hora_inicio']) ?> às <?= e($settings['hora_fim']) ?>, com pelo menos <?= (int) $settings['antecedencia_horas'] ?> h de antecedência. Entregas no mesmo dia têm taxa de urgência de <?= money($settings['taxa_urgencia']) ?>.</p>
            <div class="grid-2">
                <div class="field">
                    <label for="data_entrega">Data</label>
                    <input type="date" id="data_entrega" name="data_entrega" min="<?= e(date('Y-m-d')) ?>" max="<?= e($maxDate) ?>" value="<?= old($old, 'data_entrega', $firstDate) ?>" required>
                </div>
                <div class="field">
                    <label for="hora_entrega">Horário</label>
                    <select id="hora_entrega" name="hora_entrega" required>
                        <?php foreach ($slots as $slot): ?>
                            <option value="<?= e($slot) ?>" <?= ($old['hora_entrega'] ?? '') === $slot ? 'selected' : '' ?>><?= e($slot) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <?= field_error($errors, 'data_entrega') ?>
        </section>

        <section class="card" aria-labelledby="passo-pagamento">
            <h2 id="passo-pagamento"><span class="step">3</span> Pagamento <span class="tag tag-muted">simulado</span></h2>
            <div class="payment-options" role="radiogroup" aria-label="Forma de pagamento">
                <?php foreach ($payments as $key => $label): ?>
                    <label class="option-card">
                        <input type="radio" name="metodo_pagamento" value="<?= e($key) ?>" <?= ($old['metodo_pagamento'] ?? 'cartao') === $key ? 'checked' : '' ?>>
                        <span><?= e($label) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            <?= field_error($errors, 'metodo_pagamento') ?>

            <div id="card-fields" class="payment-panel">
                <div class="field">
                    <label for="numero_cartao">Número do cartão</label>
                    <input id="numero_cartao" name="numero_cartao" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="0000 0000 0000 0000">
                    <p class="hint">Teste: 4111 1111 1111 1111 (aprovado) · 4000 0000 0000 0002 (recusado)</p>
                    <?= field_error($errors, 'numero_cartao') ?>
                </div>
                <div class="field">
                    <label for="nome_cartao">Nome impresso no cartão</label>
                    <input id="nome_cartao" name="nome_cartao" autocomplete="cc-name" value="<?= old($old, 'nome_cartao') ?>">
                    <?= field_error($errors, 'nome_cartao') ?>
                </div>
                <div class="grid-2">
                    <div class="field">
                        <label for="validade_cartao">Validade (MM/AA)</label>
                        <input id="validade_cartao" name="validade_cartao" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="12/29" value="<?= old($old, 'validade_cartao') ?>">
                        <?= field_error($errors, 'validade_cartao') ?>
                    </div>
                    <div class="field">
                        <label for="cvv">CVV</label>
                        <input id="cvv" name="cvv" inputmode="numeric" autocomplete="cc-csc" maxlength="4">
                        <?= field_error($errors, 'cvv') ?>
                    </div>
                </div>
            </div>
            <p id="pix-info" class="payment-panel notice" hidden>Após confirmar, mostramos o código Pix “copia e cola”, válido por 10 minutos.</p>
            <p id="boleto-info" class="payment-panel notice" hidden>O boleto vence em 2 dias úteis. O pedido entra em preparo após a confirmação do pagamento.</p>
            <p class="hint">🔒 Dados enviados com criptografia (HTTPS). O número do cartão não é armazenado.</p>
        </section>
    </div>

    <aside class="card summary" aria-labelledby="resumo">
        <h2 id="resumo">Resumo do pedido</h2>
        <ul class="summary-items">
            <?php foreach ($lines as $l): ?>
                <li><span><?= (int) $l['quantidade'] ?>× <?= e($l['nome_produto']) ?>
                    <?php if ($l['nome_cobertura']): ?><small>(<?= e($l['nome_cobertura']) ?>)</small><?php endif; ?></span>
                    <span><?= money($l['subtotal']) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <dl class="summary-list" id="quote" aria-live="polite">
            <div><dt>Subtotal</dt><dd data-q="subtotal">—</dd></div>
            <div><dt>Entrega <small id="zone-name"></small></dt><dd data-q="taxa_entrega">—</dd></div>
            <div data-row="taxa_pedido_minimo" hidden><dt>Taxa de pedido mínimo</dt><dd data-q="taxa_pedido_minimo">—</dd></div>
            <div data-row="taxa_urgencia" hidden><dt>Taxa de urgência</dt><dd data-q="taxa_urgencia">—</dd></div>
            <div class="summary-total"><dt>Total</dt><dd data-q="total">—</dd></div>
        </dl>
        <button type="submit" class="btn btn-block" id="place-order" <?= $addresses === [] ? 'disabled' : '' ?>>Confirmar pedido</button>
        <a href="<?= url('carrinho') ?>" class="link-button center-block">Voltar ao carrinho</a>
    </aside>
</form>
