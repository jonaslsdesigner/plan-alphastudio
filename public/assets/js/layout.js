// ─── Shared Layout ────────────────────────────────────────────────────────────
const THEMES = {
    '#066ab5': { primary: '#0f1b2d', accent: '#22c88a', accent2: '#ffffff', dark: '#0b1424', ink: '#0f1b2d', muted: '#708098', line: '#e8e8e8', shell: '#f7f8fa', surface: '#ffffff', panel: '#ffffff', input: '#f7f8fa', sidebar: '#0f1b2d', primaryRgb: '15, 27, 45', accentRgb: '34, 200, 138' },
    '#1a3a2a': { primary: '#1a3a2a', accent: '#4caf7d', accent2: '#ffffff', dark: '#10251b', ink: '#0f1b2d', muted: '#708098', line: '#e8e8e8', shell: '#f7f8fa', surface: '#ffffff', panel: '#ffffff', input: '#f7f8fa', sidebar: '#1a3a2a', primaryRgb: '26, 58, 42', accentRgb: '76, 175, 125' },
    '#0d1f3c': { primary: '#0d1f3c', accent: '#00bcd4', accent2: '#ffffff', dark: '#08162d', ink: '#0f1b2d', muted: '#708098', line: '#e8e8e8', shell: '#f7f8fa', surface: '#ffffff', panel: '#ffffff', input: '#f7f8fa', sidebar: '#0d1f3c', primaryRgb: '13, 31, 60', accentRgb: '0, 188, 212' },
    '#1e1040': { primary: '#1e1040', accent: '#a78bfa', accent2: '#ffffff', dark: '#150b2e', ink: '#0f1b2d', muted: '#708098', line: '#e8e8e8', shell: '#f7f8fa', surface: '#ffffff', panel: '#ffffff', input: '#f7f8fa', sidebar: '#1e1040', primaryRgb: '30, 16, 64', accentRgb: '167, 139, 250' },
};

