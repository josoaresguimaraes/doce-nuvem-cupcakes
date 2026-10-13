<?php if (!empty($messages)): ?>
    <div class="flash-stack" aria-live="polite">
        <?php foreach ($messages as $flash): ?>
            <div class="flash flash-<?= e($flash['type']) ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>">
                <span><?= e($flash['message']) ?></span>
                <button type="button" class="flash-close" aria-label="Fechar mensagem">×</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
