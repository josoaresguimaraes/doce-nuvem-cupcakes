"""
Teste ponta a ponta (E2E) no navegador com Playwright — opcional.

Percorre o fluxo do cliente (vitrine → carrinho → checkout → pedido) e do gerente
(confirmação de pagamento e avanço de status), salvando capturas de tela em
docs/ihc/telas/. Falha se houver erro de JavaScript no console.

Uso (com o servidor rodando em http://127.0.0.1:8000 e o banco recém-populado):
    pip install playwright && playwright install chromium
    python tests/e2e/fluxo_completo.py
"""
import os
import re
import sys
from datetime import date, timedelta

from playwright.sync_api import sync_playwright, expect

BASE = os.environ.get("APP_URL", "http://127.0.0.1:8000")
OUT = os.path.join(os.path.dirname(__file__), "..", "..", "docs", "ihc", "telas")
os.makedirs(OUT, exist_ok=True)
console_errors = []


def shot(page, name, full=True):
    page.evaluate("window.scrollTo(0, 0)")
    page.screenshot(path=os.path.join(OUT, f"{name}.png"), full_page=full)


def login(page, email, senha):
    page.goto(f"{BASE}/login")
    page.fill("#email", email)
    page.fill("#senha", senha)
    page.click("button:has-text('Entrar')")


def watch(page):
    page.on("console", lambda m: console_errors.append(m.text) if m.type == "error" and "viacep" not in m.text.lower() else None)
    page.on("pageerror", lambda e: console_errors.append(str(e)))


def cliente(browser):
    ctx = browser.new_context(viewport={"width": 1280, "height": 900}, locale="pt-BR")
    page = ctx.new_page()
    watch(page)

    # US01 — vitrine
    page.goto(BASE)
    expect(page.locator(".product-card")).to_have_count(8)
    shot(page, "01-vitrine")

    # US01 CA#3 — carregar mais
    page.click("#load-more")
    expect(page.locator(".product-card")).to_have_count(14)

    # US02 — filtros combinados (Veganos + Sem glúten)
    page.check("label:has-text('Veganos') input")
    expect(page.locator("#result-count")).to_have_text("2 cupcake(s)")
    page.check("label:has-text('Sem glúten') input")
    expect(page.locator("#result-count")).to_have_text("1 cupcake(s)")
    shot(page, "02-vitrine-filtros")
    page.click("#clear-filters")

    # US03 — mensagem inválida
    page.goto(f"{BASE}/produto/3")
    page.fill("#mensagem", "Parabéns!!! <3")
    page.click("button:has-text('Adicionar ao carrinho')")
    expect(page.locator(".field-error")).to_contain_text("50 letras")
    shot(page, "03-produto-erro-mensagem")

    # US03 — personalização válida
    page.fill("#quantidade", "6")
    page.check("label:has-text('Cream cheese') input")
    page.fill("#mensagem", "Feliz aniversário Ana")
    expect(page.locator("#line-total")).to_have_text(re.compile(r"86,40"))
    shot(page, "04-produto-personalizacao")
    page.click("button:has-text('Adicionar ao carrinho')")
    expect(page.locator(".flash-success")).to_contain_text("adicionado")
    shot(page, "05-carrinho")

    # Checkout exige login → volta ao checkout depois de entrar
    page.click("a:has-text('Finalizar pedido')")
    expect(page).to_have_url(re.compile("/login"))
    page.fill("#email", "cliente@exemplo.com.br")
    page.fill("#senha", "Cliente123")
    page.click("button:has-text('Entrar')")
    expect(page).to_have_url(re.compile("/checkout"))

    # US04/US06 — cotação: escolhe amanhã (sem taxa de urgência)
    amanha = (date.today() + timedelta(days=1)).isoformat()
    page.fill("#data_entrega", amanha)
    page.dispatch_event("#data_entrega", "change")
    expect(page.locator("[data-q='taxa_entrega']")).to_have_text(re.compile(r"8,00"))
    expect(page.locator("[data-q='total']")).to_have_text(re.compile(r"94,40"))
    page.select_option("#hora_entrega", "14:00")

    # US05 — cartão recusado
    page.fill("#numero_cartao", "4000 0000 0000 0002")
    page.fill("#nome_cartao", "CLIENTE DEMO")
    page.fill("#validade_cartao", "12/30")
    page.fill("#cvv", "123")
    shot(page, "06-checkout")
    page.click("#place-order")
    expect(page.locator("#card-fields .field-error")).to_contain_text("recusado")
    shot(page, "07-checkout-cartao-recusado")

    # US05 — cartão aprovado
    page.fill("#data_entrega", amanha)
    page.dispatch_event("#data_entrega", "change")
    page.wait_for_timeout(300)
    page.select_option("#hora_entrega", "14:00")
    page.fill("#numero_cartao", "4111 1111 1111 1111")
    page.fill("#cvv", "123")
    page.click("#place-order")
    expect(page.locator(".success-banner")).to_be_visible()
    shot(page, "08-pedido-confirmado")
    order_url = page.url.split("?")[0]

    # Segundo pedido com Pix, pequeno e para hoje (taxas de mínimo e urgência, se houver horário)
    page.goto(f"{BASE}/produto/1")
    page.fill("#quantidade", "2")
    page.click("button:has-text('Adicionar ao carrinho')")
    page.goto(f"{BASE}/checkout")
    page.check("input[value='pix']")
    page.click("#place-order")
    expect(page.locator(".success-banner")).to_be_visible()
    expect(page.locator("#codigo-pagamento")).to_be_visible()
    shot(page, "09-pedido-pix")

    # US15 — endereço com CEP inválido e depois fora da área
    page.goto(f"{BASE}/enderecos")
    page.fill("#apelido", "Trabalho")
    page.fill("#cep", "123")
    page.click("button:has-text('Salvar endereço')")
    expect(page.locator(".field-error").first).to_be_visible()
    shot(page, "10-endereco-erros")
    page.fill("#apelido", "Trabalho")
    page.fill("#cep", "01310-100")
    page.fill("#logradouro", "Avenida Paulista")
    page.fill("#numero", "1000")
    page.fill("#bairro", "Bela Vista")
    page.fill("#cidade", "São Paulo")
    page.select_option("#uf", "SP")
    page.click("button:has-text('Salvar endereço')")
    expect(page.locator(".tag-warn")).to_contain_text("Fora da área")
    shot(page, "11-enderecos")

    page.goto(f"{BASE}/pedidos")
    shot(page, "12-meus-pedidos")
    ctx.close()
    return order_url


