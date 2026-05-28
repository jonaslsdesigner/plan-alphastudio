(async () => {
    const ctx = await initLayout('cards.html', 'Cartões de Crédito');
    if (!ctx) return;
    const { user, month } = ctx;
    const uid = user.id;
    const params = new URLSearchParams(window.location.search);
    const editCardId     = params.get('edit_card')     ? parseInt(params.get('edit_card'), 10)     : null;
    const editPurchaseId = params.get('edit_responsible') ? parseInt(params.get('edit_responsible'), 10) : null;
    let selectedCardId   = params.get('selected_card') ? parseInt(params.get('selected_card'), 10) : null;

    async function load() {
        const [{ data: cards }, { data: purchases }, { data: invoices }] = await Promise.all([
            db.from('credit_cards').select('*').eq('user_id', uid).order('sort_order').order('created_at'),
            db.from('card_purchases').select('*').eq('user_id', uid).eq('reference_month', month).order('sort_order').order('created_at'),
            db.from('card_invoice_totals').select('*').eq('user_id', uid).eq('reference_month', month),
        ]);

        if (!selectedCardId && cards?.length) selectedCardId = cards[0].id;
        const editCard     = editCardId     ? (cards     || []).find(c => c.id === editCardId)     : null;
        const editPurchase = editPurchaseId ? (purchases || []).find(p => p.id === editPurchaseId) : null;

        const invoiceMap = Object.fromEntries((invoices || []).map(i => [i.credit_card_id, parseFloat(i.amount)]));
        const purchaseSums = {};
        (purchases || []).forEach(p => { purchaseSums[p.credit_card_id] = (purchaseSums[p.credit_card_id] || 0) + parseFloat(p.amount); });

        const cardsWithTotals = (cards || []).map(c => ({
            ...c,
            invoice_total: invoiceMap[c.id] ?? null,
            responsible_total: purchaseSums[c.id] || 0,
            total: invoiceMap[c.id] !== undefined
                ? Math.max(invoiceMap[c.id] - (purchaseSums[c.id] || 0), 0)
                : (purchaseSums[c.id] || 0),
        }));

        const cardOpts = (cards || []).map(c =>
            `<option value="${c.id}" ${(editPurchase?.credit_card_id || selectedCardId) === c.id ? 'selected' : ''}>${e(c.name)}</option>`).join('');

        const selectedPurchases = (purchases || []).filter(p => p.credit_card_id === selectedCardId);
        const cardTabsHtml = cardsWithTotals.map(c => `
            <button class="flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-bold transition-colors ${c.id === selectedCardId ? 'bg-white text-[#0f1b2d]' : 'text-white/70 hover:bg-white/10'}"
                data-select-card="${c.id}" style="border-left: 4px solid ${e(c.color || '#191929')}">
                ${e(c.name)} <span class="ml-auto opacity-80">${moneyBr(c.total)}</span>
            </button>`).join('');

        const purchasesHtml = selectedPurchases.length
            ? selectedPurchases.map(p => {
                const installLabel = p.installment_total > 1 ? ` ${p.installment_number}/${p.installment_total}` : '';
                return `<article class="flex items-center justify-between gap-3 rounded-[1.05rem] bg-[#f7f7f2] p-3">
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-black">${e(p.title)}${installLabel}</p>
                        <p class="text-[11px] font-bold text-[#77798a]">${p.purchase_date || month + '-01'}</p>
                    </div>
                    <div class="flex shrink-0 items-center gap-2">
                        <p class="text-sm font-black">${moneyBr(p.amount)}</p>
                        <a class="icon-button" href="cards.html?month=${month}&selected_card=${selectedCardId}&edit_responsible=${p.id}">${editIcon()}</a>
                        <button class="icon-button danger" data-delete-purchase="${p.id}">x</button>
                        ${p.installment_group ? `<button class="icon-button danger series-delete-button" data-delete-installments="${p.installment_group}">TODOS</button>` : ''}
                    </div>
                </article>`;
            }).join('')
            : `<p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-bold text-[#77798a]">Sem compras neste cartão neste mês.</p>`;

        const selectedCard = cardsWithTotals.find(c => c.id === selectedCardId);

        document.getElementById('page-content').innerHTML = `
        <section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
            <div class="space-y-4">
                <form id="card-form" class="app-panel space-y-3">
                    <input type="hidden" id="card-edit-id" value="${editCard?.id || ''}">
                    <h2 class="text-lg font-black">${editCard ? 'Editar cartão' : 'Novo cartão'}</h2>
                    <label class="app-label">Nome<input class="app-input" id="card-name" placeholder="Nubank, Inter, Caixa" value="${e(editCard?.name || '')}" required></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="app-label">Fechamento<input class="app-input" type="date" id="card-closing"
                            value="${editCard?.closing_day ? month + '-' + String(editCard.closing_day).padStart(2,'0') : ''}"></label>
                        <label class="app-label">Vencimento<input class="app-input" type="date" id="card-due"
                            value="${editCard?.due_day ? month + '-' + String(editCard.due_day).padStart(2,'0') : ''}"></label>
                    </div>
                    <label class="app-label">Fatura em ${monthLabel(month)}
                        <input class="app-input" id="card-invoice" inputmode="decimal" placeholder="Opcional — valor fechado"
                            value="${editCard && selectedCard?.invoice_total ? selectedCard.invoice_total : ''}">
                        <span class="mt-1 block text-[10px] font-semibold leading-tight text-[#77798a]">Preencha para fixar o valor real da fatura, invalidando a soma das compras.</span>
                    </label>
                    <label class="app-label">Cor<input class="app-input h-12" type="color" id="card-color" value="${e(editCard?.color || '#191929')}"></label>
                    <button class="app-button w-full" type="submit">${editCard ? 'Salvar cartão' : 'Adicionar cartão'}</button>
                    ${editCard ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="cards.html?month=${month}">Cancelar</a>` : ''}
                </form>

                <form id="purchase-form" class="app-panel space-y-3">
                    <input type="hidden" id="purchase-edit-id" value="${editPurchase?.id || ''}">
                    <h2 class="text-lg font-black">${editPurchase ? 'Editar compra' : 'Nova compra'}</h2>
                    <label class="app-label">Cartão<select class="app-input" id="purchase-card">${cardOpts}</select></label>
                    <label class="app-label">Descrição<input class="app-input" id="purchase-title" placeholder="Mercado, farmácia…" value="${e(editPurchase?.title || '')}" required></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="app-label">Valor<input class="app-input" id="purchase-amount" inputmode="decimal" value="${e(editPurchase?.amount || '')}" required></label>
                        <label class="app-label">Data<input class="app-input" type="date" id="purchase-date" value="${e(editPurchase?.purchase_date || '')}"></label>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="app-label">Parcela nº<input class="app-input" type="number" id="purchase-inst-num" min="1" value="${editPurchase?.installment_number || 1}"></label>
                        <label class="app-label">Total de parcelas<input class="app-input" type="number" id="purchase-inst-total" min="1" value="${editPurchase?.installment_total || 1}"></label>
                    </div>
                    <button class="app-button w-full" type="submit">${editPurchase ? 'Salvar compra' : 'Adicionar compra'}</button>
                    ${editPurchase ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="cards.html?month=${month}&selected_card=${selectedCardId}">Cancelar</a>` : ''}
                </form>
            </div>

            <div class="space-y-4">
                <section class="app-panel" style="background:var(--app-sidebar-bg);color:#fff">
                    <h2 class="mb-3 text-lg font-black">Cartões</h2>
                    <div class="space-y-1" id="card-tabs">${cardTabsHtml}</div>
                </section>

                <section class="app-panel">
                    <div class="mb-4 flex items-center justify-between">
                        <h2 class="text-lg font-black">${selectedCard ? e(selectedCard.name) : 'Compras'}</h2>
                        ${selectedCard ? `<span class="text-sm font-black">${moneyBr(selectedCard.total)}</span>` : ''}
                    </div>
                    <div class="space-y-2" id="purchases-list">${purchasesHtml}</div>
                </section>
            </div>
        </section>`;

        document.getElementById('card-tabs').addEventListener('click', ev => {
            const btn = ev.target.closest('[data-select-card]');
            if (btn) {
                const url = new URL(window.location.href);
                url.searchParams.set('selected_card', btn.dataset.selectCard);
                window.location.href = url.toString();
            }
        });

        document.getElementById('card-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const eid = document.getElementById('card-edit-id').value;
            const closingStr = document.getElementById('card-closing').value;
            const dueStr     = document.getElementById('card-due').value;
            const invoiceVal = document.getElementById('card-invoice').value.trim();
            const payload = {
                user_id: uid,
                name: document.getElementById('card-name').value.trim(),
                closing_day: closingStr ? parseInt(closingStr.slice(8, 10), 10) : null,
                due_day:     dueStr     ? parseInt(dueStr.slice(8, 10), 10) : null,
                color: document.getElementById('card-color').value,
            };
            let cardId;
            if (eid) {
                await db.from('credit_cards').update(payload).eq('id', parseInt(eid, 10)).eq('user_id', uid);
                cardId = parseInt(eid, 10);
            } else {
                const { data: created } = await db.from('credit_cards').insert(payload).select().single();
                cardId = created?.id;
            }
            if (invoiceVal && cardId) {
                await db.from('card_invoice_totals').upsert({ user_id: uid, credit_card_id: cardId, reference_month: month, amount: parseFloat(invoiceVal.replace(',', '.')) || 0 });
            }
            window.location.href = `cards.html?month=${month}&selected_card=${cardId || ''}`;
        });

        document.getElementById('purchase-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const eid = document.getElementById('purchase-edit-id').value;
            const instNum   = parseInt(document.getElementById('purchase-inst-num').value, 10) || 1;
            const instTotal = Math.max(instNum, parseInt(document.getElementById('purchase-inst-total').value, 10) || 1);
            const basePayload = {
                user_id: uid,
                credit_card_id: parseInt(document.getElementById('purchase-card').value, 10) || null,
                title: document.getElementById('purchase-title').value.trim(),
                amount: parseFloat(document.getElementById('purchase-amount').value.replace(',', '.')) || 0,
                purchase_date: document.getElementById('purchase-date').value || null,
                installment_number: instNum,
                installment_total: instTotal,
            };
            if (eid) {
                await db.from('card_purchases').update({ ...basePayload, reference_month: month }).eq('id', parseInt(eid, 10)).eq('user_id', uid);
            } else if (instTotal > 1) {
                const group = crypto.randomUUID();
                const inserts = [];
                for (let n = 1; n <= instTotal; n++) {
                    const refDate = new Date(month + '-01');
                    refDate.setMonth(refDate.getMonth() + (n - instNum));
                    inserts.push({ ...basePayload, reference_month: refDate.toISOString().slice(0, 7), installment_number: n, installment_group: group, installment_auto: true });
                }
                await db.from('card_purchases').insert(inserts);
            } else {
                await db.from('card_purchases').insert({ ...basePayload, reference_month: month });
            }
            window.location.href = `cards.html?month=${month}&selected_card=${selectedCardId}`;
        });

        document.getElementById('purchases-list').addEventListener('click', async ev => {
            const btnP = ev.target.closest('[data-delete-purchase]');
            if (btnP && confirm('Excluir esta compra?')) {
                await db.from('card_purchases').delete().eq('id', parseInt(btnP.dataset.deletePurchase, 10)).eq('user_id', uid);
                load(); return;
            }
            const btnI = ev.target.closest('[data-delete-installments]');
            if (btnI && confirm('Excluir todas as parcelas?')) {
                await db.from('card_purchases').delete().eq('user_id', uid).eq('installment_group', btnI.dataset.deleteInstallments);
                load();
            }
        });
    }

    await load();
    initAppBehaviors();
})();
