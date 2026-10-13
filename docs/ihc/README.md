# Interface Humano-Computador (IHC)

## 1. Princípios aplicados

Os princípios estudados no material da disciplina e como cada um aparece na aplicação:

| Princípio | Onde está na aplicação |
|---|---|
| **Clara** | Linguagem do cliente ("Finalizar pedido", "Carregar mais"), preços sempre em R$ e rótulo visível em todo campo. Cupcake esgotado aparece em cinza com a etiqueta "Indisponível". |
| **Consistente** | Mesmo cabeçalho, botões (primário rosa, secundário com contorno), cartões e mensagens em todas as telas. A área do gerente usa a mesma linguagem visual, com cabeçalho escuro para indicar que é outro contexto. |
| **Simples** | Checkout em 3 passos numerados (endereço, data/horário, pagamento). Os campos do cartão só aparecem quando "Cartão" está marcado (divulgação progressiva). |
| **Controlada pelo usuário** | O cliente monta o carrinho sem precisar de conta. Pode alterar quantidades, remover itens e voltar ao carrinho a qualquer momento. |
| **Direta** | O total do item atualiza enquanto se escolhe a cobertura e a quantidade. O resumo do checkout recalcula ao trocar endereço ou data. |
| **Empática e reversível** | Ações destrutivas (excluir endereço, cancelar pedido, remover produto) pedem confirmação. O carrinho é mantido ao sair da conta. Um produto com pedidos é desativado, e não apagado. |
| **Dá resposta (feedback)** | Mensagens de sucesso e erro no topo, erro junto ao campo (que some assim que o usuário corrige), botão "Processando…" ao confirmar e status do pedido atualizado sozinho a cada 5 s. |
| **Esteticamente agradável** | Paleta de confeitaria (rosa framboesa, chocolate e creme), ilustrações próprias dos cupcakes e bastante espaço em branco. |

**Acessibilidade:** contraste de texto adequado, foco visível no teclado, link "Pular para o conteúdo", `aria-live` nas áreas que mudam sozinhas (contagem da vitrine, resumo, status), campos com erro marcados com `aria-invalid`, alvos de toque ≥ 44 px e tabelas que viram cartões no celular.

**Elementos de interface utilizados** (classificação da Usability.gov citada no material):
- *Controles de entrada:* botões, campos de texto, caixas de seleção (filtros), botões de opção (cobertura, endereço, pagamento), listas suspensas (horário, UF), campo de data.
- *Navegação:* menu, trilha de navegação ("Vitrine › Red Velvet"), paginação ("Carregar mais").
- *Informativos:* etiquetas de status, linha do tempo do pedido, mensagens flash, contadores de caracteres, dicas abaixo dos campos.
- *Recipientes:* cartões e painéis de resumo.

## 2. Mapa de navegação

```mermaid
flowchart TD
    V[Vitrine] --> P[Produto]
    P --> C[Carrinho]
    V --> C
    C --> CK{Logado?}
    CK -- não --> L[Entrar / Criar conta] --> CH
    CK -- sim --> CH[Checkout]
    CH --> E[Endereços] --> CH
    CH --> PD[Pedido confirmado / rastreamento]
    MP[Meus pedidos] --> PD
    subgraph Gerente
        A[Pedidos] --> AD[Detalhe do pedido]
        AP[Produtos] --> AF[Novo / editar produto]
        AC[Categorias e coberturas]
        AR[Relatórios] --> CSV[(CSV)]
        AS[Configurações]
    end
    L -- perfil gerente --> A
```

## 3. Wireframes (baixa fidelidade)

Desenhados antes das telas finais, para definir a hierarquia e o fluxo. As notas amarelas indicam a história de usuário atendida.

| Tela | Wireframe |
|---|---|
| Vitrine (US01, US02) | ![Wireframe vitrine](wireframes/wf-01-vitrine.png) |
| Produto e personalização (US03) | ![Wireframe produto](wireframes/wf-02-produto.png) |
| Checkout (US04, US05, US06, US15) | ![Wireframe checkout](wireframes/wf-03-checkout.png) |
| Rastreamento (US07) | ![Wireframe rastreamento](wireframes/wf-04-rastreamento.png) |
| Gerente: pedidos | ![Wireframe gerente](wireframes/wf-05-admin-pedidos.png) |

