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

**Deixe abertos antes de gravar:** o [laudo de qualidade](laudo-de-qualidade.md) no GitHub, a pasta [evidencias](evidencias/), o site publicado (já com a versão 1.1) e a página de commits do repositório. Faça um pedido de teste com 1 cupcake no carrinho antes de começar.

| Tempo | Tela | Fala |
|---|---|---|
| 0:00–0:20 | Página inicial do site | "Olá, eu sou o Jonathan. Neste vídeo mostro a versão 1.1 do Doce Nuvem Cupcakes, com as mudanças feitas a partir dos testes com 5 usuários." |
| 0:20–0:55 | Laudo, seções 2 e 3 | "Os 5 testadores fizeram o fluxo completo: criaram a conta, montaram o carrinho, cadastraram o endereço e pagaram, primeiro com o cartão recusado e depois com o aprovado. Todos concluíram a compra, e a nota média de facilidade de uso foi 9,2 de 10. Foram 2 defeitos e 1 sugestão, e os três foram resolvidos." |
| 0:55–1:55 | `d01-antes.png` → site: produto, mensagem "Parabéns, Ana!", adicionar | "O primeiro defeito, o D01, foi relatado pelo Gustavo: a mensagem do topper não aceitava ponto de exclamação. A regra do PIT I dizia 'caracteres alfanuméricos' e eu implementei ao pé da letra, o que ficou restritivo demais. Revisei a regra: agora aceita a pontuação comum, como vírgula, exclamação e interrogação. Continuo bloqueando sinais de menor e maior, aspas e barras, por segurança contra injeção de HTML, e emojis, que não saem na impressão do topper. Vejam: 'Parabéns, Ana!' entra no carrinho." |
| 1:55–2:55 | `d02-antes.png` → site: carrinho, mudar a quantidade de 1 para 3 | "O D02 veio da Evellen: no carrinho, ao mudar a quantidade, o total só mudava depois de clicar em 'Atualizar quantidades'. Agora a atualização é automática: mudo para 3 e, meio segundo depois, o subtotal, o resumo e o contador do menu se atualizam, sem recarregar a página. O cálculo continua no servidor, que é quem confere preço e estoque. E se o navegador estiver sem JavaScript, o botão antigo continua funcionando." |
| 2:55–3:55 | `s01-antes.png` → site: clicar em "Sobre nós", rolar a página, abrir uma dúvida | "A sugestão S01 foi do Paulo: faltavam informações sobre a empresa para passar credibilidade. Criei a página 'Sobre nós', no menu e no rodapé, com quem somos, como a compra é protegida, como funciona o pedido, as regiões e taxas de entrega e as dúvidas frequentes. Horários, taxas e regiões vêm do banco de dados: se o gerente mudar a configuração, esta página muda junto." |
| 3:55–4:25 | Laudo, seção 6 | "Ficaram fora do roteiro curto os filtros, o Pix e o boleto e a área do gerente, que estão cobertos pelos testes automatizados. Recuperação de senha, notificações, avaliações e cupons são prioridade C e seguem no backlog." |
| 4:25–5:00 | `docs/testes/resultado-phpunit.txt` e página de commits do GitHub | "Cada correção ganhou testes automatizados: agora são 65 testes, todos passando. No GitHub há um commit para cada correção, e o laudo com as evidências está na pasta docs. Obrigado!" |
