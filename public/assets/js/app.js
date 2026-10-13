/* Doce Nuvem Cupcakes — comportamento no navegador (JavaScript puro, sem dependências). */
(() => {
    'use strict';

    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
    const brl = (n) => Number(n).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const escapeHtml = (s) => String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    async function getJson(url) {
        const res = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, status: res.status, data };
    }

    /* Menu no celular */
    $$('.menu-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const nav = document.getElementById(btn.getAttribute('aria-controls'));
            const open = nav.classList.toggle('is-open');
            btn.setAttribute('aria-expanded', String(open));
        });
    });

    /* Fechar mensagens e confirmações */
    document.addEventListener('click', (e) => {
        if (e.target.matches('.flash-close')) e.target.closest('.flash').remove();
    });
    $$('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!window.confirm(form.dataset.confirm)) e.preventDefault();
        });
    });
    $$('[data-autosubmit]').forEach((el) => el.addEventListener('change', () => el.form.submit()));

    /* Marca campos com erro para leitores de tela */
    $$('.field-error').forEach((err) => {
        const field = err.closest('.field');
        if (!field) return;
        field.classList.add('has-error');
        const input = $('input, select, textarea', field);
        if (input) input.setAttribute('aria-invalid', 'true');
        // Assim que o usuário corrige o campo, a mensagem de erro sai (feedback imediato)
        field.addEventListener('input', () => {
            field.classList.remove('has-error');
            $$('[aria-invalid="true"]', field).forEach((el) => el.removeAttribute('aria-invalid'));
            err.remove();
        }, { once: true });
    });

    /* Copiar código Pix/boleto */
    $$('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const input = $(btn.dataset.copy);
            try { await navigator.clipboard.writeText(input.value); } catch { input.select(); document.execCommand('copy'); }
            btn.textContent = 'Copiado!';
            setTimeout(() => { btn.textContent = 'Copiar'; }, 2000);
        });
    });

    /* Contador de caracteres genérico */
    $$('[data-counter]').forEach((el) => {
        const out = document.getElementById(el.dataset.counter);
        const update = () => { out.textContent = el.value.length; };
        el.addEventListener('input', update);
        update();
    });

    /* Valida tamanho do arquivo antes do envio (US09 RN#2) */
    $$('input[type=file][data-max-bytes]').forEach((input) => {
        input.addEventListener('change', () => {
            const file = input.files[0];
            input.setCustomValidity('');
            if (file && file.size > Number(input.dataset.maxBytes)) {
                input.value = '';
                alert('A imagem deve ter no máximo 2 MB.');
            }
        });
    });

    /* ---------- Vitrine: filtros dinâmicos + rolagem infinita (US01 CA#3, US02 CA#2) ---------- */
    const grid = $('#product-grid');
    if (grid) {
        const form = $('#filter-form');
        const btn = $('#load-more');
        const count = $('#result-count');
        const empty = $('#empty-state');
        const clear = $('#clear-filters');
        let page = 1;
        let loading = false;
        let hasMore = !btn.hidden;

        const selected = () => $$('input[name="categorias[]"]:checked', form).map((i) => i.value);
        const card = (p) => `
            <article class="product-card${p.disponivel ? '' : ' is-unavailable'}">
              <a href="${p.url}" class="product-link">
                <div class="product-image">
                  <img src="${p.imagem}" alt="Cupcake ${escapeHtml(p.nome)}" loading="lazy" width="300" height="300">
                  ${p.disponivel ? '' : '<span class="tag tag-muted">Indisponível</span>'}
                </div>
                <div class="product-body">
                  <h3>${escapeHtml(p.nome)}</h3>
                  <p class="product-desc">${escapeHtml(p.descricao)}</p>
                  ${p.categorias ? `<p class="product-cats">${escapeHtml(p.categorias)}</p>` : ''}
                  <p class="price">${p.preco}</p>
                </div>
              </a>
            </article>`;

        async function load(reset) {
            if (loading) return;
            loading = true;
            grid.classList.add('loading');
            const next = reset ? 1 : page + 1;
            const params = new URLSearchParams({ pagina: String(next) });
            selected().forEach((id) => params.append('categorias[]', id));
            const { ok, data } = await getJson(`${grid.dataset.api}?${params}`);
            grid.classList.remove('loading');
            loading = false;
            if (!ok) return;
            page = next;
            hasMore = data.has_more;
            const html = data.items.map(card).join('');
            if (reset) grid.innerHTML = html; else grid.insertAdjacentHTML('beforeend', html);
            btn.hidden = !hasMore;
            count.textContent = `${data.total} cupcake(s)`;
            empty.hidden = data.total > 0;
            clear.hidden = selected().length === 0;
            if (reset) {
                const qs = new URLSearchParams();
                selected().forEach((id) => qs.append('categorias[]', id));
                history.replaceState(null, '', qs.toString() ? `?${qs}` : location.pathname);
            }
        }

        form.addEventListener('change', () => load(true));
        clear.addEventListener('click', () => { $$('input[type=checkbox]', form).forEach((i) => { i.checked = false; }); load(true); });
        btn.addEventListener('click', () => load(false));
        if ('IntersectionObserver' in window) {
            new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting && hasMore) load(false);
            }, { rootMargin: '200px' }).observe($('#scroll-sentinel'));
        }
    }

    /* ---------- Página do produto: preço por cobertura e contador da mensagem (US03) ---------- */
    const addForm = $('#add-to-cart');
    if (addForm) {
        const base = Number($('#unit-price').dataset.base);
        const qty = $('#quantidade');
        const msg = $('#mensagem');
        const counter = $('#mensagem-contador');
        const total = $('#line-total');
        const valid = /^[\p{L}\p{N} ]*$/u;
        const recalc = () => {
            const extra = Number(($('input[name="cobertura_id"]:checked', addForm) || {}).dataset?.extra || 0);
            const q = Math.max(1, parseInt(qty.value || '1', 10));
            total.textContent = brl((base + extra) * q);
        };
        const checkMsg = () => {
            counter.textContent = msg.value.length;
            const ok = valid.test(msg.value) && msg.value.length <= 50;
            msg.setAttribute('aria-invalid', String(!ok));
            msg.setCustomValidity(ok ? '' : 'Use apenas letras, números e espaços.');
        };
        addForm.addEventListener('input', recalc);
        addForm.addEventListener('change', recalc);
        msg.addEventListener('input', checkMsg);
        recalc();
        checkMsg();
    }

    /* ---------- Checkout: cotação e horários em tempo real (US04 CA#2, US06) ---------- */
    const checkout = $('#checkout-form');
    if (checkout) {
        const dateInput = $('#data_entrega');
        const timeSelect = $('#hora_entrega');
        const addrError = $('#address-error');
        const submit = $('#place-order');

        const togglePayment = () => {
            const method = ($('input[name="metodo_pagamento"]:checked', checkout) || {}).value;
            $('#card-fields').hidden = method !== 'cartao';
            $('#pix-info').hidden = method !== 'pix';
            $('#boleto-info').hidden = method !== 'boleto';
        };

        async function refreshQuote() {
            const address = $('input[name="endereco_id"]:checked', checkout);
            const params = new URLSearchParams({ data_entrega: dateInput.value });
            if (address) params.set('endereco_id', address.value);
            const { ok, data } = await getJson(`${checkout.dataset.quote}?${params}`);
            const values = data.formatado || {};
            Object.entries(values).forEach(([k, v]) => {
                const dd = $(`[data-q="${k}"]`, checkout);
                if (dd) dd.textContent = v;
                const row = $(`[data-row="${k}"]`, checkout);
                if (row) row.hidden = !data.valores || Number(data.valores[k]) === 0;
            });
            $('#zone-name').textContent = data.zona ? `(${data.zona})` : '';
            addrError.hidden = ok;
            if (!ok) addrError.textContent = data.erro || 'Não foi possível calcular a entrega.';
            submit.disabled = !ok || !address;
        }

        async function refreshSlots() {
            const { data } = await getJson(`${checkout.dataset.slots}?data=${encodeURIComponent(dateInput.value)}`);
            const previous = timeSelect.value;
            timeSelect.innerHTML = '';
            if (!data.horarios || data.horarios.length === 0) {
                timeSelect.innerHTML = '<option value="">Sem horários nesta data</option>';
            } else {
                data.horarios.forEach((h) => timeSelect.add(new Option(h, h, false, h === previous)));
            }
        }

        checkout.addEventListener('change', (e) => {
            if (e.target.name === 'metodo_pagamento') togglePayment();
            if (e.target === dateInput) { refreshSlots(); refreshQuote(); }
            if (e.target.name === 'endereco_id') refreshQuote();
        });

        // Máscaras simples do cartão
        const cardNumber = $('#numero_cartao');
        cardNumber.addEventListener('input', () => {
            cardNumber.value = cardNumber.value.replace(/\D/g, '').slice(0, 19).replace(/(\d{4})(?=\d)/g, '$1 ');
        });
        const expiry = $('#validade_cartao');
        expiry.addEventListener('input', () => {
            const d = expiry.value.replace(/\D/g, '').slice(0, 4);
            expiry.value = d.length > 2 ? `${d.slice(0, 2)}/${d.slice(2)}` : d;
        });
        checkout.addEventListener('submit', () => { submit.disabled = true; submit.textContent = 'Processando…'; });

        togglePayment();
        refreshQuote();
    }

    /* ---------- Rastreamento do pedido: consulta a cada 5 s (US07 RNF#1) ---------- */
    const tracker = $('#order-tracker');
    if (tracker) {
        const hint = $('#tracker-hint');
        const finalStatus = ['entregue', 'cancelado'];
        let current = tracker.dataset.status;
        let payment = ($('#payment-status') || {}).textContent;
        const timer = setInterval(async () => {
            if (document.hidden) return;
            const { ok, data } = await getJson(tracker.dataset.statusUrl);
            if (!ok) { hint.textContent = 'Sem conexão. Tentando novamente…'; return; }
            hint.textContent = `Atualizado às ${new Date().toLocaleTimeString('pt-BR')}.`;
            if (data.status !== current || data.status_pagamento_label !== payment) {
                location.reload();
            }
            current = data.status;
            payment = data.status_pagamento_label;
            if (finalStatus.includes(current)) clearInterval(timer);
        }, 5000);
        if (finalStatus.includes(current)) { clearInterval(timer); hint.textContent = 'Pedido finalizado.'; }
    }

    /* ---------- Endereço: preenche pelo CEP via ViaCEP (US15 RN#2) ---------- */
    $$('[data-cep-lookup]').forEach((input) => {
        const status = document.getElementById(input.getAttribute('aria-describedby'));
        input.addEventListener('input', () => {
            const d = input.value.replace(/\D/g, '').slice(0, 8);
            input.value = d.length > 5 ? `${d.slice(0, 5)}-${d.slice(5)}` : d;
        });
        input.addEventListener('blur', async () => {
            const cep = input.value.replace(/\D/g, '');
            if (cep.length !== 8) return;
            status.textContent = 'Buscando endereço…';
            try {
                const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await res.json();
                if (data.erro) { status.textContent = 'CEP não encontrado. Confira os números ou preencha manualmente.'; return; }
                const form = input.form;
                const set = (name, value) => { const el = form.elements[name]; if (el && !el.value) el.value = value || ''; };
                set('logradouro', data.logradouro);
                set('bairro', data.bairro);
                set('cidade', data.localidade);
                if (data.uf) form.elements.uf.value = data.uf;
                status.textContent = 'Endereço encontrado. Confira e informe o número.';
                form.elements.numero.focus();
            } catch {
                status.textContent = 'Não foi possível consultar o CEP agora. Preencha manualmente.';
            }
        });
    });
})();
