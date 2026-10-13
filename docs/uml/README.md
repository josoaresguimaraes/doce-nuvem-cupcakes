# Modelagem UML (revisada)

Os diagramas estão em Mermaid, que o GitHub desenha automaticamente. As versões em imagem ficam em [`imagens/`](imagens/).

Revisão em relação ao PIT I: o PIT I tinha apenas as histórias de usuário e o backlog. Aqui eles viraram diagramas que refletem **o que foi implementado** (ver [../01-revisao-do-planejamento.md](../01-revisao-do-planejamento.md)).

## 1. Diagrama de casos de uso

### 1.1 Cliente e visitante

```mermaid
flowchart LR
    visitante(["👤 Visitante"])
    cliente(["👤 Cliente"])
    viacep[["ViaCEP<br/>(sistema externo)"]]

    subgraph Loja["Doce Nuvem Cupcakes — loja"]
        direction TB
        UC01(["US01 Visualizar vitrine"])
        UC02(["US02 Filtrar por categoria"])
        UC03(["US03 Personalizar e adicionar ao carrinho"])
        UC11(["US11 Criar conta / entrar"])
        UCF(["Finalizar pedido"])
        UC04(["US04 Calcular custo total"])
        UC06(["US06 Agendar entrega"])
        UC05(["US05 Pagar (cartão, Pix, boleto)"])
        UC07(["US07 Rastrear pedido"])
        UC15(["US15 Gerenciar endereços"])
    end

    visitante --- UC01
    visitante --- UC02
    visitante --- UC03
    visitante --- UC11
    cliente -- "generalização<br/>(também é visitante)" --> visitante
    cliente --- UCF
    cliente --- UC07
    cliente --- UC15
    UCF -. "«include»" .-> UC04
    UCF -. "«include»" .-> UC06
    UCF -. "«include»" .-> UC05
    UC15 --- viacep
```

### 1.2 Gerente

```mermaid
flowchart LR
    gerente(["👤 Gerente"])
    subgraph Admin["Doce Nuvem Cupcakes — área do gerente"]
        direction TB
        UC09(["US09 Gerenciar catálogo"])
        UCC(["Gerenciar categorias e coberturas"])
        UCS(["Atualizar status / confirmar pagamento"])
        UC10(["US10 Ver relatórios e exportar CSV"])
        UCP(["Configurar horários e taxas"])
    end
    gerente --- UC09
    gerente --- UCC
    gerente --- UCS
    gerente --- UC10
    gerente --- UCP
```

**Atores**
- **Visitante:** navega pela vitrine e monta o carrinho sem precisar de conta.
- **Cliente:** visitante autenticado. Finaliza pedidos, salva endereços e acompanha entregas.
- **Gerente:** administra catálogo, pedidos, relatórios e parâmetros de negócio.

## 2. Diagrama de classes

### 2.1 Classes de domínio (persistência)

```mermaid
classDiagram
    direction LR
    class Usuario {
        +int id
        +string nome
        +string email
        -string senhaHash
        +Perfil perfil
    }
    class Endereco {
        +int id
        +string apelido
        +string cep
        +string logradouro
        +string numero
        +string complemento
        +string bairro
        +string cidade
        +string uf
        +formatar() string
    }
    class Produto {
        +int id
        +string nome
        +string descricao
        +decimal preco
        +int estoque
        +bool ativo
        +disponivel() bool
    }
    class Categoria {
        +int id
        +string nome
    }
    class Cobertura {
        +int id
        +string nome
        +decimal precoAdicional
        +bool ativa
    }
    class Pedido {
        +int id
        +string enderecoEntrega
        +decimal subtotal
        +decimal taxaEntrega
        +decimal taxaPedidoMinimo
        +decimal taxaUrgencia
        +decimal total
        +date dataEntrega
        +time horaEntrega
        +MetodoPagamento metodo
        +StatusPagamento statusPagamento
        +StatusPedido status
        +mudarStatus(novo) void
        +confirmarPagamento() void
    }
    class ItemPedido {
        +int quantidade
        +string mensagem
        +decimal precoUnitario
        +decimal subtotal
    }
    class HistoricoStatus {
        +StatusPedido status
        +datetime alteradoEm
    }
    class ZonaEntrega {
        +string cepInicio
        +string cepFim
        +decimal taxa
        +atende(cep) bool
    }
    class StatusPedido {
        <<enumeration>>
        RECEBIDO
        EM_PREPARO
        EM_TRANSITO
        ENTREGUE
        CANCELADO
    }

    Usuario "1" --> "0..3" Endereco : salva
    Usuario "1" --> "0..*" Pedido : faz
    Pedido "1" *-- "1..*" ItemPedido : contém
    Pedido "1" *-- "1..*" HistoricoStatus : registra
    ItemPedido "*" --> "1" Produto
    ItemPedido "*" --> "0..1" Cobertura
    Produto "*" -- "1..*" Categoria
    ZonaEntrega ..> Endereco : atende pelo CEP
    Pedido ..> StatusPedido
```

### 2.2 Classes da aplicação (MVC + serviços)

