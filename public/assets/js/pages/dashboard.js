(async () => {
    const ctx = await initLayout('index.html', 'Dashboard');
    if (!ctx) return;
    const { user, month } = ctx;
    const uid = user.id;

    const start = month + '-01';
    const end   = new Date(month + '-01');
    end.setMonth(end.getMonth() + 1);
    end.setDate(0);
    const endStr = end.toISOString().slice(0, 10);

    const [
        { data: incomeSources },
        { data: bills },
        { data: cards },
        { data: cardPurchases },
        { data: commitments },
        { data: transactions },
        { data: goals },
    ] = await Promise.all([
        db.from('income_sources').select('*').eq('user_id', uid).eq('reference_month', month),
        db.from('monthly_bills').select('*, categories(name,color)').eq('user_id', uid).eq('reference_month', month).order('sort_order').order('created_at'),
        db.from('credit_cards').select('*').eq('user_id', uid).order('sort_order').order('created_at'),
        db.from('card_purchases').select('*').eq('user_id', uid).eq('reference_month', month),
        db.from('commitments').select('*').eq('user_id', uid).eq('status', 'active').order('sort_order').order('created_at'),
        db.from('transactions').select('*, categories(name,color), accounts(name)').eq('user_id', uid).gte('due_date', start).lte('due_date', endStr).order('due_date').order('id', { ascending: false }),
        db.from('goals').select('*').eq('user_id', uid).order('created_at', { ascending: false }),
    ]);

    const { data: invoiceTotals } = await db.from('card_invoice_totals').select('*').eq('user_id', uid).eq('reference_month', month);

    // ── Calcula totais ────────────────────────────────────────────────────────
    const otherIncome    = (incomeSources || []).reduce((s, r) => s + parseFloat(r.amount), 0);
    const billsTotal     = (bills || []).reduce((s, r) => s + parseFloat(r.amount), 0);
    const txExpenses     = (transactions || []).filter(t => t.type === 'expense').reduce((s, r) => s + parseFloat(r.amount), 0);
    const txIncome       = (transactions || []).filter(t => t.type === 'income').reduce((s, r) => s + parseFloat(r.amount), 0);
    const totalIncome    = otherIncome + txIncome;

    const invoiceMap = Object.fromEntries((invoiceTotals || []).map(i => [i.credit_card_id, parseFloat(i.amount)]));
    const purchaseSums = {};
    (cardPurchases || []).forEach(p => {
        purchaseSums[p.credit_card_id] = (purchaseSums[p.credit_card_id] || 0) + parseFloat(p.amount);
    });
    const cardsTotal = (cards || []).reduce((s, c) => {
        const invoiced = invoiceMap[c.id];
        const fromPurchases = purchaseSums[c.id] || 0;
        return s + (invoiced !== undefined ? invoiced : fromPurchases);
    }, 0);

    const currentYear   = parseInt(month.slice(0, 4), 10);
    const yearCommitments = (commitments || []).filter(c => c.start_year === currentYear);
    const commitmentsTotal = yearCommitments.reduce((s, r) => s + parseFloat(r.amount), 0);
    const plannedExpenses  = billsTotal + txExpenses + cardsTotal;
    const remaining        = totalIncome - plannedExpenses;

    // ── Cards métrica ─────────────────────────────────────────────────────────
    const metricCards = [
        { label: 'Rendas do mês',      value: totalIncome,  cls: 'app-accent-card', hint: 'Entradas dinâmicas',   href: 'income.html' },
        { label: 'Contas',             value: billsTotal,   cls: 'bg-white',        hint: 'Custos fixos',         href: 'bills.html' },
        { label: 'Cartões de Crédito', value: cardsTotal,   cls: 'bg-white',        hint: 'Responsáveis',         href: 'cards.html' },
        { label: 'Sobra prevista',     value: remaining,    cls: remaining >= 0 ? 'bg-[#f2ffd0]' : 'bg-[#fff0ee]', hint: 'Depois dos custos', href: null },
    ];

    const metricHtml = metricCards.map(({ label, value, cls, hint, href }) => {
        const inner = `<article class="app-card dashboard-metric-card ${cls} p-4">
            <p class="text-xs font-bold opacity-60">${e(label)}</p>
            <p class="mt-5 text-2xl font-black tracking-normal">${moneyBr(value)}</p>
            <p class="mt-1 text-[11px] font-bold opacity-50">${e(hint)}</p>
        </article>`;
        return href ? `<a href="${href}?month=${month}" class="app-card-link" aria-label="${e(label)}">${inner}</a>` : inner;
    }).join('');

    // ── Breakdown por categoria ───────────────────────────────────────────────
    const breakdown = [
        { name: 'Contas',               color: '#6d5df5', total: billsTotal },
        { name: 'Cartões de Crédito',   color: '#191929', total: cardsTotal },
        { name: 'Lançamentos',          color: '#8f91a0', total: txExpenses },
    ].filter(r => r.total > 0);

    const breakdownHtml = breakdown.map(row => {
        const pct = plannedExpenses > 0 ? Math.min(100, (row.total / plannedExpenses) * 100) : 0;
        return `<div>
            <div class="mb-1 flex justify-between text-sm font-bold">
                <span>${e(row.name)}</span><span>${moneyBr(row.total)}</span>
            </div>
            <div class="h-2 rounded-full bg-[#eeeeE8]">
                <div class="h-2 rounded-full" style="width:${pct.toFixed(1)}%;background:${row.color}"></div>
            </div>
        </div>`;
    }).join('');

    // ── Movimentos recentes ───────────────────────────────────────────────────
    const latestItems = [
        ...(incomeSources || []).map(i => ({ title: i.title, category: 'Renda extra', amount: i.amount, type: 'income', due_date: month + '-01' })),
        ...(bills || []).map(b => ({ title: b.title, category: 'Conta', amount: b.amount, type: 'expense', due_date: month + '-' + String(b.due_day).padStart(2, '0') })),
        ...(cardPurchases || []).map(p => ({ title: p.title, category: 'Cartão', amount: p.amount, type: 'expense', due_date: p.purchase_date || month + '-01' })),
    ].sort((a, b) => b.due_date.localeCompare(a.due_date)).slice(0, 8);

    const latestHtml = latestItems.length
        ? latestItems.map(t => `
            <div class="flex items-center justify-between rounded-[1.05rem] bg-[#f7f7f2] p-3">
                <div class="min-w-0">
                    <p class="truncate text-sm font-black">${e(t.title)}</p>
                    <p class="text-[11px] font-bold text-[#77798a]">${e(t.category)} - ${t.due_date.slice(8, 10)}/${t.due_date.slice(5, 7)}</p>
                </div>
                <p class="shrink-0 text-sm font-black ${t.type === 'income' ? 'text-emerald-600' : 'text-[#24589b]'}">${moneyBr(t.amount)}</p>
            </div>`).join('')
        : `<p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-bold text-[#77798a]">Sem rendas extras ou responsáveis no cartão neste mês.</p>`;

    // ── Bills list ────────────────────────────────────────────────────────────
    const billsHtml = (bills || []).length
        ? (bills || []).map(b => `
            <div class="flex items-center justify-between rounded-[1.05rem] border border-[#ecece4] bg-white p-3">
                <div>
                    <p class="text-sm font-black">${e(b.title)}</p>
                    <p class="text-[11px] font-bold text-[#77798a]">Dia ${b.due_day}</p>
                </div>
                <p class="text-sm font-black">${moneyBr(b.amount)}</p>
            </div>`).join('')
        : `<p class="text-sm font-bold text-[#77798a]">Sem contas neste mês.</p>`;

    // ── Commitments list ──────────────────────────────────────────────────────
    const commitmentsHtml = yearCommitments.length
        ? yearCommitments.map(c => `
            <div class="rounded-[1.05rem] bg-white/8 p-3">
                <div class="flex justify-between gap-2 text-sm font-bold">
                    <span>${e(c.title)}</span><span>${moneyBr(c.amount)}</span>
                </div>
                <p class="mt-1 text-[11px] font-bold text-white/50">Ano ${c.start_year}</p>
            </div>`).join('')
        : `<p class="text-sm font-semibold text-white/60">Sem compromissos ativos neste ano.</p>`;

    // ── Render ────────────────────────────────────────────────────────────────
    document.getElementById('page-content').innerHTML = `
    <section class="dashboard-home grid gap-4 xl:grid-cols-[1fr_21rem]">
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">${metricHtml}</div>

            <section class="app-panel">
                <div class="mb-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black">Resumo do mês</h2>
                        <p class="text-xs font-semibold text-[#77798a]">${monthLabel(month)}</p>
                    </div>
                    <a class="app-mini-button" href="income.html?month=${month}">Rendas</a>
                </div>
                <div class="space-y-3">${breakdownHtml || '<p class="text-sm font-bold text-[#77798a]">Sem despesas registradas.</p>'}</div>
            </section>

            <section class="app-panel">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-black">Movimentos recentes</h2>
                    <a class="app-mini-button" href="cards.html?month=${month}">Cartões</a>
                </div>
                <div class="space-y-2">${latestHtml}</div>
            </section>
        </div>

        <aside class="space-y-4">
            <section class="app-panel">
                <h2 class="text-lg font-black">Contas do mês</h2>
                <div class="mt-3 space-y-2">${billsHtml}</div>
            </section>
            <section class="app-panel app-themed-panel">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-black">Compromissos do ano</h2>
                    <span class="app-themed-pill app-pill app-value-pill">${moneyBr(commitmentsTotal)}</span>
                </div>
                <div class="mt-3 space-y-3">${commitmentsHtml}</div>
            </section>
        </aside>
    </section>`;

    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js').catch(() => {});
    }
    initAppBehaviors();
})();
