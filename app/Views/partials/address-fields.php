<?php
/** @var array $values  valores atuais (old ou endereço salvo) */
$v = static fn (string $k) => e($values[$k] ?? '');
$ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
?>
<div class="grid-2">
    <div class="field">
        <label for="apelido">Apelido</label>
        <input id="apelido" name="apelido" maxlength="30" placeholder="Casa, Trabalho..." value="<?= $v('apelido') ?>" required>
        <?= field_error($errors, 'apelido') ?>
    </div>
    <div class="field">
        <label for="cep">CEP</label>
        <input id="cep" name="cep" inputmode="numeric" maxlength="9" placeholder="00000-000" value="<?= isset($values['cep']) ? e(format_cep($values['cep'])) : '' ?>" data-cep-lookup required aria-describedby="cep-status">
        <p id="cep-status" class="hint" aria-live="polite">Preenchemos o endereço automaticamente pelo CEP.</p>
        <?= field_error($errors, 'cep') ?>
    </div>
</div>
<div class="field">
    <label for="logradouro">Rua / Avenida</label>
    <input id="logradouro" name="logradouro" maxlength="120" autocomplete="address-line1" value="<?= $v('logradouro') ?>" required>
    <?= field_error($errors, 'logradouro') ?>
</div>
<div class="grid-2">
    <div class="field">
        <label for="numero">Número</label>
        <input id="numero" name="numero" maxlength="10" value="<?= $v('numero') ?>" required>
        <?= field_error($errors, 'numero') ?>
    </div>
    <div class="field">
        <label for="complemento">Complemento <span class="muted">(opcional)</span></label>
        <input id="complemento" name="complemento" maxlength="60" value="<?= $v('complemento') ?>">
        <?= field_error($errors, 'complemento') ?>
    </div>
</div>
<div class="grid-3">
    <div class="field">
        <label for="bairro">Bairro</label>
        <input id="bairro" name="bairro" maxlength="60" value="<?= $v('bairro') ?>" required>
        <?= field_error($errors, 'bairro') ?>
    </div>
    <div class="field">
        <label for="cidade">Cidade</label>
        <input id="cidade" name="cidade" maxlength="60" autocomplete="address-level2" value="<?= $v('cidade') ?>" required>
        <?= field_error($errors, 'cidade') ?>
    </div>
    <div class="field">
        <label for="uf">UF</label>
        <select id="uf" name="uf" required>
            <option value="">--</option>
            <?php foreach ($ufs as $uf): ?>
                <option value="<?= $uf ?>" <?= ($values['uf'] ?? '') === $uf ? 'selected' : '' ?>><?= $uf ?></option>
            <?php endforeach; ?>
        </select>
        <?= field_error($errors, 'uf') ?>
    </div>
</div>
