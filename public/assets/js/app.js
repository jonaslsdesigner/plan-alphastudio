// ─── App behaviors (privacy toggle, theme toggle, service worker) ─────────────
// Chamado após o layout ser renderizado em cada página.
function initAppBehaviors() {
    initPrivacyToggle();
    initThemeToggle();
    initPasswordToggles();
}

// ─── Privacy toggle ───────────────────────────────────────────────────────────
const PRIVACY_KEY      = 'alpha_plan_privacy_hidden';
const PRIVACY_INIT_KEY = 'alpha_plan_privacy_initialized';
const CURRENCY_RE      = /R\$\s?-?[\d.,]+/g;
const privateNodes     = [];
const seenNodes        = new WeakSet();
let privacyHidden      = false;

function maskCurrency(text) {
    return text.replace(CURRENCY_RE, 'R$ ••••');
}

function collectPrivateNodes(root = document.body) {
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, {
        acceptNode(node) {
            const parent = node.parentElement;
            if (!parent) return NodeFilter.FILTER_REJECT;
            if (parent.closest('script,style,noscript,textarea,[data-privacy-toggle]')) return NodeFilter.FILTER_REJECT;
            CURRENCY_RE.lastIndex = 0;
            if (!CURRENCY_RE.test(node.nodeValue || '')) return NodeFilter.FILTER_REJECT;
            parent.classList.add('money-value');
            return NodeFilter.FILTER_ACCEPT;
        },
    });
    let node;
    while ((node = walker.nextNode())) {
        if (seenNodes.has(node)) continue;
        seenNodes.add(node);
        privateNodes.push({ node, original: node.nodeValue || '' });
        if (privacyHidden) node.nodeValue = maskCurrency(node.nodeValue || '');
    }
}

function syncPrivacy() {
    privateNodes.forEach(e => {
        e.node.nodeValue = privacyHidden ? maskCurrency(e.original) : e.original;
    });
}

function applyPrivacy(hidden, persist = true) {
    privacyHidden = hidden;
    document.body.classList.toggle('is-privacy-mode', hidden);
    if (persist) {
        sessionStorage.setItem(PRIVACY_KEY, hidden ? '1' : '0');
        sessionStorage.setItem(PRIVACY_INIT_KEY, '1');
    }
    document.querySelectorAll('[data-privacy-toggle]').forEach(btn => {
        btn.setAttribute('aria-pressed', String(hidden));
        btn.setAttribute('aria-label', hidden ? 'Mostrar valores' : 'Ocultar valores');
        btn.classList.toggle('is-active', hidden);
    });
    syncPrivacy();
}

function initPrivacyToggle() {
    const initialized = sessionStorage.getItem(PRIVACY_INIT_KEY) === '1';
    privacyHidden = initialized ? sessionStorage.getItem(PRIVACY_KEY) === '1' : false;

    collectPrivateNodes();
    applyPrivacy(privacyHidden, false);

    new MutationObserver(mutations => {
        mutations.forEach(m => m.addedNodes.forEach(n => {
            if (n.nodeType === 1) collectPrivateNodes(n);
        }));
    }).observe(document.body, { childList: true, subtree: true });

    document.addEventListener('click', ev => {
        if (ev.target.closest('[data-privacy-toggle]')) applyPrivacy(!privacyHidden);
    });
}

// ─── Theme toggle (dark mode) ─────────────────────────────────────────────────
const THEME_KEY = 'alpha_plan_color_mode';

function initThemeToggle() {
    const saved = localStorage.getItem(THEME_KEY);
    const isDark = saved === 'dark';
    applyColorMode(isDark, false);

    document.addEventListener('click', ev => {
        if (ev.target.closest('[data-theme-toggle]')) {
            const newDark = !document.body.classList.contains('is-dark-mode');
            applyColorMode(newDark);
        }
    });
}

function applyColorMode(isDark, persist = true) {
    document.body.classList.toggle('is-dark-mode', isDark);
    if (persist) localStorage.setItem(THEME_KEY, isDark ? 'dark' : 'light');
    document.querySelectorAll('[data-theme-toggle]').forEach(btn => {
        btn.setAttribute('aria-pressed', String(isDark));
        btn.setAttribute('aria-label', isDark ? 'Desativar modo escuro' : 'Ativar modo escuro');
        btn.classList.toggle('is-active', isDark);
    });
}

// ─── Password visibility toggles ─────────────────────────────────────────────
function initPasswordToggles() {
    document.querySelectorAll('[data-password-toggle]').forEach(btn => {
        if (btn._pwInit) return;
        btn._pwInit = true;
        btn.addEventListener('click', () => {
            const input = btn.closest('.password-toggle-field')?.querySelector('input');
            if (!input) return;
            input.type = input.type === 'password' ? 'text' : 'password';
            btn.classList.toggle('is-active');
        });
    });
}
