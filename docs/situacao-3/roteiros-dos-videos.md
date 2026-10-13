# Roteiros dos vídeos

A atividade pede **dois vídeos**:

1. **Vídeo da solução** (tabela de codificação): narrado, com **no mínimo 5 minutos**.
2. **Vídeo da solução atualizada** (final): **até 5 minutos**, mostrando as mudanças feitas a partir dos testes.

O material da aula traz durações conflitantes. Confirme com o tutor; os roteiros abaixo respeitam os dois limites.

**Dicas de gravação:** OBS Studio ou o gravador do Windows (Win + Alt + R), tela em 1920×1080, zoom do navegador em 110%, microfone perto. Publique no YouTube como **Não listado**.

---

## Vídeo 1: apresentação da solução (≈ 6 min)

| Tempo | Tela | Fala (resumo) |
|---|---|---|
| 0:00–0:30 | Vitrine | "Olá, sou Jonathan, RGM 32811896. Este é o Doce Nuvem Cupcakes, a solução do meu Projeto Integrador, feita em PHP com MySQL no padrão MVC, a partir do planejamento do PIT I." |
| 0:30–1:00 | GitHub (README) | "O código e a documentação estão no GitHub: UML, banco de dados, IHC, testes e manual." Mostre rapidamente as pastas. |
| 1:00–1:40 | Vitrine | Role a página (carregamento automático), marque "Veganos" + "Sem glúten" e mostre que o resultado cai para 1. Aponte o cupcake indisponível. *US01, US02* |
| 1:40–2:30 | Produto | Mensagem com "!!!" → erro explicado. Corrija, escolha a cobertura e mostre o total mudando. Adicione ao carrinho. *US03* |
| 2:30–3:00 | Carrinho | Altere a quantidade e mostre a taxa de pedido mínimo. *US04* |
| 3:00–4:15 | Checkout | Login, troca de endereço (mostre o CEP fora da área), troca de data (taxa de urgência aparece), cartão recusado e depois aprovado. *US04, US05, US06, US15* |
| 4:15–4:45 | Pedido | Linha do tempo e atualização automática. *US07* |
| 4:45–5:40 | Gerente | Confirmar pagamento, avançar status, cadastrar produto (mostrar validação), relatório e CSV, configurações. *US09, US10* |
| 5:40–6:15 | Volta ao cliente | O status mudou sozinho na tela do cliente. |
| 6:15–6:45 | Terminal | `vendor/bin/phpunit --testdox`: "63 testes automatizados, todos passando". |
| 6:45–7:00 | — | "Próximos passos: os testes com usuários, as correções e as histórias de prioridade C. Obrigado!" |

---

## Vídeo 2: solução atualizada (até 5 min)

| Tempo | Conteúdo |
|---|---|
| 0:00–0:20 | "Neste vídeo mostro as mudanças feitas depois dos testes com 5 colegas." |
| 0:20–0:50 | Mostre o laudo de qualidade (quantos defeitos e sugestões, quantos corrigidos). |
| 0:50–3:50 | Para cada correção principal (3 a 5): **antes** (print do testador) → **depois** (sistema funcionando). Diga quem relatou e o que mudou. |
| 3:50–4:30 | Sugestões não adotadas ou no backlog, e por quê. |
| 4:30–5:00 | Testes automatizados passando e commits no GitHub. Encerramento. |
