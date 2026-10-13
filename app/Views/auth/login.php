<section class="auth-page">
    <div class="card form-card">
        <h1>Entrar</h1>
        <form method="post" action="<?= url('login') ?>" class="form" novalidate>
            <?= csrf_field() ?>
            <div class="field">
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" autocomplete="email" value="<?= old($old, 'email') ?>" required>
                <?= field_error($errors, 'email') ?>
            </div>
            <div class="field">
                <label for="senha">Senha</label>
                <input id="senha" type="password" name="senha" autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-block">Entrar</button>
        </form>
        <p class="center">Novo por aqui? <a href="<?= url('cadastro') ?>">Criar conta</a></p>
        <details class="demo-box">
            <summary>Contas de demonstração</summary>
            <p>Cliente: <code>cliente@exemplo.com.br</code> / <code>Cliente123</code><br>
               Gerente: <code>gerente@docenuvem.com.br</code> / <code>Gerente@123</code></p>
        </details>
    </div>
</section>
