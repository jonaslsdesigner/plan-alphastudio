(async () => {
    const ctx = await initLayout('roadmap.html', 'Roteiro');
    if (!ctx) return;
    const { user, month } = ctx;
    const uid = user.id;

    const start = month + '-01';
    const endD  = new Date(month + '-01'); endD.setMonth(endD.getMonth() + 1); endD.setDate(0);
    const endStr = endD.toISOString().slice(0, 10);

    const [
        { data: incomeSources },
        { data: bills },
        { data: cards },
        { data: cardPurchases },
        { data: transactions },
        { data: invoiceTotals },
        { data: statuses },
    ] = await Promise.all([
        db.from('income_sources').select('*').eq('user_id', uid).eq('reference_month', month),
        db.from('monthly_bills').select('*').eq('user_id', uid).eq('reference_month', month).order('created_at'),
        db.from('credit_cards').select('*').eq('user_id', uid),
        db.from('card_purchases').select('*').eq('user_id', uid).eq('reference_month', month),
        db.from('transactions').select('*').eq('user_id', uid).gte('due_date', start).lte('due_date', endStr),
        db.from('card_invoice_totals').select('*').eq('user_id', uid).eq('reference_month', month),
        db.from('monthly_item_statuses').select('*').eq('user_id', uid).eq('reference_month', month),
    ]);

    const statusMap = {};
    (statuses || []).forEach(s => { statusMap[s.item_type + ':' + s.item_id] = s; });

    const invoiceMap   = Object.fromEntries((invoiceTotals || []).map(i => [i.credit_card_id, parseFloat(i.amount)]));
    const purchaseSums = {};
    (cardPurchases || []).forEach(p => { purchaseSums[p.credit_card_id] = (purchaseSums[p.credit_card_id] || 0) + parseFloat(p.amount); });

    const items = [];

    (incomeSources || []).forEach(inc => {
        const key = 'income_source:' + inc.id;
        const s   = statusMap[key];
        items.push({
            item_type: 'income_source', item_id: inc.id,
            title: inc.title, category: 'Outras rendas', direction: 'income',
            amount: parseFloat(inc.amount),
            date: (s?.actual_date || inc.received_date || month + '-01'),
            status: s?.status || inc.status || 'pending',
            is_realized: ['received','paid'].includes(s?.status || inc.status),
            source_url: `income.html?month=${month}&edit=${inc.id}`,
        });
    });

    (bills || []).forEach(b => {
        const key = 'monthly_bill:' + b.id;
        const s   = statusMap[key];
        const day = String(b.due_day || 1).padStart(2, '0');
        items.push({
            item_type: 'monthly_bill', item_id: b.id,
            title: b.title, category: 'Conta', direction: 'expense',
            amount: parseFloat(b.amount),
            date: (s?.actual_date || month + '-' + day),
            status: s?.status || 'pending',
            is_realized: s?.status === 'paid',
            source_url: `bills.html?month=${month}&edit=${b.id}`,
        });
    });

    (cards || []).forEach(c => {
        const invoice = invoiceMap[c.id];
        const total   = invoice !== undefined ? invoice : (purchaseSums[c.id] || 0);
        if (total <= 0) return;
        const key = 'card_invoice:' + c.id;
        const s   = statusMap[key];
        const day = String(c.due_day || 1).padStart(2, '0');
        items.push({
            item_type: 'card_invoice', item_id: c.id,
            title: 'Fatura ' + c.name, category: 'Cartão', direction: 'expense',
            amount: total,
            date: (s?.actual_date || month + '-' + day),
            status: s?.status || 'pending',
            is_realized: s?.status === 'paid',
            source_url: `cards.html?month=${month}&selected_card=${c.id}`,
        });
    });

    (transactions || []).forEach(t => {
        items.push({
            item_type: 'transaction', item_id: t.id,
            title: t.title, category: 'Lançamento', direction: t.type,
            amount: parseFloat(t.amount),
            date: (t.paid_at || t.due_date),
            status: t.status === 'paid' ? 'paid' : 'pending',
            is_realized: t.status === 'paid',
            source_url: `transactions.html?month=${month}&edit=${t.id}`,
        });
    });

    items.sort((a, b) => a.date.localeCompare(b.date) || (a.direction === 'income' ? -1 : 1));

    let running = 0;
    items.forEach(item => {
        const signed = (item.direction === 'income' ? 1 : -1) * item.amount;
        running += signed;
        item.signed_amount = signed;
        item.running_balance = running;
    });

    const received = items.filter(i => i.direction === 'income' && i.is_realized).reduce((s, i) => s + i.amount, 0);
    const paid     = items.filter(i => i.direction === 'expense' && i.is_realized).reduce((s, i) => s + i.amount, 0);
    const plannedIncome   = items.filter(i => i.direction === 'income').reduce((s, i) => s + i.amount, 0);
    const plannedExpense  = items.filter(i => i.direction === 'expense').reduce((s, i) => s + i.amount, 0);

    const statusBadge = (item) => {
        const isIncome = item.direction === 'income';
        const done = item.is_realized;
        const label = isIncome ? (done ? 'Recebido' : 'Previsto') : (done ? 'Pago' : 'Pendente');
        const cls   = done ? 'text-emerald-600' : 'text-[#77798a]';
        return `<span class="text-[10px] font-bold ${cls}">${label}</span>`;
    };

    const itemsHtml = items.map(item => `
        <div class="flex items-center gap-3 rounded-[1.05rem] bg-white p-3 border border-[#f0f0f0]">
            <button class="icon-button ${item.is_realized ? 'bg-emerald-50 text-emerald-600' : ''}" data-toggle-status
                data-item-type="${item.item_type}" data-item-id="${item.item_id}"
                data-current-status="${item.status}" data-direction="${item.direction}" title="Alternar status">
                <svg viewBox="0 0 24 24" style="width:1rem;height:1rem;fill:currentColor"><path d="${item.is_realized ? 'M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17Z' : 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2Zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8Z'}"/></svg>
            </button>
            <div class="min-w-0 flex-1">
                <a href="${item.source_url}" class="truncate text-sm font-black block hover:underline">${e(item.title)}</a>
                <p class="text-[10px] font-bold text-[#77798a]">${e(item.category)} · ${item.date.slice(8,10)}/${item.date.slice(5,7)}</p>
            </div>
            <div class="shrink-0 text-right">
                <p class="text-sm font-black ${item.direction === 'income' ? 'text-emerald-600' : 'text-[#24589b]'}">${item.direction === 'income' ? '+' : '-'}${moneyBr(item.amount)}</p>
                ${statusBadge(item)}
            </div>
            <span class="shrink-0 text-xs font-bold text-[#77798a]">${moneyBr(item.running_balance)}</span>
        </div>`).join('');

    document.getElementById('page-content').innerHTML = `
    <section class="grid gap-4 xl:grid-cols-[1fr_20rem]">
        <div class="space-y-4">
            <section class="app-panel">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-black">Roteiro de ${monthLabel(month)}</h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black">${items.length} itens</span>
                </div>
                <div class="space-y-2" id="roadmap-list">
                    ${items.length ? itemsHtml : `<p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-bold text-[#77798a]">Sem itens cadastrados neste mês.</p>`}
                </div>
            </section>
        </div>
        <aside class="space-y-4">
            <section class="app-panel">
                <h2 class="mb-3 text-lg font-black">Resumo</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="font-bold text-[#77798a]">Receita prevista</span><span class="font-black text-emerald-600">${moneyBr(plannedIncome)}</span></div>
                    <div class="flex justify-between"><span class="font-bold text-[#77798a]">Despesa prevista</span><span class="font-black text-[#24589b]">${moneyBr(plannedExpense)}</span></div>
                    <div class="flex justify-between"><span class="font-bold text-[#77798a]">Recebido</span><span class="font-black">${moneyBr(received)}</span></div>
                    <div class="flex justify-between"><span class="font-bold text-[#77798a]">Pago</span><span class="font-black">${moneyBr(paid)}</span></div>
                    <hr style="border-color:#e8e8e8">
                    <div class="flex justify-between"><span class="font-bold">Saldo real</span><span class="font-black ${received - paid >= 0 ? 'text-emerald-600' : 'text-red-500'}">${moneyBr(received - paid)}</span></div>
                    <div class="flex justify-between"><span class="font-bold">Saldo projetado</span><span class="font-black ${plannedIncome - plannedExpense >= 0 ? 'text-emerald-600' : 'text-red-500'}">${moneyBr(plannedIncome - plannedExpense)}</span></div>
                </div>
            </section>
        </aside>
    </section>`;

    document.getElementById('roadmap-list').addEventListener('click', async ev => {
        const btn = ev.target.closest('[data-toggle-status]');
        if (!btn) return;
        const itemType    = btn.dataset.itemType;
        const itemId      = parseInt(btn.dataset.itemId, 10);
        const curStatus   = btn.dataset.currentStatus;
        const direction   = btn.dataset.direction;
        const newStatus   = curStatus === 'paid' || curStatus === 'received' ? 'pending'
            : (direction === 'income' ? 'received' : 'paid');

        if (itemType === 'transaction') {
            await db.from('transactions').update({ status: newStatus === 'paid' ? 'paid' : 'pending', paid_at: newStatus === 'paid' ? new Date().toISOString().slice(0, 10) : null }).eq('id', itemId).eq('user_id', uid);
        } else if (itemType === 'income_source') {
            await db.from('income_sources').update({ status: newStatus }).eq('id', itemId).eq('user_id', uid);
        } else {
            await db.from('monthly_item_statuses').upsert({ user_id: uid, item_type: itemType, item_id: itemId, reference_month: month, status: newStatus, actual_date: ['paid','received'].includes(newStatus) ? new Date().toISOString().slice(0, 10) : null });
        }
        window.location.reload();
    });

    initAppBehaviors();
})();
