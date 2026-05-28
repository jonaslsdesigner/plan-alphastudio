(async () => {
    const ctx = await initLayout('goals.html', 'Metas');
    if (!ctx) return;
    const { user } = ctx;
    const uid = user.id;
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit') ? parseInt(params.get('edit'), 10) : null;

    async function load() {
        const { data: goals } = await db.from('goals').select('*').eq('user_id', uid).order('created_at', { ascending: false });
        const editItem = editId ? (goals || []).find(g => g.id === editId) : null;

        const goalsHtml = (goals || []).length
            ? (goals || []).map(g => {
                const pct = g.target_amount > 0 ? Math.min(100, (g.current_amount / g.target_amount) * 100) : 0;
                return `<article class="app-panel">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black">${e(g.name)}</h2>
                            <p class="text-xs font-bold text-slate-400">${moneyBr(g.current_amount)} de ${moneyBr(g.target_amount)}</p>
                        </div>
                        <div class="flex gap-2">
                            <a class="icon-button" href="goals.html?edit=${g.id}">${editIcon()}</a>
                            <button class="icon-button danger" data-delete="${g.id}">x</button>
                        </div>
                    </div>
                    <div class="mt-5 h-3 rounded-full bg-slate-100"><div class="h-3 rounded-full" style="width:${pct.toFixed(1)}%;background:${e(g.color)}"></div></div>
                    <p class="mt-2 text-right text-xs font-black text-slate-500">${pct.toFixed(0)}%</p>
                </article>`;
            }).join('')
            : `<p class="app-panel text-sm font-bold text-slate-400">Nenhuma caixinha criada.</p>`;

        document.getElementById('page-content').innerHTML = `
        <section class="grid gap-4 lg:grid-cols-[22rem_1fr]">
            <form id="goal-form" class="app-panel space-y-3">
                <input type="hidden" id="edit-id" value="${editItem?.id || ''}">
                <h2 class="text-lg font-black">${editItem ? 'Editar caixinha' : 'Nova caixinha'}</h2>
                <label class="app-label">Nome<input class="app-input" id="g-name" required placeholder="Reserva, investimento, viagem" value="${e(editItem?.name || '')}"></label>
                <label class="app-label">Meta<input class="app-input" id="g-target" inputmode="decimal" required value="${e(editItem?.target_amount || '')}"></label>
                <label class="app-label">Guardado agora<input class="app-input" id="g-current" inputmode="decimal" value="${e(editItem?.current_amount || '0')}"></label>
                <label class="app-label">Data alvo<input class="app-input" type="date" id="g-date" value="${e(editItem?.due_date || '')}"></label>
                <label class="app-label">Cor<input class="app-input h-12" type="color" id="g-color" value="${e(editItem?.color || '#2563eb')}"></label>
                <button class="app-button w-full" type="submit">${editItem ? 'Salvar' : 'Adicionar'}</button>
                ${editItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="goals.html">Cancelar</a>` : ''}
            </form>
            <section class="grid gap-3 sm:grid-cols-2" id="goals-list">${goalsHtml}</section>
        </section>`;

        document.getElementById('goal-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const eid = document.getElementById('edit-id').value;
            const payload = {
                user_id: uid,
                name: document.getElementById('g-name').value.trim(),
                target_amount: parseFloat(document.getElementById('g-target').value.replace(',', '.')) || 0,
                current_amount: parseFloat(document.getElementById('g-current').value.replace(',', '.')) || 0,
                due_date: document.getElementById('g-date').value || null,
                color: document.getElementById('g-color').value,
            };
            if (eid) {
                await db.from('goals').update(payload).eq('id', parseInt(eid, 10)).eq('user_id', uid);
                window.location.href = 'goals.html';
            } else {
                await db.from('goals').insert(payload);
                load();
            }
        });

        document.getElementById('goals-list').addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-delete]');
            if (!btn || !confirm('Excluir esta caixinha?')) return;
            await db.from('goals').delete().eq('id', parseInt(btn.dataset.delete, 10)).eq('user_id', uid);
            load();
        });
    }

    await load();
    initAppBehaviors();
})();