def gerente(browser, order_url):
    ctx = browser.new_context(viewport={"width": 1280, "height": 900}, locale="pt-BR")
    page = ctx.new_page()
    watch(page)
    login(page, "gerente@docenuvem.com.br", "Gerente@123")
    expect(page).to_have_url(re.compile("/admin"))
    shot(page, "13-admin-pedidos")

    order_id = order_url.rstrip("/").split("/")[-1]
    page.goto(f"{BASE}/admin/pedidos/{order_id}")
    page.click("button:has-text('Em preparo')")
    expect(page.locator(".flash-success")).to_be_visible()
    page.click("button:has-text('Em trânsito')")
    shot(page, "14-admin-pedido-status")

    # US09 — validações do cadastro de produto
    page.goto(f"{BASE}/admin/produtos/novo")
    page.click("button:has-text('Cadastrar produto')")
    expect(page.locator(".field-error").first).to_be_visible()
    shot(page, "15-admin-produto-erros")

    page.goto(f"{BASE}/admin/produtos")
    shot(page, "16-admin-produtos")
    page.goto(f"{BASE}/admin/categorias")
    shot(page, "17-admin-categorias")
    page.goto(f"{BASE}/admin/relatorios")
    expect(page.locator(".kpi strong").first).to_have_text("1")
    shot(page, "18-admin-relatorios")
    page.goto(f"{BASE}/admin/configuracoes")
    shot(page, "19-admin-configuracoes")
    ctx.close()


def cliente_ve_status(browser, order_url):
    ctx = browser.new_context(viewport={"width": 1280, "height": 900}, locale="pt-BR")
    page = ctx.new_page()
    watch(page)
    login(page, "cliente@exemplo.com.br", "Cliente123")
    page.goto(order_url)
    expect(page.locator("#status-label")).to_have_text("Em trânsito")
    shot(page, "20-rastreamento")
    ctx.close()


def mobile(browser):
    ctx = browser.new_context(viewport={"width": 390, "height": 844}, device_scale_factor=2, is_mobile=True, has_touch=True, locale="pt-BR")
    page = ctx.new_page()
    watch(page)
    page.goto(BASE)
    shot(page, "21-mobile-vitrine", full=False)
    page.click(".menu-toggle")
    shot(page, "22-mobile-menu", full=False)
    page.goto(f"{BASE}/produto/12")
    shot(page, "23-mobile-produto")
    ctx.close()


if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch()
        url = cliente(browser)
        gerente(browser, url)
        cliente_ve_status(browser, url)
        mobile(browser)
        browser.close()
    if console_errors:
        print("Erros de JavaScript:", *console_errors, sep="\n- ")
        sys.exit(1)
    print("Fluxo E2E concluído sem erros. Capturas em docs/ihc/telas/")
