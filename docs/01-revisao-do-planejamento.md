# Revisão do planejamento (Kaizen) — PIT I → PIT II

**Aluno:** Jonathan Soares Guimarães · **RGM:** 32811896

O PIT I definiu o produto (aplicativo de pedidos de cupcakes) em 15 histórias de usuário e um backlog priorizado. Esta revisão registra o que foi mantido, o que mudou e por quê, antes de partir para a implementação.

## 1. Escopo e ideia (revalidados)

**Problema:** uma confeitaria de cupcakes gourmet precisa vender online com personalização e entrega agendada. O cliente também precisa ter confiança no pagamento (o PIT I cita uma "experiência negativa anterior"), e o gerente precisa de autonomia para manter o catálogo.

**Solução:** aplicação **web responsiva** (funciona no celular e no computador), com vitrine, carrinho personalizável, checkout com agendamento e rastreamento do pedido, além de uma área do gerente.

## 2. Principais mudanças em relação ao PIT I

| # | O que mudou | Motivo |
|---|---|---|
| 1 | **App nativo iOS/Android → aplicação web responsiva** (US01 CA#2) | Um único código atende celular e computador. É mais viável no prazo da disciplina e cumpre a orientação de construir front-end em HTML, CSS e JavaScript. A interface foi testada em largura de celular (390 px). |
| 2 | **Pagamento real → pagamento simulado** (US05) | Integrar uma operadora real exige contrato, CNPJ e certificação. O fluxo, as validações (Luhn, validade, CVV), o Pix com validade de 10 min e o boleto com 2 dias úteis foram simulados fielmente, e o cartão de teste `4000 0000 0000 0002` simula a recusa. |
| 3 | **Rolagem infinita** (US01 CA#3) implementada com carregamento por página (8 itens) e botão "Carregar mais" como alternativa acessível | Atende quem usa teclado ou leitor de tela, que tem dificuldade com rolagem infinita pura. |
| 4 | **Combinação de filtros** (US02 CA#3) definida como "E" (interseção) | "Vegano" + "Sem glúten" deve mostrar o que atende às **duas** restrições, que é o caso de uso de quem tem restrição alimentar. |
| 5 | **Taxa de entrega por faixa de CEP** (US04 RN#1) | "Localização" foi concretizada como faixas de CEP cadastradas (tabela `zonas_entrega`), com mensagem clara para CEP fora da área (US04 CA#3). |
| 6 | **Novas regras de negócio** levantadas na revisão | Controle de estoque na compra (com transação), devolução do estoque ao cancelar, fluxo de status sem pular etapas, pedido só avança com pagamento aprovado, produto com pedidos é desativado em vez de excluído. |
| 7 | **US04 subiu de C para A e US11 de B para A** | O cálculo do total é indispensável para finalizar qualquer pedido. A conta do cliente também: endereços, histórico e rastreamento dependem dela. |
| 8 | **Validação de CEP** (US15 RN#2) feita no navegador via ViaCEP, com validação de formato no servidor | Preenche o endereço automaticamente. Se o serviço estiver fora do ar, o cliente preenche à mão e o sistema continua funcionando. |
| 9 | **Requisitos de segurança explicitados** | Senhas com hash bcrypt, proteção CSRF em todos os formulários, prepared statements (SQL injection), escape de HTML (XSS), sessão regenerada no login, upload restrito a JPG/PNG de até 2 MB e bloqueio de execução de scripts na pasta de uploads. |

## 3. Backlog revisado e status de implementação

| ID | História | Prior. PIT I | Prior. revisada | Status no PIT II |
|---|---|---|---|---|
| US01 | Visualizar vitrine | A | A | ✅ Implementada (14 cupcakes, indisponíveis sinalizados, paginação) |
| US03 | Personalizar cupcakes no carrinho | A | A | ✅ Implementada (quantidade, cobertura, mensagem até 50 caracteres) |
| US04 | Calcular custo total | C | **A** | ✅ Implementada (itens + entrega + taxa de mínimo + urgência) |
| US05 | Métodos de pagamento | A | A | ✅ Implementada **(simulada)** |
| US06 | Data e horário de entrega | A | A | ✅ Implementada (expediente, antecedência, taxa de urgência) |
| US11 | Criar conta | B | A | ✅ Implementada · ⚠️ confirmação por e-mail (CA#2) **não implementada** |
| US02 | Filtrar por categoria | B | B | ✅ Implementada |
| US07 | Rastrear status | B | B | ✅ Implementada (linha do tempo e atualização a cada 5 s) |
| US09 | Gerenciar catálogo | B | B | ✅ Implementada (CRUD, upload, categorias, coberturas) |
| US15 | Salvar endereço | B | B | ✅ Implementada (até 3, ViaCEP) |
| US10 | Relatórios de vendas | C | C | ✅ Implementada (dia/semana/mês e CSV) |
| US12 | Recuperar senha | C | C | ⏳ Backlog: exige envio de e-mail (SMTP) |
| US08 | Notificações | C | C | ⏳ Backlog: o status já atualiza sozinho na página; push e e-mail ficam para depois |
| US13 | Avaliar cupcakes | C | C | ⏳ Backlog |
| US14 | Cupons de desconto | C | C | ⏳ Backlog |

**Resumo:** 11 das 15 histórias foram implementadas, incluindo **todas** as de prioridade A e B. As 4 restantes (prioridade C) seguem no backlog para próximas iterações.

## 4. Requisitos não funcionais revisados

| RNF | Meta do PIT I | Como foi tratado |
|---|---|---|
| Desempenho da vitrine | ≤ 2 s em 4G | Páginas de 8 itens, imagens vetoriais leves (SVG) e índices no banco. |
| Filtros / cálculo | < 1 s / < 0,5 s | Feitos por requisições assíncronas (JSON), sem recarregar a página. |
| Rastreamento | < 5 s | A página consulta o status a cada 5 s. |
| Segurança do pagamento | TLS e criptografia | O site deve ser publicado com HTTPS. O número do cartão **não é armazenado**, só os 4 últimos dígitos. |
| Usabilidade/acessibilidade | — (novo) | Contraste adequado, foco visível, rótulos em todos os campos, mensagens de erro junto ao campo e alvos de toque ≥ 44 px. |
| Manutenibilidade | — (novo) | Padrão MVC, regras de negócio em serviços testáveis e 63 testes automatizados. |

## 5. Artefatos produzidos nesta revisão

- [Diagramas UML](uml/README.md): casos de uso, classes, sequência, atividades e estados
- [Projeto de dados](banco-de-dados/README.md): conceitual, lógico normalizado e físico, mais o [dicionário de dados](banco-de-dados/dicionario-de-dados.md)
- [IHC](ihc/README.md): princípios aplicados, mapa de navegação, wireframes, mensagens de erro e telas finais
- [Arquitetura](arquitetura.md) e [plano de testes](testes/README.md)
