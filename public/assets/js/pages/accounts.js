(async () => {
    const ctx = await initLayout('accounts.html', 'Contas');
    if (!ctx) return;
    const { user } = ctx;
    const uid = user.id;
    const params = new URLSearchParams(window.location.search);
    const editId    = params.get('edit')          ? parseInt(params.get('edit'), 10)          : null;
    const editCatId = params.get('edit_category') ? parseInt(params.get('edit_category'), 10) : null;

    const TYPE_LABELS = { checking: 'Conta corrente', cash: 'Dinheiro', credit: 'Cartão', saving: 'Caixinha' };

    async function load() {
        const [{ data: accounts }, { data: cats }] = await Promise.all([
            db.from('accounts').select('*').eq('user_id', uid).order('sort_order').order('created_at'),
            db.from('categories').select('*').eq('user_id', uid).order('sort_order').order('created_at'),
        ]);
        const editItem    = editId    ? (accounts || []).find(a => a.id === editId)    : null;
        const editCatItem = editCatId ? (cats     || []).find(c => c.id === editCatId) : null;

        document.getElementById('page-content').innerHTML = `
        <section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
            <div class="space-y-4">
                <form id="acc-form" class="app-panel space-y-3">
                    <input type="hidden" id="acc-edit-id" value="${editItem?.id || ''}">
                    <h2 class="text-lg font-black">${editItem ? 'Editar conta' : 'Nova conta'}</h2>
                    <label class="app-label">Nome<input class="app-input" id="acc-name" required value="${e(editItem?.name || '')}"></label>
                    <label class="app-label">Tipo<select class="app-input" id="acc-type">
                        <option value="checking" ${(editItem?.type || 'checking') === 'checking' ? 'selected' : ''}>Conta corrente</option>
                        <option value="cash"     ${editItem?.type === 'cash'    ? 'selected' : ''}>Dinheiro</option>
                        <option value="credit"   ${editItem?.type === 'credit'  ? 'selected' : ''}>Cartão</option>
                        <option value="saving"   ${editItem?.type === 'saving'  ? 'selected' : ''}>Caixinha</option>
                    </select></label>
                    <label class="app-label">Saldo inicial<input class="app-input" id="acc-balance" inputmode="decimal" value="${e(editItem?.balance || '0')}"></label>
                    <button class="app-button w-full" type="submit">${editItem ? 'Salvar conta' : 'Adicionar conta'}</button>
                    ${editItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="accounts.html">Cancelar</a>` : ''}
                </form>

                <form id="cat-form" class="app-panel space-y-3">
                    <input type="hidden" id="cat-edit-id" value="${editCatItem?.id || ''}">
                    <h2 class="text-lg font-black">${editCatItem ? 'Editar categoria' : 'Nova categoria'}</h2>
                    <label class="app-label">Nome<input class="app-input" id="cat-name" required value="${e(editCatItem?.name || '')}"></label>
                    <label class="app-label">Tipo<select class="app-input" id="cat-type">
                        <option value="expense" ${(editCatItem?.type || 'expense') === 'expense' ? 'selected' : ''}>Gasto</option>
                        <option value="income"  ${editCatItem?.type === 'income' ? 'selected' : ''}>Ganho</option>
                    </select></label>
                    <label class="app-label">Cor<input class="app-input h-12" type="color" id="cat-color" value="${e(editCatItem?.color || '#9b57e3')}"></label>
                    <label class="app-label">Ícone<input class="app-input" id="cat-icon" value="${e(editCatItem?.icon || 'tag')}"></label>
                    <button class="app-button w-full" type="submit">${editCatItem ? 'Salvar categoria' : 'Adicionar categoria'}</button>
                    ${editCatItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="accounts.html">Cancelar</a>` : ''}
                </form>
            </div>

            <div class="space-y-4">
                <section class="app-panel">
                    <h2 class="mb-4 text-lg font-black">Contas cadastradas</h2>
                    <div class="grid gap-2 sm:grid-cols-2" id="acc-list">
                        ${(accounts || []).length ? (accounts || []).map(a => `
                            <article class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-black">${e(a.name)}</p>
                                    <p class="text-[11px] font-bold text-slate-400">${TYPE_LABELS[a.type] || a.type} - ${moneyBr(a.balance)}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a class="icon-button" href="accounts.html?edit=${a.id}">${editIcon()}</a>
                                    <button class="icon-button danger" data-delete-acc="${a.id}">x</button>
                                </div>
                            </article>`).join('')
                        : `<p class="text-sm font-bold text-[#77798a]">Nenhuma conta cadastrada.</p>`}
                    </div>
                </section>

                <section class="app-panel">
                    <h2 class="mb-4 text-lg font-black">Categorias</h2>
                    <div class="grid gap-2 sm:grid-cols-2" id="cat-list">
                        ${(cats || []).length ? (cats || []).map(c => `
                            <article class="flex items-center justify-between gap-3 rounded-2xl bg-slate-50 p-3">
                                <div class="flex items-center gap-3">
                                    <span class="h-4 w-4 rounded-full" style="background:${e(c.color)}"></span>
                                    <div><p class="text-sm font-black">${e(c.name)}</p><p class="text-[11px] font-bold text-slate-400">${c.type === 'income' ? 'Ganho' : 'Gasto'}</p></div>
                                </div>
                                <div class="flex gap-2">
                                    <a class="icon-button" href="accounts.html?edit_category=${c.id}">${editIcon()}</a>
                                    <button class="icon-button danger" data-delete-cat="${c.id}">x</button>
                                </div>
                            </article>`).join('')
                        : `<p class="text-sm font-bold text-[#77798a]">Nenhuma categoria cadastrada.</p>`}
                    </div>
                </section>
            </div>
        </section>`;

        document.getElementById('acc-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const eid = document.getElementById('acc-edit-id').value;
            const payload = {
                user_id: uid,
                name: document.getElementById('acc-name').value.trim(),
                type: document.getElementById('acc-type').value,
                balance: parseFloat(document.getElementById('acc-balance').value.replace(',', '.')) || 0,
            };
            if (eid) {
                await db.from('accounts').update(payload).eq('id', parseInt(eid, 10)).eq('user_id', uid);
                window.location.href = 'accounts.html';
            } else {
                await db.from('accounts').insert(payload);
                load();
            }
        });

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
                window.location.href = 'accounts.html';
            } else {
                await db.from('categories').insert(payload);
                load();
            }
        });

        document.getElementById('acc-list').addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-delete-acc]');
            if (!btn || !confirm('Excluir esta conta?')) return;
            await db.from('accounts').delete().eq('id', parseInt(btn.dataset.deleteAcc, 10)).eq('user_id', uid);
            load();
        });
        document.getElementById('cat-list').addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-delete-cat]');
            if (!btn || !confirm('Excluir esta categoria?')) return;
            await db.from('categories').delete().eq('id', parseInt(btn.dataset.deleteCat, 10)).eq('user_id', uid);
            load();
        });
    }

    await load();
    initAppBehaviors();
})();
