(async () => {
    const ctx = await initLayout('income.html', 'Rendas');
    if (!ctx) return;
    const { user, month } = ctx;
    const uid = user.id;

    const INCOME_TYPE_LABELS = { first_half: 'Quinzena', month_end: 'Final de Mês', other: 'Outras Rendas' };
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit') ? parseInt(params.get('edit'), 10) : null;

    async function load() {
        const { data: incomes } = await db.from('income_sources').select('*')
            .eq('user_id', uid).eq('reference_month', month)
            .order('sort_order').order('created_at');

        const editItem = editId ? (incomes || []).find(i => i.id === editId) || null : null;
        const today    = new Date().toISOString().slice(0, 10);
        const defaultDate = month + '-' + String(Math.min(parseInt(today.slice(8, 10), 10), new Date(month + '-01T00:00:00').setMonth(new Date(month + '-01T00:00:00').getMonth() + 1) - 1)).padStart(2, '0');
        const total = (incomes || []).reduce((s, r) => s + parseFloat(r.amount), 0);

        const typeOptions = Object.entries(INCOME_TYPE_LABELS).map(([v, l]) =>
            `<option value="${v}" ${(editItem?.income_type || 'other') === v ? 'selected' : ''}>${l}</option>`
        ).join('');

        document.getElementById('page-content').innerHTML = `
        <section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
            <div class="space-y-4">
                <form id="income-form" class="app-panel space-y-3">
                    <input type="hidden" id="edit-id" value="${editItem?.id || ''}">
                    <h2 class="text-lg font-black">${editItem ? 'Editar renda' : 'Nova renda do mês'}</h2>
                    <label class="app-label">Tipo<select class="app-input" id="income-type">${typeOptions}</select></label>
                    <label class="app-label">Referência<input class="app-input" id="income-title" placeholder="Quinzena, final do mês, freelance" value="${e(editItem?.title || '')}" required></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="app-label">Valor<input class="app-input" id="income-amount" inputmode="decimal" value="${e(editItem?.amount || '')}" required></label>
                        <label class="app-label">Data<input class="app-input" type="date" id="income-date" value="${e(editItem?.received_date || defaultDate)}" required></label>
                    </div>
                    <label class="app-label">Status<select class="app-input" id="income-status">
                        <option value="pending" ${(editItem?.status || 'pending') !== 'received' ? 'selected' : ''}>Previsto</option>
                        <option value="received" ${editItem?.status === 'received' ? 'selected' : ''}>Recebido</option>
                    </select></label>
                    <button class="app-button w-full" type="submit">${editItem ? 'Salvar renda' : 'Adicionar renda'}</button>
                    ${editItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="income.html?month=${month}">Cancelar edição</a>` : ''}
                </form>
            </div>

            <section class="app-panel">
                <div class="app-income-summary mb-4">
                    <div class="app-income-summary-copy">
                        <h2 class="text-lg font-black">Rendas de ${monthLabel(month)}</h2>
                        <p class="text-sm font-semibold text-[#77798a]">Quinzena, final do mês e outras rendas variam mês a mês.</p>
                    </div>
                    <span class="app-pill app-value-pill app-income-summary-total text-white" style="background:var(--app-blue)">${moneyBr(total)}</span>
                </div>
                <div class="space-y-2" id="income-list">
                    ${(incomes || []).length ? (incomes || []).map(inc => `
                        <article class="app-income-item flex items-center justify-between gap-3 rounded-[1.05rem] bg-white p-3">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-black">${e(inc.title)}</p>
                                <p class="text-[11px] font-bold text-[#77798a]">${e(INCOME_TYPE_LABELS[inc.income_type] || 'Outras Rendas')} - ${(inc.received_date || month + '-01').slice(8, 10)}/${(inc.received_date || month + '-01').slice(5, 7)} - ${inc.status === 'received' ? 'Recebido' : 'Previsto'}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <p class="text-sm font-black text-emerald-600">${moneyBr(inc.amount)}</p>
                                <a class="icon-button" href="income.html?month=${month}&edit=${inc.id}">${editIcon()}</a>
                                <button class="icon-button danger" data-delete="${inc.id}">x</button>
                            </div>
                        </article>`).join('')
                    : `<p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-bold text-[#77798a]">Sem rendas cadastradas neste mês.</p>`}
                </div>
            </section>
        </section>`;

        document.getElementById('income-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const editIdVal = document.getElementById('edit-id').value;
            const payload = {
                user_id: uid,
                reference_month: month,
                title: document.getElementById('income-title').value.trim(),
                amount: parseFloat(document.getElementById('income-amount').value.replace(',', '.')) || 0,
                income_type: document.getElementById('income-type').value,
                received_date: document.getElementById('income-date').value || null,
                status: document.getElementById('income-status').value,
            };
            if (editIdVal) {
                await db.from('income_sources').update(payload).eq('id', parseInt(editIdVal, 10)).eq('user_id', uid);
                window.location.href = `income.html?month=${month}`;
            } else {
                await db.from('income_sources').insert(payload);
                load();
            }
        });

        document.getElementById('income-list').addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-delete]');
            if (!btn) return;
            if (!confirm('Excluir esta renda?')) return;
            await db.from('income_sources').delete().eq('id', parseInt(btn.dataset.delete, 10)).eq('user_id', uid);
            load();
        });
    }

    await load();
    initAppBehaviors();
})();
