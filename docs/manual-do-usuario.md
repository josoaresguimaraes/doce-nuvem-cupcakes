# Manual do usuário

## Contas de demonstração

| Perfil | E-mail | Senha |
|---|---|---|
| Cliente | `cliente@exemplo.com.br` | `Cliente123` |
| Gerente | `gerente@docenuvem.com.br` | `Gerente@123` |

Cartões de teste (o pagamento é **simulado**, nada é cobrado):

| Cartão | Resultado |
|---|---|
| `4111 1111 1111 1111` · validade futura (ex.: 12/30) · CVV `123` | Aprovado |
| `4000 0000 0000 0002` | Recusado pela operadora |
| Qualquer número que não passe no algoritmo de Luhn | "Número de cartão inválido" |

---

## Para o cliente

### 1. Escolher cupcakes
1. Na **Vitrine**, role a página. Mais cupcakes carregam sozinhos, ou clique em **Carregar mais**.
2. Para filtrar, marque categorias à esquerda (no celular, ficam acima da lista). Marcando duas ou mais, aparecem só os cupcakes que atendem a **todas**, por exemplo "Veganos" + "Sem glúten".
3. Cupcakes em cinza com a etiqueta **Indisponível** estão sem estoque.

### 2. Personalizar e adicionar ao carrinho
1. Clique no cupcake.
2. Escolha a **quantidade**, a **cobertura** (algumas têm valor adicional) e, se quiser, uma **mensagem** de até 50 letras, números e espaços.
3. O **total do item** atualiza na hora.
4. Clique em **Adicionar ao carrinho** ou em **Adicionar e continuar comprando**.

### 3. Carrinho
- Altere as quantidades e clique em **Atualizar quantidades**. Quantidade 0 remove o item.
- **Remover** tira o item.
- Pedidos abaixo de R$ 50,00 pagam taxa de R$ 5,00. O carrinho avisa quanto falta.

### 4. Finalizar pedido
1. Clique em **Finalizar pedido**. Se não estiver logado, entre ou crie sua conta e você volta direto ao checkout.
2. **Endereço:** escolha um endereço salvo ou cadastre um novo. Se o CEP estiver fora da área de entrega, o sistema avisa.
3. **Data e horário:** entregamos das 9h às 18h, com 2 h de antecedência. Entrega no mesmo dia tem taxa de urgência de R$ 10,00.
4. **Pagamento:** cartão (aprovado na hora), Pix (código "copia e cola" válido por 10 min) ou boleto (vence em 2 dias úteis).
5. Confira o **resumo** à direita, que recalcula sozinho, e clique em **Confirmar pedido**.

### 5. Acompanhar o pedido
- Depois de confirmar, você vê a **linha do tempo**: Recebido → Em preparo → Em trânsito → Entregue. A página se atualiza sozinha a cada 5 segundos.
- Em **Meus pedidos** está o histórico completo.

### 6. Endereços
- Em **Endereços** você salva até 3 endereços, edita ou exclui. Ao digitar o CEP e sair do campo, rua, bairro e cidade são preenchidos automaticamente (quando há internet).

---

## Para o gerente

Entre com a conta de gerente: você cai direto na **Área do gerente**.

### Pedidos
1. A lista mostra os pedidos por data e hora de entrega. Filtre por status no topo.
2. Clique em **Gerenciar** para abrir o pedido.
3. Para Pix ou boleto, clique em **Confirmar pagamento recebido** quando o pagamento cair.
4. Avance o status: **Em preparo** → **Em trânsito** → **Entregue**. Só aparecem os próximos passos válidos.
5. **Cancelar** devolve os cupcakes ao estoque.

### Produtos
- **+ Novo produto:** nome, descrição (até 100 caracteres), preço, estoque, categorias e imagem (JPG/PNG até 2 MB).
- **Editar:** deixe a imagem em branco para manter a atual. Estoque 0 deixa o cupcake "indisponível" na vitrine.
- **Remover:** se o produto já foi vendido, ele é apenas ocultado da vitrine, para preservar o histórico.

### Categorias e coberturas
- Crie ou exclua categorias (viram filtros na vitrine).
- Crie coberturas com valor adicional, ou desative e reative as existentes.

### Relatórios
- Escolha o período e o agrupamento (dia, semana ou mês). Veja pedidos, faturamento e ticket médio.
- **Exportar CSV** abre no Excel com acentos corretos.
- Entram apenas pedidos com pagamento aprovado e não cancelados.

### Configurações
- Horário de entrega, antecedência mínima, até quantos dias se pode agendar, valor mínimo do pedido, taxa abaixo do mínimo e taxa de urgência.
- As áreas de entrega (faixas de CEP e taxas) aparecem para consulta. Nesta versão, são editadas direto no banco (tabela `zonas_entrega`).
