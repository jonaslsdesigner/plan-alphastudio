(async () => {
    const ctx = await initLayout('categories.html', 'Categorias');
    if (!ctx) return;
    const { user } = ctx;
    const uid = user.id;
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit') ? parseInt(params.get('edit'), 10) : null;

    async function load() {
        const { data: cats } = await db.from('categories').select('*').eq('user_id', uid).order('sort_order').order('created_at');
        const editItem = editId ? (cats || []).find(c => c.id === editId) : null;

        document.getElementById('page-content').innerHTML = `
        <section class="grid gap-4 lg:grid-cols-[22rem_1fr]">
            <form id="cat-form" class="app-panel space-y-3">
                <input type="hidden" id="edit-id" value="${editItem?.id || ''}">
                <h2 class="text-lg font-black">${editItem ? 'Editar categoria' : 'Nova categoria'}</h2>
                <label class="app-label">Nome<input class="app-input" id="c-name" required value="${e(editItem?.name || '')}"></label>
                <label class="app-label">Tipo<select class="app-input" id="c-type">
                    <option value="expense" ${(editItem?.type || 'expense') === 'expense' ? 'selected' : ''}>Gasto</option>
                    <option value="income"  ${editItem?.type === 'income' ? 'selected' : ''}>Ganho</option>
                </select></label>
                <label class="app-label">Cor<input class="app-input h-12" type="color" id="c-color" value="${e(editItem?.color || '#4f46e5')}"></label>
                <label class="app-label">Ícone<input class="app-input" id="c-icon" value="${e(editItem?.icon || 'tag')}"></label>
                <button class="app-button w-full" type="submit">${editItem ? 'Salvar' : 'Adicionar'}</button>
                ${editItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="categories.html">Cancelar</a>` : ''}
            </form>
            <div class="app-panel grid gap-2 sm:grid-cols-2" id="cat-list">
                ${(cats || []).length ? (cats || []).map(c => `
                    <article class="flex items-center justify-between rounded-2xl bg-slate-50 p-3">
                        <div class="flex items-center gap-3">
                            <span class="h-4 w-4 rounded-full" style="background:${e(c.color)}"></span>
                            <div><p class="text-sm font-black">${e(c.name)}</p><p class="text-[11px] font-bold text-slate-400">${c.type === 'income' ? 'Ganho' : 'Gasto'}</p></div>
                        </div>
                        <div class="flex gap-2">
                            <a class="icon-button" href="categories.html?edit=${c.id}">${editIcon()}</a>
                            <button class="icon-button danger" data-delete="${c.id}">x</button>
                        </div>
                    </article>`).join('')
                : `<p class="text-sm font-bold text-[#77798a]">Nenhuma categoria cadastrada.</p>`}
            </div>
        </section>`;

        document.getElementById('cat-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const eid = document.getElementById('edit-id').value;
            const payload = {
                user_id: uid,
                name: document.getElementById('c-name').value.trim(),
                type: document.getElementById('c-type').value,
                color: document.getElementById('c-color').value,
                icon: document.getElementById('c-icon').value || 'tag',
            };
            if (eid) {
                await db.from('categories').update(payload).eq('id', parseInt(eid, 10)).eq('user_id', uid);
                window.location.href = 'categories.html';
            } else {
                await db.from('categories').insert(payload);
                load();
            }
        });

        document.getElementById('cat-list').addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-delete]');
            if (!btn || !confirm('Excluir esta categoria?')) return;
            await db.from('categories').delete().eq('id', parseInt(btn.dataset.delete, 10)).eq('user_id', uid);
            load();
        });
    }

    await load();
    initAppBehaviors();
})();