```mermaid
classDiagram
    direction TB
    class Controller {
        <<abstract>>
        #Request request
        #view(nome, dados)
        #json(dados, status)
        #backWithErrors(rota, erros)
    }
    class Model {
        <<abstract>>
        #PDO db
        #fetch(sql, params)
        #fetchAll(sql, params)
        #execute(sql, params)
    }
    class CheckoutController {
        +index()
        +quote()
        +slots()
        +place()
    }
    class ShopController {
        +index()
        +products()
        +show(id)
    }
    class CartController {
        +index()
        +add()
        +update()
        +remove()
    }
    class OrderController {
        +index()
        +show(id)
        +status(id)
    }
    class CheckoutService {
        +detailedLines(Cart) array
        +quote(Cart, enderecoId, usuarioId, data, agora) array
        +placeOrder(Cart, usuarioId, entrada, agora) array
    }
    class Cart {
        +add()
        +update()
        +remove()
        +clear()
        +count()
    }
    class PricingService {
        +quote(itens, taxaZona, config, urgente) array
    }
    class DeliveryScheduler {
        +availableSlots(data, agora) array
        +validate()
        +isUrgent()
    }
    class PaymentSimulator {
        +process(metodo, dados, valor, agora) array
        +luhn(numero)$
    }
    class Validator {
        +email()$
        +password()$
        +cep()$
        +cupcakeMessage()$
        +address()$
    }
    class Order {
        +create(pedido, itens) int
        +changeStatus(id, status)
        +salesReport(de, ate, agrupar)
    }
    class Product {
        +paginate(categorias, pagina)
        +create()
        +update()
        +remove()
    }

    Controller <|-- CheckoutController
    Controller <|-- ShopController
    Controller <|-- CartController
    Controller <|-- OrderController
    Model <|-- Order
    Model <|-- Product
    CheckoutController --> CheckoutService
    CartController --> Cart
    ShopController --> Product
    CheckoutService --> Cart
    CheckoutService --> PricingService
    CheckoutService --> DeliveryScheduler
    CheckoutService --> PaymentSimulator
    CheckoutService --> Order
    CheckoutService --> Product
    CartController ..> Validator
```

Os serviços (`PricingService`, `DeliveryScheduler`, `PaymentSimulator`, `Validator`, `Cart`) **não acessam o banco**. Recebem dados e devolvem resultados, por isso são testados de forma isolada (testes unitários).

## 3. Diagrama de sequência — finalizar pedido

```mermaid
sequenceDiagram
    autonumber
    actor C as Cliente
    participant V as View (checkout)
    participant CT as CheckoutController
    participant S as CheckoutService
    participant DS as DeliveryScheduler
    participant PS as PricingService
    participant PG as PaymentSimulator
    participant O as Order (Model)
    participant DB as MySQL

    C->>V: escolhe endereço, data/horário e pagamento
    V->>CT: GET /api/checkout/cotacao (a cada alteração)
    CT->>S: quote()
    S->>PS: quote(itens, taxa da zona, urgente?)
    PS-->>S: subtotal, taxas, total
    S-->>V: JSON com valores atualizados
    C->>V: Confirmar pedido
    V->>CT: POST /checkout (+ token CSRF)
    CT->>S: placeOrder(carrinho, usuário, dados, agora)
    S->>S: confere estoque, endereço e CEP atendido
    S->>DS: validate(data, horário)
    alt dados inválidos
        S-->>CT: erros
        CT-->>V: volta ao formulário com mensagens
    else válido
        S->>PS: quote()
        S->>PG: process(método, dados do cartão)
        alt pagamento recusado
            PG-->>S: erro (ex.: cartão recusado)
            S-->>CT: erros
            CT-->>V: mensagem no campo do cartão
        else aprovado / aguardando (Pix, boleto)
            S->>O: create(pedido, itens)
            O->>DB: BEGIN
            O->>DB: SELECT ... FOR UPDATE (estoque)
            O->>DB: UPDATE produtos SET estoque = estoque - qtd
            O->>DB: INSERT pedidos, itens_pedido, historico_status
            O->>DB: COMMIT
            S->>S: esvazia o carrinho
            CT-->>C: redireciona para /pedidos/{id} (rastreamento)
        end
    end
```

## 4. Diagrama de atividades — compra do cliente

```mermaid
flowchart TD
    A([Início]) --> B[Ver vitrine]
    B --> C{Filtrar?}
    C -- sim --> D[Marcar categorias] --> B
    C -- não --> E[Abrir cupcake]
    E --> F[Escolher quantidade, cobertura e mensagem]
    F --> G{Mensagem válida e<br/>há estoque?}
    G -- não --> F
    G -- sim --> H[Adicionar ao carrinho]
    H --> I{Continuar comprando?}
    I -- sim --> B
    I -- não --> J[Carrinho: revisar itens]
    J --> K{Logado?}
    K -- não --> L[Entrar ou criar conta] --> M
    K -- sim --> M[Checkout]
    M --> N{Tem endereço<br/>atendido?}
    N -- não --> O[Cadastrar endereço] --> M
    N -- sim --> P[Escolher data e horário]
    P --> Q[Escolher pagamento]
    Q --> R{Pagamento ok?}
    R -- recusado --> Q
    R -- sim --> S[Pedido criado]
    S --> T[Acompanhar status]
    T --> U([Fim])
```

## 5. Diagrama de estados — pedido

```mermaid
stateDiagram-v2
    [*] --> Recebido: pedido confirmado
    Recebido --> EmPreparo: gerente avança<br/>(exige pagamento aprovado)
    Recebido --> Cancelado: gerente cancela<br/>(estoque devolvido)
    EmPreparo --> EmTransito: saiu para entrega
    EmPreparo --> Cancelado
    EmTransito --> Entregue: entrega concluída
    Entregue --> [*]
    Cancelado --> [*]
```

As transições são validadas no modelo `Order::changeStatus()`. Não é possível, por exemplo, ir de "Recebido" direto para "Entregue" (RN#1 da US07).
