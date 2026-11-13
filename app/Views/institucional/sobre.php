<?php
/**
 * Página "Sobre nós" — sugestão S01 do laudo de qualidade (testador:
 * "explicação mais detalhada sobre a empresa para passar mais credibilidade").
 *
 * @var array<string, string> $settings
 * @var array<int, array<string, mixed>> $zones
 * @var array<int, array<string, mixed>> $categories
 */
$inicio = (int) substr($settings['hora_inicio'], 0, 2);
$fim = (int) substr($settings['hora_fim'], 0, 2);
$cep = static fn (string $c): string => substr($c, 0, 5) . '-' . substr($c, 5);
?>
<section class="hero about-hero">
    <div>
        <h1>Sobre a Doce Nuvem</h1>
        <p>Somos uma confeitaria de Sorocaba (SP) especializada em cupcakes. Cada pedido é preparado no dia da entrega,
            personalizado do seu jeito e levado até você no horário que você escolher.</p>
    </div>
</section>

<div class="about-grid">
    <section class="card" aria-labelledby="quem-somos">
        <h2 id="quem-somos">Quem somos</h2>
        <p>A Doce Nuvem nasceu da vontade de levar cupcakes caseiros e bem-feitos para aniversários, cafés da tarde e
            presentes. Trabalhamos por encomenda: assim não há cupcake parado na vitrine, e o que chega na sua casa
            foi feito poucas horas antes.</p>
        <ul class="about-list">
            <li><strong>Feitos no dia:</strong> massa, recheio e cobertura preparados para cada entrega.</li>
            <li><strong>Do seu jeito:</strong> escolha a cobertura e escreva uma mensagem de até 50 caracteres no topper.</li>
            <?php if ($categories): ?>
                <li><strong>Para todos:</strong> temos opções nas categorias
                    <?= e(implode(', ', array_column($categories, 'nome'))) ?>.</li>
            <?php endif; ?>
        </ul>
    </section>

    <section class="card" aria-labelledby="compra-segura">
        <h2 id="compra-segura">Compra segura</h2>
        <ul class="about-list">
            <li>O site usa conexão criptografada (HTTPS).</li>
            <li>Sua senha é guardada de forma criptografada: nem a nossa equipe consegue vê-la.</li>
            <li>Não guardamos o número do seu cartão, só os 4 últimos dígitos para você reconhecer a compra.</li>
            <li>Você acompanha cada etapa do pedido em <a href="<?= url('pedidos') ?>">Meus pedidos</a>, e o status se atualiza sozinho.</li>
        </ul>
    </section>
</div>

<section class="card" aria-labelledby="como-funciona">
    <h2 id="como-funciona">Como funciona</h2>
    <ol class="steps">
        <li><strong>Escolha</strong> os cupcakes na <a href="<?= url('/') ?>">vitrine</a> e personalize cobertura e mensagem.</li>
        <li><strong>Agende</strong> a entrega: de <?= $inicio ?>h às <?= $fim ?>h, com pelo menos <?= (int) $settings['antecedencia_horas'] ?> horas de antecedência e até <?= (int) $settings['dias_maximos'] ?> dias à frente.</li>
        <li><strong>Pague</strong> com cartão de crédito, Pix ou boleto.</li>
        <li><strong>Acompanhe</strong> o pedido: pagamento aprovado → em preparo → saiu para entrega → entregue.</li>
    </ol>
</section>

<section class="card" aria-labelledby="onde-entregamos">
    <h2 id="onde-entregamos">Onde entregamos</h2>
    <?php if ($zones): ?>
        <table class="table compact">
            <caption class="sr-only">Áreas de entrega e taxas</caption>
            <thead><tr><th scope="col">Região</th><th scope="col">CEPs</th><th scope="col">Taxa de entrega</th></tr></thead>
            <tbody>
            <?php foreach ($zones as $z): ?>
                <tr>
                    <td data-label="Região"><?= e($z['nome']) ?></td>
                    <td data-label="CEPs"><?= e($cep($z['cep_inicio'])) ?> a <?= e($cep($z['cep_fim'])) ?></td>
                    <td data-label="Taxa"><?= money($z['taxa']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <p class="hint">No checkout, a taxa é calculada automaticamente pelo CEP do endereço de entrega.</p>
    <?php else: ?>
        <p>As áreas de entrega estão sendo atualizadas.</p>
    <?php endif; ?>
</section>

<section class="card" aria-labelledby="duvidas">
    <h2 id="duvidas">Dúvidas frequentes</h2>
    <details>
        <summary>Posso receber no mesmo dia?</summary>
        <p>Sim, respeitando a antecedência de <?= (int) $settings['antecedencia_horas'] ?> horas. Entregas no mesmo dia têm taxa de urgência de <?= money($settings['taxa_urgencia']) ?>.</p>
    </details>
    <details>
        <summary>Existe pedido mínimo?</summary>
        <p>Não. Pedidos abaixo de <?= money($settings['valor_minimo']) ?> pagam uma taxa de <?= money($settings['taxa_pedido_minimo']) ?>, mostrada no carrinho antes de você finalizar.</p>
    </details>
    <details>
        <summary>Quanto tempo tenho para pagar o Pix ou o boleto?</summary>
        <p>O código Pix vale por 10 minutos. O boleto vence em 2 dias úteis. O preparo começa depois da confirmação do pagamento.</p>
    </details>
    <details>
        <summary>Quais símbolos posso usar na mensagem do topper?</summary>
        <p>Letras, números, espaços e a pontuação mais comum: <?= e(App\Services\Validator::MESSAGE_RULE_TEXT) ?>. Emojis não são aceitos porque não saem na impressão do topper.</p>
    </details>
    <details>
        <summary>Meu CEP não aparece na lista. E agora?</summary>
        <p>Por enquanto entregamos só nas regiões da tabela acima. Se o CEP estiver fora da área, o checkout avisa antes de você pagar.</p>
    </details>
</section>

<section class="card" aria-labelledby="contato">
    <h2 id="contato">Fale com a gente</h2>
    <p>Atendimento de segunda a sábado, das <?= $inicio ?>h às <?= $fim ?>h, pelo e-mail
        <a href="mailto:contato@docenuvem.com.br">contato@docenuvem.com.br</a>.</p>
</section>
