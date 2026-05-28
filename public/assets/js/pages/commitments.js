(async () => {
    const ctx = await initLayout('commitments.html', 'Compromissos');
    if (!ctx) return;
    const { user, month } = ctx;
    const uid  = user.id;
    const year = parseInt(month.slice(0, 4), 10);
    const params = new URLSearchParams(window.location.search);
    const editId = params.get('edit') ? parseInt(params.get('edit'), 10) : null;

    async function load() {
        const { data: commitments } = await db.from('commitments').select('*').eq('user_id', uid).order('sort_order').order('created_at');
        const active = (commitments || []).filter(c => c.status === 'active' && c.start_year === year);
        const total  = active.reduce((s, c) => s + parseFloat(c.amount), 0);
        const editItem = editId ? (commitments || []).find(c => c.id === editId) : null;

        document.getElementById('page-content').innerHTML = `
        <section class="grid gap-4 xl:grid-cols-[22rem_1fr]">
            <form id="commitment-form" class="app-panel space-y-3">
                <input type="hidden" id="edit-id" value="${editItem?.id || ''}">
                <h2 class="text-lg font-black">${editItem ? 'Editar compromisso' : 'Novo compromisso'}</h2>
                <label class="app-label">Título<input class="app-input" id="c-title" placeholder="Licenciamento, IPVA, acordo" value="${e(editItem?.title || '')}" required></label>
                <label class="app-label">Descrição<textarea class="app-input min-h-24" id="c-desc" placeholder="Anotações, vencimentos, parcelas">${e(editItem?.description || '')}</textarea></label>
                <label class="app-label">Valor previsto<input class="app-input" id="c-amount" inputmode="decimal" value="${e(editItem?.amount || '')}" required></label>
                <label class="app-label">Ano<input class="app-input" type="number" id="c-year" value="${e(editItem?.start_year || year)}" required></label>
                <label class="app-label">Status<select class="app-input" id="c-status">
                    <option value="active" ${(editItem?.status || 'active') === 'active' ? 'selected' : ''}>Ativo</option>
                    <option value="done"   ${editItem?.status === 'done' ? 'selected' : ''}>Resolvido</option>
                </select></label>
                <button class="app-button w-full" type="submit">${editItem ? 'Salvar compromisso' : 'Adicionar compromisso'}</button>
                ${editItem ? `<a class="block text-center text-xs font-bold text-[#77798a]" href="commitments.html?month=${month}">Cancelar</a>` : ''}
            </form>

            <div class="space-y-4">
                <section class="app-panel">
                    <div class="mb-4 flex items-start justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-black">Pendências de ${year}</h2>
                            <p class="text-sm font-semibold text-[#77798a]">Área informativa para acompanhar o que precisa resolver no ano.</p>
                        </div>
                        <span class="app-pill app-money-pill text-white" style="background:var(--app-accent)">${moneyBr(total)}</span>
                    </div>
                    <div class="space-y-2" id="commitment-list">
                        ${active.length ? active.map(c => `
                            <article class="flex items-center justify-between gap-3 rounded-[1.05rem] bg-[#f7f7f2] p-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-black">${e(c.title)}</p>
                                    <p class="text-[11px] font-bold text-[#77798a]">Ano ${c.start_year}${c.description ? ' — ' + c.description.slice(0, 60) : ''}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <p class="text-sm font-black">${moneyBr(c.amount)}</p>
                                    <a class="icon-button" href="commitments.html?month=${month}&edit=${c.id}">${editIcon()}</a>
                                    <button class="icon-button danger" data-delete="${c.id}">x</button>
                                </div>
                            </article>`).join('')
                        : `<p class="rounded-[1.05rem] bg-[#f7f7f2] p-4 text-sm font-bold text-[#77798a]">Sem compromissos ativos em ${year}.</p>`}
                    </div>
                </section>
            </div>
        </section>`;

        document.getElementById('commitment-form').addEventListener('submit', async ev => {
            ev.preventDefault();
            const eid = document.getElementById('edit-id').value;
            const payload = {
                user_id: uid,
                title: document.getElementById('c-title').value.trim(),
                description: document.getElementById('c-desc').value.trim(),
                amount: parseFloat(document.getElementById('c-amount').value.replace(',', '.')) || 0,
                start_year: parseInt(document.getElementById('c-year').value, 10),
                start_month: 1,
                duration_months: 12,
                status: document.getElementById('c-status').value,
            };
            if (eid) {
                await db.from('commitments').update(payload).eq('id', parseInt(eid, 10)).eq('user_id', uid);
                window.location.href = `commitments.html?month=${month}`;
            } else {
                await db.from('commitments').insert(payload);
                load();
            }
        });

        document.getElementById('commitment-list').addEventListener('click', async ev => {
            const btn = ev.target.closest('[data-delete]');
            if (!btn || !confirm('Excluir este compromisso?')) return;
            await db.from('commitments').delete().eq('id', parseInt(btn.dataset.delete, 10)).eq('user_id', uid);
            load();
        });
    }

    await load();
    initAppBehaviors();
})();