const NAV_ITEMS = [
    { page: 'index.html',       label: 'Dashboard',    icon: 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z' },
    { page: 'roadmap.html',     label: 'Roteiro',      icon: 'M7 2h10v2h3v18H4V4h3V2Zm0 6h10V6H7v2Zm0 4h7v2H7v-2Zm0 4h10v2H7v-2Z' },
    { page: 'bills.html',       label: 'Contas',       icon: 'M6 2h12v20l-3-2-3 2-3-2-3 2V2Zm3 6h6v2H9V8Zm0 4h6v2H9v-2Z' },
    { page: 'cards.html',       label: 'Cartões',      icon: 'M3 6h18v12H3V6Zm2 4h14V8H5v2Zm0 3v3h6v-3H5Z' },
    { page: 'income.html',      label: 'Rendas',       icon: 'M12 3 3 8v8l9 5 9-5V8l-9-5Zm0 2.3L18.7 9 12 12.7 5.3 9 12 5.3ZM5 11.1l6 3.3v5l-6-3.3v-5Zm8 8.3v-5l6-3.3v5l-6 3.3Z' },
    { page: 'commitments.html', label: 'Compromissos', icon: 'M7 2h10v2h3v18H4V4h3V2Zm0 6h10V6H7v2Zm0 4h10v2H7v-2Zm0 4h7v2H7v-2Z' },
];

const MONTH_NAMES_SHORT = ['JAN','FEV','MAR','ABR','MAI','JUN','JUL','AGO','SET','OUT','NOV','DEZ'];
const MONTH_NAMES_LONG  = ['Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'];

// ─── Month helpers ────────────────────────────────────────────────────────────
function getMonth() {
    const p = new URLSearchParams(window.location.search).get('month');
    if (p && /^\d{4}-\d{2}$/.test(p)) {
        sessionStorage.setItem('alpha_selected_month', p);
        return p;
    }
    return sessionStorage.getItem('alpha_selected_month') || new Date().toISOString().slice(0, 7);
}

function monthLabel(month) {
    const [y, m] = month.split('-');
    return MONTH_NAMES_SHORT[parseInt(m, 10) - 1] + ' ' + y;
}

function monthLabelLong(month) {
    const [y, m] = month.split('-');
    return MONTH_NAMES_LONG[parseInt(m, 10) - 1] + ' de ' + y;
}

// ─── Utilities ────────────────────────────────────────────────────────────────
function moneyBr(value) {
    const n = parseFloat(value) || 0;
    return 'R$ ' + n.toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function e(str) {
    const div = document.createElement('div');
    div.textContent = String(str ?? '');
    return div.innerHTML;
}

function editIcon() {
    return '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 17.3V20h2.7L17.8 8.9l-2.7-2.7L4 17.3Zm15.9-10.5a1 1 0 0 0 0-1.4l-1.3-1.3a1 1 0 0 0-1.4 0l-1 1 2.7 2.7 1-1Z"/></svg>';
}

// ─── Theme ────────────────────────────────────────────────────────────────────
function applyTheme(themeColor) {
    const t = THEMES[themeColor] || THEMES['#066ab5'];
    document.body.style.cssText = [
        `--app-blue:${t.primary}`,`--app-blue-2:${t.primary}`,`--app-dark:${t.dark}`,
        `--app-accent:${t.accent}`,`--app-accent-2:${t.accent2}`,`--app-ink:${t.ink}`,
        `--app-muted:${t.muted}`,`--app-line:${t.line}`,`--app-shell-bg:${t.shell}`,
        `--app-surface-bg:${t.surface}`,`--app-panel-bg:${t.panel}`,`--app-input-bg:${t.input}`,
        `--app-sidebar-bg:${t.sidebar}`,`--app-primary-rgb:${t.primaryRgb}`,`--app-accent-rgb:${t.accentRgb}`,
    ].join(';');
    document.querySelector('meta[name="theme-color"]')?.setAttribute('content', themeColor);
}

// ─── Shell renderer ───────────────────────────────────────────────────────────
function renderShell(activePage, profile, month) {
    const name       = profile?.name || 'Usuário';
    const firstName  = name.split(' ')[0] || 'Usuário';
    const avatarHtml = profile?.avatar_path
        ? `<img class="app-user-hero-avatar" src="${e(profile.avatar_path)}" alt="">`
        : `<span class="app-user-hero-avatar app-avatar-initial">${e(firstName[0].toUpperCase())}</span>`;

    const navLinks = NAV_ITEMS.map(item => `
        <a href="${item.page}?month=${month}" class="app-nav ${activePage === item.page ? 'is-active' : ''}">
            <svg viewBox="0 0 24 24"><path d="${item.icon}"/></svg>
            ${item.label}
        </a>`).join('');

    const mobileNavLinks = NAV_ITEMS.map(item => `
        <a href="${item.page}?month=${month}" class="mobile-nav ${activePage === item.page ? 'is-active' : ''}" aria-label="${item.label}">
            <svg viewBox="0 0 24 24"><path d="${item.icon}"/></svg>
            <span>${item.label}</span>
        </a>`).join('');

    const [y, m] = month.split('-').map(Number);

    document.body.innerHTML = `
    <div class="flex min-h-screen w-full gap-5 px-3 py-3 sm:px-5 sm:py-6">
        <aside class="app-sidebar sticky top-6 hidden h-[calc(100vh-3rem)] w-64 shrink-0 p-4 lg:block">
            <div class="app-sidebar-brand mb-8 flex items-center gap-3 text-white">
                <span class="grid h-10 w-10 place-items-center rounded-lg bg-white/10 text-white/90">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2h2V2Zm12 8H5v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8ZM7 6v2h10V6H7Z" fill="currentColor"/></svg>
                </span>
                <span class="app-sidebar-brand-copy">
                    <span class="block">ALPHA PLAN</span>
                    <span class="block">Painel financeiro pessoal</span>
                </span>
            </div>
            <nav class="space-y-1">${navLinks}</nav>
            <div class="absolute bottom-4 left-4 right-4 rounded-lg bg-white/10 p-4 text-white">
                <a href="index.html?month=${month}" class="flex items-center gap-3">
                    <span class="app-brand-mark grid h-9 w-9 place-items-center rounded-full text-sm font-black text-white">A</span>
                    <span>
                        <span class="block text-sm font-bold">Alpha Planilhas</span>
                        <span class="block text-[11px] text-white/55">Dashboard</span>
                    </span>
                </a>
                <button id="sidebar-logout" class="mt-3 inline-flex text-xs font-bold text-white">Sair da conta</button>
            </div>
        </aside>

        <main class="app-surface min-w-0 flex-1 px-3 py-4 pb-24 sm:px-5 lg:pb-5">
            <header class="mb-5 px-1">
                <div class="app-topbar">
                    <a href="settings.html?month=${month}" class="app-user-hero">
                        ${avatarHtml}
                        <span class="app-user-hero-copy">
                            <span class="app-user-hero-eyebrow" id="topbar-page-title">Controle mensal</span>
                            <strong>Olá, ${e(firstName)}</strong>
                            <span>Acesse seu perfil</span>
                        </span>
                        <span class="app-user-hero-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                    <div class="app-topbar-tools">
                        <div class="app-topbar-title">
                            <p class="text-xs font-bold text-[#708098]" id="topbar-subtitle">Controle mensal</p>
                            <h1 class="app-page-title text-2xl font-black leading-tight sm:text-3xl">Mês de ${monthLabel(month)}</h1>
                        </div>
                        <button class="app-privacy-toggle" type="button" data-privacy-toggle aria-pressed="false" aria-label="Ocultar valores" title="Ocultar valores">
                            <span class="app-privacy-toggle-icon is-visible" data-privacy-icon="visible" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M12 5c5.23 0 9.27 3.36 10.8 6.45a1.3 1.3 0 0 1 0 1.1C21.27 15.64 17.23 19 12 19S2.73 15.64 1.2 12.55a1.3 1.3 0 0 1 0-1.1C2.73 8.36 6.77 5 12 5Zm0 2C8.04 7 4.8 9.42 3.34 12 4.8 14.58 8.04 17 12 17s7.2-2.42 8.66-5C19.2 9.42 15.96 7 12 7Zm0 2.2a2.8 2.8 0 1 1 0 5.6 2.8 2.8 0 0 1 0-5.6Zm0 2a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6Z" fill="currentColor"/></svg>
                            </span>
                            <span class="app-privacy-toggle-icon" data-privacy-icon="hidden" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M4.7 3.3 20.7 19.3l-1.4 1.4-3.3-3.3A13.07 13.07 0 0 1 12 19C6.77 19 2.73 15.64 1.2 12.55a1.3 1.3 0 0 1 0-1.1A13.67 13.67 0 0 1 6.2 6.5L3.3 3.7l1.4-1.4Zm2.95 4.36A11.44 11.44 0 0 0 3.34 12C4.8 14.58 8.04 17 12 17c1.03 0 2-.16 2.88-.46l-1.9-1.9a4.1 4.1 0 0 1-3.62-3.62L7.65 7.66ZM12 7c3.96 0 7.2 2.42 8.66 5a11.4 11.4 0 0 1-3.14 3.59l-1.44-1.44A4.8 4.8 0 0 0 12 9.2c-.35 0-.7.04-1.03.11L8.91 7.25C9.88 7.09 10.91 7 12 7Zm0 4.2a.8.8 0 0 0-.79.91l1.68 1.68a.8.8 0 0 0-.89-1.59Z" fill="currentColor"/></svg>
                            </span>
                        </button>
                        <button class="app-theme-toggle" type="button" data-theme-toggle aria-pressed="false" aria-label="Ativar modo escuro" title="Ativar modo escuro">
                            <span class="app-theme-toggle-icon is-visible" data-theme-icon="light" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M12 4.5a1 1 0 0 1-1-1V2a1 1 0 1 1 2 0v1.5a1 1 0 0 1-1 1Zm0 17.5a1 1 0 0 1-1-1v-1.5a1 1 0 1 1 2 0V21a1 1 0 0 1-1 1Zm7.5-9a1 1 0 1 1 0-2H21a1 1 0 1 1 0 2h-1.5ZM3 13a1 1 0 1 1 0-2h1.5a1 1 0 1 1 0 2H3Zm14.95-5.54a1 1 0 0 1-.7-1.7l1.06-1.07a1 1 0 0 1 1.42 1.42l-1.07 1.06a1 1 0 0 1-.71.29ZM5 19.6a1 1 0 0 1-.7-1.7l1.05-1.06a1 1 0 1 1 1.42 1.42l-1.06 1.05a1 1 0 0 1-.71.29Zm13.98 0a1 1 0 0 1-.7-.29l-1.07-1.05a1 1 0 0 1 1.42-1.42l1.06 1.06a1 1 0 0 1-.71 1.7ZM6.05 7.46a1 1 0 0 1-.7-.29L4.29 6.11A1 1 0 0 1 5.7 4.69l1.06 1.07a1 1 0 0 1-.71 1.7ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" fill="currentColor"/></svg>
                            </span>
                            <span class="app-theme-toggle-icon" data-theme-icon="dark" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M20.65 14.6a1 1 0 0 1 .2 1.04A9.5 9.5 0 1 1 8.36 3.15a1 1 0 0 1 1.04.2 1 1 0 0 1 .22 1.05A7.5 7.5 0 0 0 19.6 14.38a1 1 0 0 1 1.05.22Zm-2.54 2.28A9.5 9.5 0 0 1 7.12 5.89a7.5 7.5 0 1 0 10.99 10.99Z" fill="currentColor"/></svg>
                            </span>
                        </button>
                        <div class="app-month-picker" id="month-picker" data-month-picker data-year="${y}" data-month="${m}">
                            <input type="hidden" id="month-value" value="${month}">
                            <button class="app-month-trigger" type="button" data-month-trigger aria-expanded="false">
                                <span data-month-label>${monthLabelLong(month)}</span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7Zm12 8H5v10h14V10Z"/></svg>
                            </button>
                            <div class="app-month-popover" data-month-popover hidden>
                                <div class="app-month-top">
                                    <button type="button" data-year-step="-1" aria-label="Ano anterior">&lsaquo;</button>
                                    <strong data-year-label>${y}</strong>
                                    <button type="button" data-year-step="1" aria-label="Próximo ano">&rsaquo;</button>
                                </div>
                                <div class="app-month-grid">
                                    ${MONTH_NAMES_SHORT.map((name, i) => `<button type="button" data-month-option="${i + 1}" class="${i + 1 === m ? 'is-selected' : ''}">${name}</button>`).join('')}
                                </div>
                                <div class="app-month-actions">
                                    <button type="button" data-month-today>Este mês</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>
            <div id="page-content"></div>
        </main>
    </div>

    <nav class="mobile-bottom-nav fixed z-20 lg:hidden">
        <div class="mobile-bottom-nav-track" data-mobile-nav-track>${mobileNavLinks}</div>
    </nav>`;

    document.getElementById('sidebar-logout')?.addEventListener('click', () => Auth.logout());
    initMonthPicker();
}

// ─── Month picker logic ───────────────────────────────────────────────────────
function initMonthPicker() {
    const picker  = document.getElementById('month-picker');
    const trigger = picker?.querySelector('[data-month-trigger]');
    const popover = picker?.querySelector('[data-month-popover]');
    if (!picker || !trigger || !popover) return;

    let pickerYear  = parseInt(picker.dataset.year, 10);
    let pickerMonth = parseInt(picker.dataset.month, 10);

    function updatePopoverYear() {
        picker.querySelector('[data-year-label]').textContent = pickerYear;
        picker.querySelectorAll('[data-month-option]').forEach(btn => {
            btn.classList.toggle('is-selected', parseInt(btn.dataset.monthOption, 10) === pickerMonth);
        });
    }

    trigger.addEventListener('click', () => {
        const open = popover.hidden;
        popover.hidden = !open;
        trigger.setAttribute('aria-expanded', String(open));
    });

    document.addEventListener('click', e => {
        if (!picker.contains(e.target)) popover.hidden = true;
    });

    picker.querySelector('[data-year-step="-1"]').addEventListener('click', () => { pickerYear--; updatePopoverYear(); });
    picker.querySelector('[data-year-step="1"]').addEventListener('click',  () => { pickerYear++; updatePopoverYear(); });

    picker.querySelectorAll('[data-month-option]').forEach(btn => {
        btn.addEventListener('click', () => {
            pickerMonth = parseInt(btn.dataset.monthOption, 10);
            const newMonth = `${pickerYear}-${String(pickerMonth).padStart(2, '0')}`;
            sessionStorage.setItem('alpha_selected_month', newMonth);
            const url = new URL(window.location.href);
            url.searchParams.set('month', newMonth);
            window.location.href = url.toString();
        });
    });

    picker.querySelector('[data-month-today]').addEventListener('click', () => {
        const today = new Date().toISOString().slice(0, 7);
        sessionStorage.setItem('alpha_selected_month', today);
        const url = new URL(window.location.href);
        url.searchParams.set('month', today);
        window.location.href = url.toString();
    });
}

// ─── Main init ────────────────────────────────────────────────────────────────
async function initLayout(activePage, pageTitle) {
    const user = await Auth.requireLogin();
    if (!user) return null;

    const profile = await Auth.getProfile(user.id);
    const month   = getMonth();

    applyTheme(profile?.theme_color || '#066ab5');
    renderShell(activePage, profile, month);

    if (pageTitle) {
        document.title = `${pageTitle} — Alpha Planilhas`;
        const el1 = document.getElementById('topbar-page-title');
        const el2 = document.getElementById('topbar-subtitle');
        if (el1) el1.textContent = pageTitle;
        if (el2) el2.textContent = pageTitle;
    }

    return { user, profile, month };
}
