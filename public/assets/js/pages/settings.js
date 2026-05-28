(async () => {
    const ctx = await initLayout('settings.html', 'Perfil');
    if (!ctx) return;
    const { user, profile } = ctx;
    const uid = user.id;

    const THEME_OPTIONS = [
        { name: 'Alpha Green', value: '#066ab5', colors: ['#0f1b2d', '#22c88a', '#ffffff'] },
        { name: 'Forest',      value: '#1a3a2a', colors: ['#1a3a2a', '#4caf7d', '#ffffff'] },
        { name: 'Navy Cyan',   value: '#0d1f3c', colors: ['#0d1f3c', '#00bcd4', '#ffffff'] },
        { name: 'Violet',      value: '#1e1040', colors: ['#1e1040', '#a78bfa', '#ffffff'] },
    ];

    const selectedTheme = profile?.theme_color || '#066ab5';
    const firstName = (profile?.name || 'A')[0].toUpperCase();

    const themeOptionsHtml = THEME_OPTIONS.map(t => `
        <label class="theme-option ${selectedTheme === t.value ? 'is-selected' : ''}">
            <input type="radio" name="theme_color" value="${t.value}" ${selectedTheme === t.value ? 'checked' : ''}>
            <span class="theme-swatch">${t.colors.map(c => `<i style="background:${c}"></i>`).join('')}</span>
            <strong>${e(t.name)}</strong>
        </label>`).join('');

    const avatarHtml = profile?.avatar_path
        ? `<img class="app-profile-photo" src="${e(profile.avatar_path)}" alt="" id="avatar-preview">`
        : `<div class="app-profile-avatar app-profile-photo" id="avatar-preview">${firstName}</div>`;

    document.getElementById('page-content').innerHTML = `
    <section class="max-w-3xl">
        <form id="settings-form" class="app-panel space-y-4">
            <div class="flex items-center gap-4">
                <label class="app-profile-photo-edit" aria-label="Alterar foto de perfil">
                    ${avatarHtml}
                    <span class="app-profile-photo-badge" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M4 17.2V20h2.8l8.3-8.3-2.8-2.8L4 17.2Zm12-10.4 2.8 2.8 1.4-1.4a1 1 0 0 0 0-1.4l-1.4-1.4a1 1 0 0 0-1.4 0L16 6.8Z"/></svg>
                    </span>
                    <input class="app-profile-photo-input" type="file" id="avatar-input" accept="image/*">
                </label>
                <div>
                    <h2 class="text-lg font-black">Personalização do perfil</h2>
                    <p class="text-sm font-semibold text-[#77798a]">Nome, idade, foto e tema para deixar o app com sua cara.</p>
                </div>
            </div>
            <label class="app-label">Seu nome<input class="app-input" id="s-name" value="${e(profile?.name || '')}" required></label>
            <label class="app-label">Idade<input class="app-input" type="number" id="s-age" min="1" max="120" value="${e(profile?.age || '')}"></label>
            <label class="app-label">Moeda<input class="app-input" id="s-currency" value="${e(profile?.currency || 'BRL')}"></label>
            <fieldset class="space-y-2">
                <legend class="app-label">Tema de cores</legend>
                <div class="grid gap-2 sm:grid-cols-2">${themeOptionsHtml}</div>
            </fieldset>
            <div class="rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-500">
                O app já possui manifest e service worker. No celular, abra no navegador e use "Adicionar à Tela Inicial".
            </div>
            <button class="app-button w-full" type="submit" id="save-btn">Salvar perfil</button>
            <p id="save-msg" class="text-center text-sm font-bold text-emerald-600" hidden>Perfil salvo!</p>
        </form>
        <div class="app-logout-panel mt-4">
            <span class="app-logout-panel-brand">
                <span class="app-brand-mark grid h-9 w-9 place-items-center rounded-full text-sm font-black text-white">A</span>
                <span>
                    <span class="block text-sm font-bold">Alpha Planilhas</span>
                    <span class="block text-[11px] text-slate-500">Dashboard</span>
                </span>
            </span>
            <span class="app-logout-panel-action">
                <button id="logout-btn" class="text-sm font-bold">Sair da conta</button>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4H5v16h5v-2H7V6h3V4Zm6.6 4.4-1.4 1.4L16.4 11H10v2h6.4l-1.2 1.2 1.4 1.4L20.8 12l-4.2-3.6Z"/></svg>
            </span>
        </div>
    </section>`;

    // Theme radio live preview
    document.querySelectorAll('[name="theme_color"]').forEach(radio => {
        radio.addEventListener('change', () => {
            document.querySelectorAll('.theme-option').forEach(el => el.classList.remove('is-selected'));
            radio.closest('.theme-option')?.classList.add('is-selected');
            applyTheme(radio.value);
        });
    });

    // Avatar preview
    document.getElementById('avatar-input').addEventListener('change', ev => {
        const file = ev.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('avatar-preview');
            const img = document.createElement('img');
            img.className = 'app-profile-photo';
            img.id = 'avatar-preview';
            img.src = e.target.result;
            preview.replaceWith(img);
        };
        reader.readAsDataURL(file);
    });

    document.getElementById('settings-form').addEventListener('submit', async ev => {
        ev.preventDefault();
        const btn = document.getElementById('save-btn');
        btn.disabled = true;
        btn.textContent = 'Salvando…';

        const payload = {
            name: document.getElementById('s-name').value.trim(),
            age: parseInt(document.getElementById('s-age').value, 10) || null,
            currency: document.getElementById('s-currency').value || 'BRL',
            theme_color: document.querySelector('[name="theme_color"]:checked')?.value || '#066ab5',
        };

        // Avatar upload via Supabase Storage (se configurado)
        const avatarFile = document.getElementById('avatar-input').files[0];
        if (avatarFile) {
            const ext  = avatarFile.name.split('.').pop().toLowerCase();
            const path = `avatars/${uid}-${Date.now()}.${ext}`;
            const { error: upErr } = await db.storage.from('avatars').upload(path, avatarFile, { upsert: true });
            if (!upErr) {
                const { data: { publicUrl } } = db.storage.from('avatars').getPublicUrl(path);
                payload.avatar_path = publicUrl;
            }
        }

        await db.from('profiles').update(payload).eq('id', uid);
        Auth._profile = null;

        btn.disabled = false;
        btn.textContent = 'Salvar perfil';
        const msg = document.getElementById('save-msg');
        msg.hidden = false;
        setTimeout(() => { msg.hidden = true; }, 2500);
    });

    document.getElementById('logout-btn').addEventListener('click', () => Auth.logout());
    initAppBehaviors();
})();