## 4. Telas finais (protótipo funcional)

Capturadas automaticamente pelo teste ponta a ponta (`tests/e2e/fluxo_completo.py`).

| | |
|---|---|
| ![Vitrine](telas/01-vitrine.png) Vitrine | ![Filtros](telas/02-vitrine-filtros.png) Filtros combinados |
| ![Erro mensagem](telas/03-produto-erro-mensagem.png) Mensagem inválida | ![Personalização](telas/04-produto-personalizacao.png) Personalização |
| ![Carrinho](telas/05-carrinho.png) Carrinho | ![Checkout](telas/06-checkout.png) Checkout |
| ![Cartão recusado](telas/07-checkout-cartao-recusado.png) Cartão recusado | ![Pedido confirmado](telas/08-pedido-confirmado.png) Pedido confirmado |
| ![Pix](telas/09-pedido-pix.png) Pedido com Pix | ![Endereço com erro](telas/10-endereco-erros.png) Endereço inválido |
| ![Endereços](telas/11-enderecos.png) Endereços (fora da área) | ![Meus pedidos](telas/12-meus-pedidos.png) Meus pedidos |
| ![Gerente pedidos](telas/13-admin-pedidos.png) Gerente: pedidos | ![Gerente status](telas/14-admin-pedido-status.png) Gerente: avançar status |
| ![Produto erros](telas/15-admin-produto-erros.png) Gerente: validação de produto | ![Produtos](telas/16-admin-produtos.png) Gerente: produtos |
| ![Categorias](telas/17-admin-categorias.png) Categorias e coberturas | ![Relatórios](telas/18-admin-relatorios.png) Relatórios |
| ![Configurações](telas/19-admin-configuracoes.png) Configurações | ![Rastreamento](telas/20-rastreamento.png) Cliente vê "Em trânsito" |
| ![Mobile vitrine](telas/21-mobile-vitrine.png) Celular: vitrine | ![Mobile menu](telas/22-mobile-menu.png) Celular: menu |

## 5. Catálogo de mensagens de erro

Toda mensagem diz **o que aconteceu** e **o que fazer**, aparece **junto ao campo** e some quando o usuário corrige.

| Situação | Mensagem |
|---|---|
| Mensagem do topper com símbolo ou mais de 50 caracteres | A mensagem aceita até 50 letras, números e espaços (sem símbolos). |
| Quantidade acima do estoque | Escolha entre 1 e N unidades. |
| Cupcake esgotado | Este sabor está indisponível no momento. Volte em breve! |
| E-mail já cadastrado | Este e-mail já está cadastrado. Faça login ou use outro e-mail. |
| Senha fraca | A senha precisa ter pelo menos 8 caracteres, com letras e números. |
| Login inválido | E-mail ou senha incorretos. *(genérica, para não revelar quais e-mails existem)* |
| CEP inválido | CEP inválido. Use 8 dígitos, por exemplo 18035-000. |
| CEP fora da área | Infelizmente ainda não entregamos no CEP 01310-100. |
| Limite de endereços | Você já tem 3 endereços salvos. Exclua um para cadastrar outro. |
| Horário indisponível | Horário indisponível. Entregamos das 09:00 às 18:00, com 2 h de antecedência. |
| Cartão inválido / vencido / CVV | Número de cartão inválido. · Validade inválida ou cartão vencido (use MM/AA). · CVV deve ter 3 ou 4 dígitos. |
| Cartão recusado | Pagamento recusado pela operadora. Tente outro cartão. |
| Estoque acabou durante a compra | Estoque insuficiente para "Nome": restam N unidade(s). |
| Imagem do produto | Formato inválido. Envie uma imagem JPG ou PNG. · A imagem deve ter no máximo 2 MB. |
| Transição de status inválida | Não é possível mudar de "Recebido" para "Entregue". |
| Avançar pedido sem pagamento | Confirme o pagamento antes de avançar o pedido. |
| Sessão expirada (CSRF) | Sua sessão expirou. Recarregue a página e tente novamente. |
| Página inexistente / sem permissão | Página não encontrada. · Você não tem permissão para acessar esta página. |
| Erro interno | Algo deu errado do nosso lado. Já registramos o problema; tente novamente em instantes. |
