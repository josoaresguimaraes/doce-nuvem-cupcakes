-- =====================================================================
-- Dados iniciais para demonstração e testes manuais.
-- Execute depois de schema.sql.
--
-- Usuários de teste:
--   Gerente: gerente@docenuvem.com.br / Gerente@123
--   Cliente: cliente@exemplo.com.br   / Cliente123
-- =====================================================================

SET NAMES utf8mb4;

INSERT INTO usuarios (id, nome, email, senha_hash, perfil) VALUES
(1, 'Gerente da Loja', 'gerente@docenuvem.com.br', '$2y$10$Qeu6kTaqURPbh54pFIKuQ.oP0SXSkcmKoLtIGVO24DbD8xYtE4yKe', 'gerente'),
(2, 'Cliente Demonstração', 'cliente@exemplo.com.br', '$2y$10$x1Ypzmu67JzYLYwChGXfmuQuZ16P98L8Ovm6MjTgOV9IoklbB/5qm', 'cliente');

INSERT INTO categorias (id, nome) VALUES
(1, 'Tradicionais'),
(2, 'Veganos'),
(3, 'Sem glúten'),
(4, 'Eventos'),
(5, 'Gourmet');

INSERT INTO produtos (id, nome, descricao, preco, estoque, imagem) VALUES
(1,  'Chocolate Clássico',     'Massa de cacau com recheio de brigadeiro cremoso.',                9.90,  40, 'chocolate.svg'),
(2,  'Baunilha Nuvem',         'Massa leve de baunilha com chantili.',                             8.90,  35, 'baunilha.svg'),
(3,  'Red Velvet',             'Massa aveludada com cobertura de cream cheese.',                  11.90,  25, 'red-velvet.svg'),
(4,  'Limão Siciliano',        'Massa cítrica com curd de limão e merengue tostado.',             10.90,  20, 'limao.svg'),
(5,  'Cenoura com Chocolate',  'A combinação de vó, em versão cupcake.',                           9.50,  30, 'cenoura.svg'),
(6,  'Vegano de Cacau',        'Sem ingredientes de origem animal, com ganache de coco.',         12.90,  15, 'vegano-cacau.svg'),
(7,  'Vegano de Morango',      'Massa de baunilha vegana com creme de morango.',                  12.90,  12, 'vegano-morango.svg'),
(8,  'Sem Glúten de Amêndoas', 'Farinha de amêndoas e cobertura de chocolate meio amargo.',       13.90,  10, 'amendoas.svg'),
(9,  'Sem Glúten de Coco',     'Massa de coco sem glúten com cobertura de doce de leite.',        13.50,   0, 'coco.svg'),
(10, 'Pistache Gourmet',       'Creme de pistache e pedaços crocantes.',                          15.90,  18, 'pistache.svg'),
(11, 'Caramelo Salgado',       'Recheio de caramelo com flor de sal.',                            14.50,  22, 'caramelo.svg'),
(12, 'Festa Colorida',         'Granulado colorido, ideal para aniversários.',                    10.50,  60, 'festa.svg'),
(13, 'Casamento Branco',       'Massa branca com pasta americana e pérolas de açúcar.',           16.90,  50, 'casamento.svg'),
(14, 'Café com Avelã',         'Massa de café e creme de avelã.',                                 12.50,  20, 'cafe.svg');

INSERT INTO produto_categoria (produto_id, categoria_id) VALUES
(1,1),(2,1),(3,1),(3,5),(4,1),(5,1),
(6,2),(6,3),(7,2),
(8,3),(8,5),(9,3),
(10,5),(11,5),(12,4),(12,1),(13,4),(13,5),(14,5);

INSERT INTO coberturas (id, nome, preco_adicional) VALUES
(1, 'Chocolate belga', 2.00),
(2, 'Baunilha',        1.50),
(3, 'Cream cheese',    2.50),
(4, 'Morango',         2.00),
(5, 'Doce de leite',   2.00);

-- Faixas de CEP de exemplo (região de Sorocaba/SP). Ajuste conforme a área real atendida.
INSERT INTO zonas_entrega (nome, cep_inicio, cep_fim, taxa) VALUES
('Sorocaba – Centro e arredores', '18000000', '18039999',  8.00),
('Sorocaba – Zona Norte',         '18040000', '18079999', 10.00),
('Sorocaba – Zonas Sul e Oeste',  '18080000', '18109999', 12.00),
('Votorantim',                    '18110000', '18119999', 15.00);

INSERT INTO configuracoes (chave, valor, descricao) VALUES
('hora_inicio',            '09:00', 'Início do horário de entrega'),
('hora_fim',               '18:00', 'Fim do horário de entrega (último horário = fim − 1h)'),
('antecedencia_horas',     '2',     'Antecedência mínima, em horas, para agendar uma entrega'),
('dias_maximos',           '30',    'Quantos dias à frente o cliente pode agendar'),
('valor_minimo',           '50.00', 'Pedidos abaixo deste valor pagam taxa extra'),
('taxa_pedido_minimo',     '5.00',  'Taxa extra para pedidos abaixo do valor mínimo'),
('taxa_urgencia',          '10.00', 'Taxa para entregas no mesmo dia');

INSERT INTO enderecos (usuario_id, apelido, cep, logradouro, numero, complemento, bairro, cidade, uf) VALUES
(2, 'Casa', '18035000', 'Rua Exemplo', '100', 'Apto 12', 'Centro', 'Sorocaba', 'SP');
