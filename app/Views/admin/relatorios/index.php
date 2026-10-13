<?php
$query = http_build_query(['de' => $from, 'ate' => $to, 'agrupar' => $groupBy]);
$max = 0.0;
foreach ($report['linhas'] as $r) { $max = max($max, (float) $r['faturamento']); }
$labels = ['dia' => 'Dia', 'semana' => 'Semana (início)', 'mes' => 'Mês'];
?>
<h1>Relatórios de vendas</h1>
<form method="get" action="<?= url('admin/relatorios') ?>" class="card filter-bar">
    <div class="field"><label for="de">De</label><input type="date" id="de" name="de" value="<?= e($from) ?>"></div>
    <div class="field"><label for="ate">Até</label><input type="date" id="ate" name="ate" value="<?= e($to) ?>"></div>
    <div class="field">
        <label for="agrupar">Agrupar por</label>
        <select id="agrupar" name="agrupar">
            <option value="dia" <?= $groupBy === 'dia' ? 'selected' : '' ?>>Dia</option>
            <option value="semana" <?= $groupBy === 'semana' ? 'selected' : '' ?>>Semana</option>
            <option value="mes" <?= $groupBy === 'mes' ? 'selected' : '' ?>>Mês</option>
        </select>
    </div>
    <button class="btn">Gerar</button>
    <a class="btn btn-secondary" href="<?= url('admin/relatorios/csv?' . $query) ?>">Exportar CSV</a>
</form>

<div class="kpis">
    <div class="kpi"><span>Pedidos</span><strong><?= (int) $report['totais']['pedidos'] ?></strong></div>
    <div class="kpi"><span>Faturamento</span><strong><?= money($report['totais']['faturamento']) ?></strong></div>
    <div class="kpi"><span>Ticket médio</span><strong><?= money($report['totais']['ticket_medio']) ?></strong></div>
</div>
<p class="hint">Considera apenas pedidos com pagamento aprovado e não cancelados.</p>

<?php if ($report['linhas'] === []): ?>
    <p class="empty">Nenhuma venda no período.</p>
<?php else: ?>
    <table class="table">
        <thead><tr><th scope="col"><?= e($labels[$groupBy]) ?></th><th scope="col">Pedidos</th><th scope="col">Faturamento</th><th scope="col"><span class="sr-only">Gráfico</span></th></tr></thead>
        <tbody>
        <?php foreach ($report['linhas'] as $r): ?>
            <tr>
                <td data-label="<?= e($labels[$groupBy]) ?>"><?= e($groupBy === 'mes' ? $r['periodo'] : format_date($r['periodo'])) ?></td>
                <td data-label="Pedidos"><?= (int) $r['pedidos'] ?></td>
                <td data-label="Faturamento"><?= money($r['faturamento']) ?></td>
                <td class="bar-cell" aria-hidden="true"><span class="bar" style="width: <?= $max > 0 ? round((float) $r['faturamento'] / $max * 100) : 0 ?>%"></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
