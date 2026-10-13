<section class="auth-page">
    <div class="card form-card">
        <h1>Criar conta</h1>
        <p class="muted">Salve endereços, acompanhe pedidos e compre mais rápido.</p>
        <form method="post" action="<?= url('cadastro') ?>" class="form" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="nome">Nome completo</label>
                <input id="nome" name="nome" autocomplete="name" value="<?= old($old, 'nome') ?>" required>
                <?= field_error($errors, 'nome') ?>
            </div>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" autocomplete="email" value="<?= old($old, 'email') ?>" required>
                <?= field_error($errors, 'email') ?>
            </div>
            <div class="field">
                <label for="senha">Senha</label>
                <input id="senha" type="password" name="senha" autocomplete="new-password" minlength="8" aria-describedby="senha-ajuda" required>
                <p id="senha-ajuda" class="hint">Mínimo de 8 caracteres, com letras e números.</p>
                <?= field_error($errors, 'senha') ?>
            </div>
            <div class="field">
                <label for="senha_confirmacao">Confirme a senha</label>
                <input id="senha_confirmacao" type="password" name="senha_confirmacao" autocomplete="new-password" required>
                <?= field_error($errors, 'senha_confirmacao') ?>
            </div>
            <button type="submit" class="btn btn-block">Criar conta</button>
        </form>
        <p class="center">Já tem conta? <a href="<?= url('login') ?>">Entrar</a></p>
    </div>
</section>
