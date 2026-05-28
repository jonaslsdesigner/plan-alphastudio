(async () => {
    const ctx = await initLayout('bills.html', 'Contas');
    if (!ctx) return;
    const { user, month } = ctx;
    const uid = user.id;
    const params  = new URLSearchParams(window.location.search);
    const editId  = params.get('edit') ? parseInt(params.get('edit'), 10) : null;
    const editCatId = params.get('edit_category') ? parseInt(params.get('edit_category'), 10) : null;

    async function load() {
        const [{ data: bills }, { data: cats }] = await Promise.all([
            db.from('monthly_bills').select('*, categories(name,color)').eq('user_id', uid).eq('reference_month', month).order('sort_order').order('created_at'),
            db.from('categories').select('*').eq('user_id', uid).order('sort_order').order('created_at'),
        ]);

        const editItem    = editId    ? (bills || []).find(b => b.id === editId)    : null;
        const editCatItem = editCatId ? (cats  || []).find(c => c.id === editCatId) : null;

        const billDueDay  = editItem?.due_day || 10;
        const billDueDate = month + '-' + String(billDueDay).padStart(2, '0');
        const catOpts = `<option value="">Sem categoria</option>` + (cats || []).map(c =>
            `<option value="${c.id}" ${editItem?.category_id === c.id ? 'selected' : ''}>${e(c.name)}</option>`).join('');

        document.getElementById('page-content').innerHTML = `
        <section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
            <div class="space-y-4">
                <form id="bill-form" class="app-panel space-y-3">
                    <input type="hidden" id="bill-edit-id" value="${editItem?.id || ''}">
                    <h2 class="text-lg font-black">${editItem ? 'Editar conta' : 'Nova conta/custo'}</h2>
                    <label class="app-label">Título<input class="app-input" id="bill-title" required placeholder="Aluguel, energia, internet" value="${e(editItem?.title || '')}"></label>
                    <label class="app-label">Categoria<select class="app-input" id="bill-category">${catOpts}</select></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="app-label">Valor<input class="app-input" id="bill-amount" inputmode="decimal" required value="${e(editItem?.amount || '')}"></label>
                        <label class="app-label">Vencimento<input class="app-input" type="date" id="bill-due-date" value="${billDueDate}" required></label>
                    </div>
                    <label class="flex items-center gap-2 text-sm font-bold">
                        <input type="checkbox" id="bill-recurring" ${(editItem?.auto_create ?? true) ? 'checked' : ''}> Conta recorrente
                    </label>
                    <p class="text-xs font-semibold text-[#77798a]">Toda conta/custo adicionada aqui entra automaticamente no dashboard do mês.</p>
                    <button class="app-button w-full" type="submit">${editItem ? 'Salvar conta' : 'Adicionar conta'}</button>
                    ${editItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="bills.html?month=${month}">Cancelar</a>` : ''}
                </form>

                <form id="cat-form" class="app-panel space-y-3">
                    <input type="hidden" id="cat-edit-id" value="${editCatItem?.id || ''}">
                    <h2 class="text-lg font-black">${editCatItem ? 'Editar categoria' : 'Nova categoria'}</h2>
                    <label class="app-label">Nome<input class="app-input" id="cat-name" required placeholder="Moradia, transporte" value="${e(editCatItem?.name || '')}"></label>
                    <label class="app-label">Tipo<select class="app-input" id="cat-type">
                        <option value="expense" ${(editCatItem?.type || 'expense') === 'expense' ? 'selected' : ''}>Gasto</option>
                        <option value="income"  ${editCatItem?.type === 'income' ? 'selected' : ''}>Ganho</option>
                    </select></label>
                    <label class="app-label">Cor<input class="app-input h-12" type="color" id="cat-color" value="${e(editCatItem?.color || '#9b57e3')}"></label>
                    <label class="app-label">Ícone<input class="app-input" id="cat-icon" value="${e(editCatItem?.icon || 'tag')}"></label>
                    <button class="app-button w-full" type="submit">${editCatItem ? 'Salvar categoria' : 'Adicionar categoria'}</button>
                    ${editCatItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="bills.html?month=${month}">Cancelar</a>` : ''}
                </form>
            </div>

            <div class="space-y-4">
                <section class="app-panel">
                    <h2 class="mb-4 text-lg font-black">Custos organizados</h2>
                    <div class="overflow-hidden rounded-2xl border border-slate-100">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-indigo-600 text-white">
                                <tr><th class="p-3">Conta</th><th class="p-3">Dia</th><th class="p-3 text-right">Valor</th><th class="p-3"></th></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white" id="bills-tbody">
                                ${(bills || []).length ? (bills || []).map(b => `
                                    <tr>
                                        <td class="p-3 font-black">${e(b.title)}<p class="text-[11px] font-bold text-slate-400">${e(b.categories?.name || 'Sem categoria')}</p></td>
                                        <td class="p-3 font-bold">${String(b.due_day).padStart(2,'0')}/${month.slice(5, 7)}</td>
                                        <td class="p-3 text-right font-black">${moneyBr(b.amount)}</td>
                                        <td class="p-3">
                                            <div class="flex justify-end gap-2">
                                                <a class="icon-button" href="bills.html?month=${month}&edit=${b.id}">${editIcon()}</a>
                                                <button class="icon-button danger" data-delete-bill="${b.id}">x</button>
                                                ${b.auto_create ? `<button class="icon-button danger series-delete-button" data-delete-series="${b.id}">TODOS</button>` : ''}
                                            </div>
                                        </td>
                                    </tr>`).join('')
                                : `<tr><td colspan="4" class="p-4 text-sm font-bold text-slate-400">Sem contas neste mês.</td></tr>`}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="app-panel">
                    <h2 class="mb-4 text-lg font-black">Categorias</h2>
                    <div class="grid gap-2 sm:grid-cols-2" id="cats-list">
                        ${(cats || []).length ? (cats || []).map(c => `
                            <article class="app-category-item flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3">
                                <div class="flex items-center gap-3">
                                    <span class="h-4 w-4 rounded-full" style="background:${e(c.color)}"></span>
                                    <div><p class="text-sm font-black">${e(c.name)}</p><p class="text-[11px] font-bold text-slate-400">${c.type === 'income' ? 'Ganho' : 'Gasto'}</p></div>
                                </div>
                                <div class="flex gap-2">
                                    <a class="icon-button" href="bills.html?month=${month}&edit_category=${c.id}">${editIcon()}</a>
                                    <button class="icon-button danger" data-delete-cat="${c.id}">x</button>
                                </div>
                            </article>`).join('')
                        : `<p class="text-sm font-bold text-[#77798a]">Nenhuma categoria cadastrada.</p>`}
                    </div>
                </section>
            </div>
        </section>`;

        // ── Bill form ─────────────────────────────────────────────────────────
        document.getElementById('bill-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const eid   = document.getElementById('bill-edit-id').value;
            const dDate = document.getElementById('bill-due-date').value;
            const dueDay = dDate ? parseInt(dDate.slice(8, 10), 10) : 10;
            const recurring = document.getElementById('bill-recurring').checked;
            const payload = {
                user_id: uid,
                reference_month: month,
                category_id: parseInt(document.getElementById('bill-category').value, 10) || null,
                title: document.getElementById('bill-title').value.trim(),
                amount: parseFloat(document.getElementById('bill-amount').value.replace(',', '.')) || 0,
                due_day: dueDay,
                auto_create: recurring,
                active: true,
            };
            if (eid) {
                await db.from('monthly_bills').update(payload).eq('id', parseInt(eid, 10)).eq('user_id', uid);
                window.location.href = `bills.html?month=${month}`;
            } else {
                await db.from('monthly_bills').insert(payload);
                if (recurring) await propagateRecurringBills(uid, month, payload);
                load();
            }
        });

        // ── Cat form ──────────────────────────────────────────────────────────
        document.getElementById('cat-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const eid = document.getElementById('cat-edit-id').value;
            const payload = {
                user_id: uid,
                name: document.getElementById('cat-name').value.trim(),
                type: document.getElementById('cat-type').value,
                color: document.getElementById('cat-color').value,
                icon: document.getElementById('cat-icon').value || 'tag',
            };
            if (eid) {
                await db.from('categories').update(payload).eq('id', parseInt(eid, 10)).eq('user_id', uid);
                window.location.href = `bills.html?month=${month}`;
            } else {
                await db.from('categories').insert(payload);
                load();
            }
        });

        // ── Deletes ───────────────────────────────────────────────────────────
        document.getElementById('bills-tbody').addEventListener('click', async ev => {
            const btnBill = ev.target.closest('[data-delete-bill]');
            if (btnBill && confirm('Excluir esta conta?')) {
                await db.from('monthly_bills').delete().eq('id', parseInt(btnBill.dataset.deleteBill, 10)).eq('user_id', uid);
                load(); return;
            }
            const btnSeries = ev.target.closest('[data-delete-series]');
            if (btnSeries && confirm('Excluir toda esta recorrência (todos os meses)?')) {
                const { data: tgt } = await db.from('monthly_bills').select('title,amount,due_day').eq('id', parseInt(btnSeries.dataset.deleteSeries, 10)).eq('user_id', uid).single();
                if (tgt) await db.from('monthly_bills').delete().eq('user_id', uid).eq('title', tgt.title).eq('amount', tgt.amount).eq('due_day', tgt.due_day);
                load();
            }
        });

        document.getElementById('cats-list').addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-delete-cat]');
            if (!btn || !confirm('Excluir esta categoria?')) return;
            await db.from('categories').delete().eq('id', parseInt(btn.dataset.deleteCat, 10)).eq('user_id', uid);
            load();
        });
    }

    async function propagateRecurringBills(uid, sourceMonth, tpl) {
        const [y, m] = sourceMonth.split('-').map(Number);
        for (let mo = m + 1; mo <= 12; mo++) {
            const refMonth = `${y}-${String(mo).padStart(2, '0')}`;
            const { data: existing } = await db.from('monthly_bills').select('id').eq('user_id', uid).eq('reference_month', refMonth).eq('title', tpl.title).maybeSingle();
            if (!existing) await db.from('monthly_bills').insert({ ...tpl, reference_month: refMonth });
        }
    }

    await load();
    initAppBehaviors();
})();
