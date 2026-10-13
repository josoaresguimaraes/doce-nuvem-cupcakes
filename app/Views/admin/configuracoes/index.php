<?php
$val = static fn (string $k) => e($old[$k] ?? $settings[$k]);
$money = static fn (string $k) => e($old[$k] ?? number_format((float) $settings[$k], 2, ',', ''));
?>
<h1>Configurações</h1>
<div class="grid-2 align-start">
    <section class="card">
        <h2>Entrega e taxas</h2>
        <form method="post" action="<?= url('admin/configuracoes') ?>" class="form" novalidate>
            <?= csrf_field() ?>
            <div class="grid-2">
                <div class="field"><label for="hora_inicio">Início das entregas</label><input id="hora_inicio" name="hora_inicio" value="<?= $val('hora_inicio') ?>" placeholder="09:00"><?= field_error($errors, 'hora_inicio') ?></div>
                <div class="field"><label for="hora_fim">Fim das entregas</label><input id="hora_fim" name="hora_fim" value="<?= $val('hora_fim') ?>" placeholder="18:00"><?= field_error($errors, 'hora_fim') ?></div>
                <div class="field"><label for="antecedencia_horas">Antecedência mínima (h)</label><input id="antecedencia_horas" name="antecedencia_horas" type="number" min="0" max="72" value="<?= $val('antecedencia_horas') ?>"><?= field_error($errors, 'antecedencia_horas') ?></div>
                <div class="field"><label for="dias_maximos">Agendar até (dias)</label><input id="dias_maximos" name="dias_maximos" type="number" min="1" max="90" value="<?= $val('dias_maximos') ?>"><?= field_error($errors, 'dias_maximos') ?></div>
                <div class="field"><label for="valor_minimo">Valor mínimo do pedido (R$)</label><input id="valor_minimo" name="valor_minimo" inputmode="decimal" value="<?= $money('valor_minimo') ?>"><?= field_error($errors, 'valor_minimo') ?></div>
                <div class="field"><label for="taxa_pedido_minimo">Taxa abaixo do mínimo (R$)</label><input id="taxa_pedido_minimo" name="taxa_pedido_minimo" inputmode="decimal" value="<?= $money('taxa_pedido_minimo') ?>"><?= field_error($errors, 'taxa_pedido_minimo') ?></div>
                <div class="field"><label for="taxa_urgencia">Taxa de urgência (mesmo dia) (R$)</label><input id="taxa_urgencia" name="taxa_urgencia" inputmode="decimal" value="<?= $money('taxa_urgencia') ?>"><?= field_error($errors, 'taxa_urgencia') ?></div>
            </div>
            <button class="btn">Salvar</button>
        </form>
    </section>
    <section class="card">
        <h2>Áreas de entrega</h2>
        <p class="hint">Faixas de CEP atendidas e taxa de cada região. Nesta versão, são cadastradas direto no banco (tabela <code>zonas_entrega</code>).</p>
        <table class="table compact">
            <thead><tr><th scope="col">Região</th><th scope="col">CEPs</th><th scope="col">Taxa</th></tr></thead>
            <tbody>
            <?php foreach ($zones as $z): ?>
                <tr><td data-label="Região"><?= e($z['nome']) ?></td><td data-label="CEPs"><?= e(format_cep($z['cep_inicio'])) ?> a <?= e(format_cep($z['cep_fim'])) ?></td><td data-label="Taxa"><?= money($z['taxa']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
