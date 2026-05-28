(async () => {
    const ctx = await initLayout('transactions.html', 'Lançamentos');
    if (!ctx) return;
    const { user, month } = ctx;
    const uid = user.id;

    const start = month + '-01';
    const endD  = new Date(month + '-01'); endD.setMonth(endD.getMonth() + 1); endD.setDate(0);
    const endStr = endD.toISOString().slice(0, 10);
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit') ? parseInt(params.get('edit'), 10) : null;

    async function load() {
        const [{ data: txs }, { data: cats }, { data: accounts }] = await Promise.all([
            db.from('transactions').select('*, categories(name,color), accounts(name)').eq('user_id', uid).gte('due_date', start).lte('due_date', endStr).order('due_date').order('id', { ascending: false }),
            db.from('categories').select('*').eq('user_id', uid).order('sort_order').order('created_at'),
            db.from('accounts').select('*').eq('user_id', uid).order('sort_order').order('created_at'),
        ]);

        const editItem = editId ? (txs || []).find(t => t.id === editId) || null : null;
        const today    = new Date().toISOString().slice(0, 10);
        const defaultDate = today.slice(0, 7) === month ? today : month + '-01';

        const catOptions = `<option value="">Sem categoria</option>` + (cats || []).map(c =>
            `<option value="${c.id}" ${editItem?.category_id === c.id ? 'selected' : ''}>${e(c.name)}</option>`).join('');
        const accOptions = `<option value="">Sem conta</option>` + (accounts || []).map(a =>
            `<option value="${a.id}" ${editItem?.account_id === a.id ? 'selected' : ''}>${e(a.name)}</option>`).join('');

        document.getElementById('page-content').innerHTML = `
        <section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
            <form id="tx-form" class="app-panel space-y-3">
                <input type="hidden" id="edit-id" value="${editItem?.id || ''}">
                <h2 class="text-lg font-black">${editItem ? 'Editar lançamento' : 'Novo lançamento'}</h2>
                <label class="app-label">Título<input class="app-input" id="tx-title" required value="${e(editItem?.title || '')}"></label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="app-label">Tipo<select class="app-input" id="tx-type">
                        <option value="expense" ${(editItem?.type || 'expense') === 'expense' ? 'selected' : ''}>Gasto</option>
                        <option value="income"  ${editItem?.type === 'income'  ? 'selected' : ''}>Ganho</option>
                    </select></label>
                    <label class="app-label">Valor<input class="app-input" id="tx-amount" required inputmode="decimal" value="${e(editItem?.amount || '')}"></label>
                </div>
                <label class="app-label">Categoria<select class="app-input" id="tx-category">${catOptions}</select></label>
                <label class="app-label">Conta<select class="app-input" id="tx-account">${accOptions}</select></label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="app-label">Vencimento<input class="app-input" type="date" id="tx-due" required value="${e(editItem?.due_date || defaultDate)}"></label>
                    <label class="app-label">Status<select class="app-input" id="tx-status">
                        <option value="pending" ${(editItem?.status || 'pending') === 'pending' ? 'selected' : ''}>Pendente</option>
                        <option value="paid"    ${editItem?.status === 'paid' ? 'selected' : ''}>Pago</option>
                    </select></label>
                </div>
                <label class="app-label">Notas<textarea class="app-input min-h-20" id="tx-notes">${e(editItem?.notes || '')}</textarea></label>
                <button class="app-button w-full" type="submit">${editItem ? 'Salvar alterações' : 'Adicionar'}</button>
                ${editItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="transactions.html?month=${month}">Cancelar</a>` : ''}
            </form>

            <section class="app-panel">
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="text-lg font-black">Planilha do mês</h2>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black">${monthLabel(month)}</span>
                </div>
                <div class="space-y-2" id="tx-list">
                    ${(txs || []).length ? (txs || []).map(t => `
                        <article class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black">${e(t.title)}</p>
                                <p class="text-[11px] font-bold text-slate-400">${e(t.categories?.name || 'Sem categoria')} - ${t.due_date.slice(8, 10)}/${t.due_date.slice(5, 7)}/${t.due_date.slice(0, 4)} - ${t.status === 'paid' ? 'Pago' : 'Pendente'}</p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <p class="text-sm font-black ${t.type === 'income' ? 'text-emerald-600' : 'text-slate-950'}">${moneyBr(t.amount)}</p>
                                <a class="icon-button" href="transactions.html?month=${month}&edit=${t.id}">${editIcon()}</a>
                                <button class="icon-button danger" data-delete="${t.id}">x</button>
                            </div>
                        </article>`).join('')
                    : `<p class="rounded-2xl bg-slate-50 p-4 text-sm font-bold text-slate-400">Sem lançamentos por enquanto.</p>`}
                </div>
            </section>
        </section>`;

        document.getElementById('tx-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const editIdVal = document.getElementById('edit-id').value;
            const status = document.getElementById('tx-status').value;
            const payload = {
                user_id: uid,
                type: document.getElementById('tx-type').value,
                title: document.getElementById('tx-title').value.trim(),
                amount: parseFloat(document.getElementById('tx-amount').value.replace(',', '.')) || 0,
                category_id: parseInt(document.getElementById('tx-category').value, 10) || null,
                account_id: parseInt(document.getElementById('tx-account').value, 10) || null,
                due_date: document.getElementById('tx-due').value,
                paid_at: status === 'paid' ? new Date().toISOString().slice(0, 10) : null,
                status,
                notes: document.getElementById('tx-notes').value.trim(),
            };
            if (editIdVal) {
                await db.from('transactions').update(payload).eq('id', parseInt(editIdVal, 10)).eq('user_id', uid);
                window.location.href = `transactions.html?month=${month}`;
            } else {
                await db.from('transactions').insert(payload);
                load();
            }
        });

        document.getElementById('tx-list').addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-delete]');
            if (!btn || !confirm('Excluir este lançamento?')) return;
            await db.from('transactions').delete().eq('id', parseInt(btn.dataset.delete, 10)).eq('user_id', uid);
            load();
        });
    }

    await load();
    initAppBehaviors();
})();
