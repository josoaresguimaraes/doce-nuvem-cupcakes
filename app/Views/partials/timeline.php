<?php
use App\Models\Order;

$reached = array_column($history, 'alterado_em', 'status');
$steps = $order['status'] === 'cancelado'
    ? ['recebido', 'cancelado']
    : ['recebido', 'em_preparo', 'em_transito', 'entregue'];
?>
<ol class="timeline" id="timeline">
    <?php foreach ($steps as $step): ?>
        <li class="timeline-step<?= isset($reached[$step]) ? ' is-done' : '' ?><?= $order['status'] === $step ? ' is-current' : '' ?>" data-step="<?= e($step) ?>">
            <span class="dot" aria-hidden="true"></span>
            <span class="label"><?= e(Order::STATUS_LABELS[$step]) ?></span>
            <small class="when"><?= isset($reached[$step]) ? e(format_date($reached[$step], true)) : '' ?></small>
        </li>
    <?php endforeach; ?>
</ol>
