<section class="error-page">
    <p class="error-code"><?= (int) $status ?></p>
    <h1><?= e($message) ?></h1>
    <p><a class="btn" href="<?= url('/') ?>">Voltar para a vitrine</a></p>
</section>
