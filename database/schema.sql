-- =====================================================================
-- Doce Nuvem Cupcakes — Projeto físico do banco de dados (MySQL 8 / MariaDB 10.4+)
-- Engine InnoDB (integridade referencial) e charset utf8mb4 (acentos e emojis).
-- Execute este script no banco já criado (ex.: phpMyAdmin > Importar).
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS historico_status;
DROP TABLE IF EXISTS itens_pedido;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS enderecos;
DROP TABLE IF EXISTS produto_categoria;
DROP TABLE IF EXISTS produtos;
DROP TABLE IF EXISTS categorias;
DROP TABLE IF EXISTS coberturas;
DROP TABLE IF EXISTS zonas_entrega;
DROP TABLE IF EXISTS configuracoes;
DROP TABLE IF EXISTS usuarios;

SET FOREIGN_KEY_CHECKS = 1;

-- Clientes e gerentes (US11, RN: e-mail único; senha guardada como hash bcrypt)
CREATE TABLE usuarios (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nome          VARCHAR(100)     NOT NULL,
    email         VARCHAR(150)     NOT NULL,
    senha_hash    VARCHAR(255)     NOT NULL,
    perfil        ENUM('cliente','gerente') NOT NULL DEFAULT 'cliente',
    criado_em     DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categorias configuráveis pelo gerente (US02 RN#1)
CREATE TABLE categorias (
    id     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome   VARCHAR(50)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_categorias_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Catálogo (US01, US09)
CREATE TABLE produtos (
    id             INT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nome           VARCHAR(80)    NOT NULL,
    descricao      VARCHAR(100)   NOT NULL,
    preco          DECIMAL(10,2)  NOT NULL,
    estoque        INT UNSIGNED   NOT NULL DEFAULT 0,
    imagem         VARCHAR(255)   NULL,
    ativo          TINYINT(1)     NOT NULL DEFAULT 1,
    criado_em      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em  DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_produtos_ativo (ativo),
    CONSTRAINT ck_produtos_preco CHECK (preco > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Associação N:N entre produtos e categorias
CREATE TABLE produto_categoria (
    produto_id    INT UNSIGNED NOT NULL,
    categoria_id  INT UNSIGNED NOT NULL,
    PRIMARY KEY (produto_id, categoria_id),
    KEY idx_pc_categoria (categoria_id),
    CONSTRAINT fk_pc_produto   FOREIGN KEY (produto_id)   REFERENCES produtos (id)   ON DELETE CASCADE,
    CONSTRAINT fk_pc_categoria FOREIGN KEY (categoria_id) REFERENCES categorias (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coberturas definidas pelo gerente (US03 RN#2)
CREATE TABLE coberturas (
    id               INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nome             VARCHAR(50)   NOT NULL,
    preco_adicional  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    ativa            TINYINT(1)    NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uk_coberturas_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Endereços do cliente: até 3 por conta (US15 CA#1)
CREATE TABLE enderecos (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario_id   INT UNSIGNED NOT NULL,
    apelido      VARCHAR(30)  NOT NULL,
    cep          CHAR(8)      NOT NULL,
    logradouro   VARCHAR(120) NOT NULL,
    numero       VARCHAR(10)  NOT NULL,
    complemento  VARCHAR(60)  NULL,
    bairro       VARCHAR(60)  NOT NULL,
    cidade       VARCHAR(60)  NOT NULL,
    uf           CHAR(2)      NOT NULL,
    criado_em    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_enderecos_usuario (usuario_id),
    CONSTRAINT fk_enderecos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Áreas atendidas por faixa de CEP e respectiva taxa (US04 RN#1)
CREATE TABLE zonas_entrega (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(60)   NOT NULL,
    cep_inicio  CHAR(8)       NOT NULL,
    cep_fim     CHAR(8)       NOT NULL,
    taxa        DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_zonas_faixa (cep_inicio, cep_fim)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parâmetros de negócio editáveis pelo gerente (horários, valor mínimo, taxas)
CREATE TABLE configuracoes (
    chave      VARCHAR(40)  NOT NULL,
    valor      VARCHAR(100) NOT NULL,
    descricao  VARCHAR(150) NOT NULL,
    PRIMARY KEY (chave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Pedidos (US04, US05, US06, US07). O endereço é copiado (snapshot) para o pedido,
-- para que o histórico não mude se o cliente editar ou excluir o endereço depois.
CREATE TABLE pedidos (
    id                INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    usuario_id        INT UNSIGNED  NOT NULL,
    endereco_entrega  VARCHAR(300)  NOT NULL,
    cep_entrega       CHAR(8)       NOT NULL,
    subtotal          DECIMAL(10,2) NOT NULL,
    taxa_entrega      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    taxa_pedido_minimo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    taxa_urgencia     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    total             DECIMAL(10,2) NOT NULL,
    data_entrega      DATE          NOT NULL,
    hora_entrega      TIME          NOT NULL,
    metodo_pagamento  ENUM('cartao','pix','boleto') NOT NULL,
    status_pagamento  ENUM('aguardando','aprovado','recusado') NOT NULL DEFAULT 'aguardando',
    codigo_pagamento  VARCHAR(120)  NULL,
    cartao_final      CHAR(4)       NULL,
    vencimento_pagamento DATETIME   NULL,
    status            ENUM('recebido','em_preparo','em_transito','entregue','cancelado') NOT NULL DEFAULT 'recebido',
    criado_em         DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_pedidos_usuario (usuario_id),
    KEY idx_pedidos_criado (criado_em),
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios (id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Itens do pedido com personalização (US03). Nome e preço também são copiados (snapshot).
CREATE TABLE itens_pedido (
    id              INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    pedido_id       INT UNSIGNED  NOT NULL,
    produto_id      INT UNSIGNED  NOT NULL,
    nome_produto    VARCHAR(80)   NOT NULL,
    cobertura_id    INT UNSIGNED  NULL,
    nome_cobertura  VARCHAR(50)   NULL,
    mensagem        VARCHAR(50)   NULL,
    quantidade      INT UNSIGNED  NOT NULL,
    preco_unitario  DECIMAL(10,2) NOT NULL,
    subtotal        DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id),
    KEY idx_itens_pedido (pedido_id),
    KEY idx_itens_produto (produto_id),
    CONSTRAINT fk_itens_pedido    FOREIGN KEY (pedido_id)    REFERENCES pedidos (id)    ON DELETE CASCADE,
    CONSTRAINT fk_itens_produto   FOREIGN KEY (produto_id)   REFERENCES produtos (id)   ON DELETE RESTRICT,
    CONSTRAINT fk_itens_cobertura FOREIGN KEY (cobertura_id) REFERENCES coberturas (id) ON DELETE SET NULL,
    CONSTRAINT ck_itens_quantidade CHECK (quantidade > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Linha do tempo do pedido para o rastreamento (US07)
CREATE TABLE historico_status (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    pedido_id   INT UNSIGNED NOT NULL,
    status      ENUM('recebido','em_preparo','em_transito','entregue','cancelado') NOT NULL,
    alterado_em DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_historico_pedido (pedido_id),
    CONSTRAINT fk_historico_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
