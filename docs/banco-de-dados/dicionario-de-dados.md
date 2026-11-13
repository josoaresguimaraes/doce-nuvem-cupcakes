# Dicionário de dados

Gerado a partir do banco MySQL criado por `database/schema.sql` e complementado com a descrição de negócio de cada campo.

Legenda: **PK** chave primária · **FK** chave estrangeira · **UK** valor único · **Nulo** indica se o campo aceita NULL.

## `usuarios`

Clientes e gerentes do sistema. *(Histórias: US11)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Identificador do usuário |
| `nome` | varchar(100) | não |  |  | Nome completo (3 a 100 caracteres) |
| `email` | varchar(150) | não | UK |  | E-mail de login, único, gravado em minúsculas |
| `senha_hash` | varchar(255) | não |  |  | Hash bcrypt da senha (password_hash). A senha nunca é guardada em texto puro |
| `perfil` | enum('cliente','gerente') | não |  | cliente | Papel: cliente ou gerente (controla o acesso à área administrativa) |
| `criado_em` | datetime | não |  | CURRENT_TIMESTAMP | Data/hora do cadastro |

## `enderecos`

Endereços de entrega salvos pelo cliente (máx. 3 por conta). *(Histórias: US15)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Identificador |
| `usuario_id` | int(10) unsigned | não | FK → usuarios.id |  | Dono do endereço (só ele acessa, RN#1 US15) |
| `apelido` | varchar(30) | não |  |  | Nome curto (Casa, Trabalho...) |
| `cep` | char(8) | não |  |  | CEP só com dígitos (8) |
| `logradouro` | varchar(120) | não |  |  | Rua/avenida |
| `numero` | varchar(10) | não |  |  | Número (ou s/n) |
| `complemento` | varchar(60) | sim |  |  | Complemento opcional |
| `bairro` | varchar(60) | não |  |  | Bairro |
| `cidade` | varchar(60) | não |  |  | Cidade |
| `uf` | char(2) | não |  |  | Sigla do estado |
| `criado_em` | datetime | não |  | CURRENT_TIMESTAMP | Data/hora do cadastro |

## `categorias`

Categorias usadas nos filtros da vitrine, configuráveis pelo gerente. *(Histórias: US02)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Identificador |
| `nome` | varchar(50) | não | UK |  | Nome exibido no filtro (único) |

## `produtos`

Catálogo de cupcakes. *(Histórias: US01, US09)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Identificador |
| `nome` | varchar(80) | não |  |  | Nome do cupcake |
| `descricao` | varchar(100) | não |  |  | Descrição curta (máx. 100 caracteres, RN#1 US01) |
| `preco` | decimal(10,2) | não |  |  | Preço unitário em reais (> 0) |
| `estoque` | int(10) unsigned | não |  | 0 | Unidades disponíveis. Zero = exibido como indisponível (RN#2 US01) |
| `imagem` | varchar(255) | sim |  |  | Arquivo da imagem (JPG/PNG até 2 MB, RN#2 US09) |
| `ativo` | tinyint(1) | não |  | 1 | 1 = aparece na vitrine; 0 = oculto (produto removido que já tem pedidos) |
| `criado_em` | datetime | não |  | CURRENT_TIMESTAMP | Data/hora do cadastro |
| `atualizado_em` | datetime | não |  | CURRENT_TIMESTAMP | Data/hora da última alteração |

## `produto_categoria`

Associação N:N entre produtos e categorias. *(Histórias: US02)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `produto_id` | int(10) unsigned | não | PK, FK → produtos.id |  | Produto |
| `categoria_id` | int(10) unsigned | não | PK, FK → categorias.id |  | Categoria |

## `coberturas`

Opções de cobertura para personalização, definidas pelo gerente. *(Histórias: US03)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Identificador |
| `nome` | varchar(50) | não | UK |  | Nome da cobertura (único) |
| `preco_adicional` | decimal(10,2) | não |  | 0.00 | Valor somado ao preço unitário |
| `ativa` | tinyint(1) | não |  | 1 | 1 = disponível para o cliente |

## `zonas_entrega`

Faixas de CEP atendidas e taxa de entrega de cada região. *(Histórias: US04)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Identificador |
| `nome` | varchar(60) | não |  |  | Nome da região |
| `cep_inicio` | char(8) | não |  |  | Primeiro CEP da faixa |
| `cep_fim` | char(8) | não |  |  | Último CEP da faixa |
| `taxa` | decimal(10,2) | não |  |  | Taxa de entrega da região (RN#1 US04) |

## `configuracoes`

Parâmetros de negócio editáveis pelo gerente (chave/valor). *(Histórias: US04, US06)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `chave` | varchar(40) | não | PK |  | Nome do parâmetro (hora_inicio, hora_fim, antecedencia_horas, dias_maximos, valor_minimo, taxa_pedido_minimo, taxa_urgencia) |
| `valor` | varchar(100) | não |  |  | Valor do parâmetro |
| `descricao` | varchar(150) | não |  |  | Explicação exibida para o gerente |

## `pedidos`

Pedidos realizados. Endereço e valores são gravados como cópia (snapshot) no momento da compra. *(Histórias: US04, US05, US06, US07)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Número do pedido |
| `usuario_id` | int(10) unsigned | não | FK → usuarios.id |  | Cliente que fez o pedido |
| `endereco_entrega` | varchar(300) | não |  |  | Endereço completo copiado no momento da compra |
| `cep_entrega` | char(8) | não |  |  | CEP de entrega |
| `subtotal` | decimal(10,2) | não |  |  | Soma dos itens |
| `taxa_entrega` | decimal(10,2) | não |  | 0.00 | Taxa da zona do CEP |
| `taxa_pedido_minimo` | decimal(10,2) | não |  | 0.00 | Taxa extra quando o subtotal fica abaixo do mínimo (RN#2 US04) |
| `taxa_urgencia` | decimal(10,2) | não |  | 0.00 | Taxa para entrega no mesmo dia (RN#2 US06) |
| `total` | decimal(10,2) | não |  |  | Subtotal + taxas |
| `data_entrega` | date | não |  |  | Data agendada |
| `hora_entrega` | time | não |  |  | Horário agendado (de hora em hora) |
| `metodo_pagamento` | enum('cartao','pix','boleto') | não |  |  | cartao, pix ou boleto |
| `status_pagamento` | enum('aguardando','aprovado','recusado') | não |  | aguardando | aguardando, aprovado ou recusado |
| `codigo_pagamento` | varchar(120) | sim |  |  | Autorização do cartão, código Pix ou linha do boleto (simulados) |
| `cartao_final` | char(4) | sim |  |  | Somente os 4 últimos dígitos do cartão |
| `vencimento_pagamento` | datetime | sim |  |  | Validade do Pix (10 min) ou vencimento do boleto (2 dias úteis) |
| `status` | enum('recebido','em_preparo','em_transito','entregue','cancelado') | não |  | recebido | recebido → em_preparo → em_transito → entregue (ou cancelado) |
| `criado_em` | datetime | não |  | CURRENT_TIMESTAMP | Data/hora do pedido |
| `atualizado_em` | datetime | não |  | CURRENT_TIMESTAMP | Data/hora da última alteração |

## `itens_pedido`

Itens de cada pedido, com a personalização escolhida. *(Histórias: US03)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Identificador |
| `pedido_id` | int(10) unsigned | não | FK → pedidos.id |  | Pedido |
| `produto_id` | int(10) unsigned | não | FK → produtos.id |  | Produto comprado |
| `nome_produto` | varchar(80) | não |  |  | Nome copiado no momento da compra |
| `cobertura_id` | int(10) unsigned | sim | FK → coberturas.id |  | Cobertura escolhida (opcional) |
| `nome_cobertura` | varchar(50) | sim |  |  | Nome da cobertura copiado |
| `mensagem` | varchar(50) | sim |  |  | Mensagem do topper (até 50 caracteres: letras, números, espaços e pontuação comum; RN#1 US03 revisada na v1.1) |
| `quantidade` | int(10) unsigned | não |  |  | Quantidade (> 0) |
| `preco_unitario` | decimal(10,2) | não |  |  | Preço do cupcake + adicional da cobertura |
| `subtotal` | decimal(10,2) | não |  |  | quantidade × preço unitário |

## `historico_status`

Linha do tempo de cada pedido, usada no rastreamento. *(Histórias: US07)*

| Campo | Tipo | Nulo | Chave | Padrão | Descrição |
|---|---|---|---|---|---|
| `id` | int(10) unsigned | não | PK | auto incremento | Identificador |
| `pedido_id` | int(10) unsigned | não | FK → pedidos.id |  | Pedido |
| `status` | enum('recebido','em_preparo','em_transito','entregue','cancelado') | não |  |  | Status atingido |
| `alterado_em` | datetime | não |  | CURRENT_TIMESTAMP | Quando o status mudou |
