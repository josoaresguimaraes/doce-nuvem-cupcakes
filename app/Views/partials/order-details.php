<?php use App\Models\Order; ?>
<section class="card">
    <h2>Itens</h2>
    <table class="table compact">
        <thead><tr><th scope="col">Cupcake</th><th scope="col">Personalização</th><th scope="col">Qtd.</th><th scope="col">Preço un.</th><th scope="col">Subtotal</th></tr></thead>
        <tbody>
        <?php foreach ($items as $i): ?>
            <tr>
                <td data-label="Cupcake"><?= e($i['nome_produto']) ?></td>
                <td data-label="Personalização">
                    Cobertura: <?= e($i['nome_cobertura'] ?? 'padrão') ?>
                    <?php if ($i['mensagem']): ?><br>Mensagem: “<?= e($i['mensagem']) ?>”<?php endif; ?>
                </td>
                <td data-label="Qtd."><?= (int) $i['quantidade'] ?></td>
                <td data-label="Preço un."><?= money($i['preco_unitario']) ?></td>
                <td data-label="Subtotal"><?= money($i['subtotal']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <dl class="summary-list narrow">
        <div><dt>Subtotal</dt><dd><?= money($order['subtotal']) ?></dd></div>
        <div><dt>Entrega</dt><dd><?= money($order['taxa_entrega']) ?></dd></div>
        <?php if ((float) $order['taxa_pedido_minimo'] > 0): ?><div><dt>Taxa de pedido mínimo</dt><dd><?= money($order['taxa_pedido_minimo']) ?></dd></div><?php endif; ?>
        <?php if ((float) $order['taxa_urgencia'] > 0): ?><div><dt>Taxa de urgência</dt><dd><?= money($order['taxa_urgencia']) ?></dd></div><?php endif; ?>
        <div class="summary-total"><dt>Total</dt><dd><?= money($order['total']) ?></dd></div>
    </dl>
</section>

<section class="card">
    <h2>Entrega e pagamento</h2>
    <p><strong>Endereço:</strong> <?= e($order['endereco_entrega']) ?></p>
    <p><strong>Data:</strong> <?= e(format_date($order['data_entrega'])) ?> às <?= e(substr($order['hora_entrega'], 0, 5)) ?></p>
    <p><strong>Pagamento:</strong> <?= e(payment_label($order['metodo_pagamento'])) ?>
        <?php if ($order['cartao_final']): ?>final <?= e($order['cartao_final']) ?><?php endif; ?>
        · <span id="payment-status" class="status status-pag-<?= e($order['status_pagamento']) ?>"><?= e(Order::PAYMENT_STATUS_LABELS[$order['status_pagamento']]) ?></span>
    </p>
    <?php if ($order['status_pagamento'] === 'aguardando' && $order['codigo_pagamento']): ?>
        <div class="payment-code">
            <label for="codigo-pagamento"><?= $order['metodo_pagamento'] === 'pix' ? 'Pix copia e cola (simulado)' : 'Linha digitável do boleto (simulada)' ?></label>
            <div class="copy-row">
                <input id="codigo-pagamento" readonly value="<?= e($order['codigo_pagamento']) ?>">
                <button type="button" class="btn btn-small btn-secondary" data-copy="#codigo-pagamento">Copiar</button>
            </div>
            <p class="hint">Vence em <?= e(format_date($order['vencimento_pagamento'], true)) ?>.</p>
        </div>
    <?php endif; ?>
</section>
