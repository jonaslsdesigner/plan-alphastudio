<?php
$nav = [
    ['/', 'Dashboard', 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z'],
    ['/roadmap', 'Roteiro', 'M7 2h10v2h3v18H4V4h3V2Zm0 6h10V6H7v2Zm0 4h7v2H7v-2Zm0 4h10v2H7v-2Z'],
    ['/bills', 'Contas', 'M6 2h12v20l-3-2-3 2-3-2-3 2V2Zm3 6h6v2H9V8Zm0 4h6v2H9v-2Z'],
    ['/cards', 'Cartões', 'M3 6h18v12H3V6Zm2 4h14V8H5v2Zm0 3v3h6v-3H5Z'],
    ['/income', 'Rendas', 'M12 3 3 8v8l9 5 9-5V8l-9-5Zm0 2.3L18.7 9 12 12.7 5.3 9 12 5.3ZM5 11.1l6 3.3v5l-6-3.3v-5Zm8 8.3v-5l6-3.3v5l-6 3.3Z'],
    ['/commitments', 'Compromissos', 'M7 2h10v2h3v18H4V4h3V2Zm0 6h10V6H7v2Zm0 4h10v2H7v-2Zm0 4h7v2H7v-2Z'],
];
$mobileNav = $nav;
$currentPath = current_path();
$currentMonth = $month ?? date('Y-m');
$monthQuery = '?month=' . urlencode($currentMonth);
$monthDate = DateTime::createFromFormat('Y-m', $currentMonth) ?: new DateTime();
$pickerYear = (int) $monthDate->format('Y');
$pickerMonth = (int) $monthDate->format('n');
$monthNames = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
$monthNamesLong = ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$themes = [
    '#066ab5' => [
        'primary' => '#0f1b2d',
        'accent' => '#22c88a',
        'accent_2' => '#ffffff',
        'dark' => '#0b1424',
        'ink' => '#0f1b2d',
        'muted' => '#708098',
        'soft' => '#ffffff',
        'line' => '#e8e8e8',
        'shell' => '#f7f8fa',
        'surface' => '#ffffff',
        'panel' => '#ffffff',
        'input' => '#f7f8fa',
        'sidebar' => '#0f1b2d',
        'primary_rgb' => '15, 27, 45',
        'accent_rgb' => '34, 200, 138',
    ],
    '#1a3a2a' => [
        'primary' => '#1a3a2a',
        'accent' => '#4caf7d',
        'accent_2' => '#ffffff',
        'dark' => '#10251b',
        'ink' => '#0f1b2d',
        'muted' => '#708098',
        'soft' => '#ffffff',
        'line' => '#e8e8e8',
        'shell' => '#f7f8fa',
        'surface' => '#ffffff',
        'panel' => '#ffffff',
        'input' => '#f7f8fa',
        'sidebar' => '#1a3a2a',
        'primary_rgb' => '26, 58, 42',
        'accent_rgb' => '76, 175, 125',
    ],
    '#0d1f3c' => [
        'primary' => '#0d1f3c',
        'accent' => '#00bcd4',
        'accent_2' => '#ffffff',
        'dark' => '#08162d',
        'ink' => '#0f1b2d',
        'muted' => '#708098',
        'soft' => '#ffffff',
        'line' => '#e8e8e8',
        'shell' => '#f7f8fa',
        'surface' => '#ffffff',
        'panel' => '#ffffff',
        'input' => '#f7f8fa',
        'sidebar' => '#0d1f3c',
        'primary_rgb' => '13, 31, 60',
        'accent_rgb' => '0, 188, 212',
    ],
    '#1e1040' => [
        'primary' => '#1e1040',
        'accent' => '#a78bfa',
        'accent_2' => '#ffffff',
        'dark' => '#150b2e',
        'ink' => '#0f1b2d',
        'muted' => '#708098',
        'soft' => '#ffffff',
        'line' => '#e8e8e8',
        'shell' => '#f7f8fa',
        'surface' => '#ffffff',
        'panel' => '#ffffff',
        'input' => '#f7f8fa',
        'sidebar' => '#1e1040',
        'primary_rgb' => '30, 16, 64',
        'accent_rgb' => '167, 139, 250',
    ],
];
$themeColor = strtolower($user['theme_color'] ?? '#066ab5');
if (!isset($themes[$themeColor])) {
    $themeColor = '#066ab5';
}
$theme = $themes[$themeColor];
$cssVersion = @filemtime(__DIR__ . '/../../public/assets/css/app.css') ?: time();
$jsVersion = @filemtime(__DIR__ . '/../../public/assets/js/app.js') ?: time();
$themeStyle = "--app-blue: {$theme['primary']}; --app-blue-2: {$theme['primary']}; --app-dark: {$theme['dark']}; --app-accent: {$theme['accent']}; --app-accent-2: {$theme['accent_2']}; --app-ink: {$theme['ink']}; --app-muted: {$theme['muted']}; --app-soft: {$theme['soft']}; --app-line: {$theme['line']}; --app-shell-bg: {$theme['shell']}; --app-surface-bg: {$theme['surface']}; --app-panel-bg: {$theme['panel']}; --app-input-bg: {$theme['input']}; --app-sidebar-bg: {$theme['sidebar']}; --app-primary-rgb: {$theme['primary_rgb']}; --app-accent-rgb: {$theme['accent_rgb']};";
$userName = trim((string) ($user['name'] ?? 'Usuário'));
$firstName = strtok($userName !== '' ? $userName : 'Usuário', ' ') ?: 'Usuário';
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="<?= e($themeColor) ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Alpha Planilhas">
    <title><?= e($title ?? 'Alpha Planilhas') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= url('/icon.svg') ?>">
    <link rel="shortcut icon" href="<?= url('/icon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('/icon.svg') ?>">
    <link rel="manifest" href="<?= url('/manifest.webmanifest') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/app.css?v=' . $cssVersion) ?>">
</head>
<body class="app-shell min-h-screen is-privacy-mode" style="<?= e($themeStyle) ?>">
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
            <nav class="space-y-1">
                <?php foreach ($nav as [$href, $label, $icon]): ?>
                    <a href="<?= url($href) . $monthQuery ?>" class="app-nav <?= $currentPath === $href ? 'is-active' : '' ?>">
                        <svg viewBox="0 0 24 24"><path d="<?= $icon ?>"/></svg>
                        <?= e($label) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="absolute bottom-4 left-4 right-4 rounded-lg bg-white/10 p-4 text-white">
                <a href="<?= url('/') . $monthQuery ?>" class="flex items-center gap-3">
                    <span class="app-brand-mark grid h-9 w-9 place-items-center rounded-full text-sm font-black text-white">A</span>
                    <span>
                        <span class="block text-sm font-bold">Alpha Planilhas</span>
                        <span class="block text-[11px] text-white/55">Dashboard</span>
                    </span>
                </a>
                <a class="mt-3 inline-flex text-xs font-bold text-white" href="<?= url('/logout') ?>">Sair da conta</a>
            </div>
        </aside>

        <main class="app-surface min-w-0 flex-1 px-3 py-4 pb-24 sm:px-5 lg:pb-5">
            <header class="mb-5 px-1">
                <div class="app-topbar">
                    <a href="<?= url('/settings') . $monthQuery ?>" class="app-user-hero">
                        <?php if (!empty($user['avatar_path'])): ?>
                            <img class="app-user-hero-avatar" src="<?= url('/' . $user['avatar_path']) ?>" alt="">
                        <?php else: ?>
                            <span class="app-user-hero-avatar app-avatar-initial"><?= e(substr($userName !== '' ? $userName : 'A', 0, 1)) ?></span>
                        <?php endif; ?>
                        <span class="app-user-hero-copy">
                            <span class="app-user-hero-eyebrow"><?= e($pageTitle ?? 'Controle mensal') ?></span>
                            <strong>Olá, <?= e($firstName) ?></strong>
                            <span>Acesse seu perfil</span>
                        </span>
                        <span class="app-user-hero-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M9 6l6 6-6 6"/></svg>
                        </span>
                    </a>

                    <div class="app-topbar-tools">
                        <div class="app-topbar-title">
                            <p class="text-xs font-bold text-[#708098]"><?= e($pageTitle ?? 'Controle mensal') ?></p>
                            <h1 class="app-page-title text-2xl font-black leading-tight sm:text-3xl">Mês de <?= e(month_label($currentMonth)) ?></h1>
                        </div>

                        <button
                            class="app-privacy-toggle"
                            type="button"
                            data-privacy-toggle
                            aria-pressed="false"
                            aria-label="Ocultar valores"
                            title="Ocultar valores"
                        >
                            <span class="app-privacy-toggle-icon is-visible" data-privacy-icon="visible" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M12 5c5.23 0 9.27 3.36 10.8 6.45a1.3 1.3 0 0 1 0 1.1C21.27 15.64 17.23 19 12 19S2.73 15.64 1.2 12.55a1.3 1.3 0 0 1 0-1.1C2.73 8.36 6.77 5 12 5Zm0 2C8.04 7 4.8 9.42 3.34 12 4.8 14.58 8.04 17 12 17s7.2-2.42 8.66-5C19.2 9.42 15.96 7 12 7Zm0 2.2a2.8 2.8 0 1 1 0 5.6 2.8 2.8 0 0 1 0-5.6Zm0 2a.8.8 0 1 0 0 1.6.8.8 0 0 0 0-1.6Z" fill="currentColor"/></svg>
                            </span>
                            <span class="app-privacy-toggle-icon" data-privacy-icon="hidden" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M4.7 3.3 20.7 19.3l-1.4 1.4-3.3-3.3A13.07 13.07 0 0 1 12 19C6.77 19 2.73 15.64 1.2 12.55a1.3 1.3 0 0 1 0-1.1A13.67 13.67 0 0 1 6.2 6.5L3.3 3.7l1.4-1.4Zm2.95 4.36A11.44 11.44 0 0 0 3.34 12C4.8 14.58 8.04 17 12 17c1.03 0 2-.16 2.88-.46l-1.9-1.9a4.1 4.1 0 0 1-3.62-3.62L7.65 7.66ZM12 7c3.96 0 7.2 2.42 8.66 5a11.4 11.4 0 0 1-3.14 3.59l-1.44-1.44A4.8 4.8 0 0 0 12 9.2c-.35 0-.7.04-1.03.11L8.91 7.25C9.88 7.09 10.91 7 12 7Zm0 4.2a.8.8 0 0 0-.79.91l1.68 1.68a.8.8 0 0 0-.89-1.59Z" fill="currentColor"/></svg>
                            </span>
                        </button>

                        <button
                            class="app-theme-toggle"
                            type="button"
                            data-theme-toggle
                            aria-pressed="false"
                            aria-label="Ativar modo escuro"
                            title="Ativar modo escuro"
                        >
                            <span class="app-theme-toggle-icon is-visible" data-theme-icon="light" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M12 4.5a1 1 0 0 1-1-1V2a1 1 0 1 1 2 0v1.5a1 1 0 0 1-1 1Zm0 17.5a1 1 0 0 1-1-1v-1.5a1 1 0 1 1 2 0V21a1 1 0 0 1-1 1Zm7.5-9a1 1 0 1 1 0-2H21a1 1 0 1 1 0 2h-1.5ZM3 13a1 1 0 1 1 0-2h1.5a1 1 0 1 1 0 2H3Zm14.95-5.54a1 1 0 0 1-.7-1.7l1.06-1.07a1 1 0 0 1 1.42 1.42l-1.07 1.06a1 1 0 0 1-.71.29ZM5 19.6a1 1 0 0 1-.7-1.7l1.05-1.06a1 1 0 1 1 1.42 1.42l-1.06 1.05a1 1 0 0 1-.71.29Zm13.98 0a1 1 0 0 1-.7-.29l-1.07-1.05a1 1 0 0 1 1.42-1.42l1.06 1.06a1 1 0 0 1-.71 1.7ZM6.05 7.46a1 1 0 0 1-.7-.29L4.29 6.11A1 1 0 0 1 5.7 4.69l1.06 1.07a1 1 0 0 1-.71 1.7ZM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10Zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z" fill="currentColor"/></svg>
                            </span>
                            <span class="app-theme-toggle-icon" data-theme-icon="dark" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M20.65 14.6a1 1 0 0 1 .2 1.04A9.5 9.5 0 1 1 8.36 3.15a1 1 0 0 1 1.04.2 1 1 0 0 1 .22 1.05A7.5 7.5 0 0 0 19.6 14.38a1 1 0 0 1 1.05.22Zm-2.54 2.28A9.5 9.5 0 0 1 7.12 5.89a7.5 7.5 0 1 0 10.99 10.99Z" fill="currentColor"/></svg>
                            </span>
                        </button>

                        <form method="get" action="<?= url($currentPath) ?>" class="app-month-picker" data-month-picker data-year="<?= $pickerYear ?>" data-month="<?= $pickerMonth ?>">
                            <?php foreach ($_GET as $key => $value): ?>
                                <?php if ($key !== 'month' && is_scalar($value)): ?><input type="hidden" name="<?= e((string) $key) ?>" value="<?= e((string) $value) ?>"><?php endif; ?>
                            <?php endforeach; ?>
                            <input type="hidden" name="month" value="<?= e($currentMonth) ?>" data-month-value>
                            <button class="app-month-trigger" type="button" data-month-trigger aria-expanded="false">
                                <span data-month-label><?= e($monthNamesLong[$pickerMonth - 1] . ' de ' . $pickerYear) ?></span>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 2v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-2V2h-2v2H9V2H7Zm12 8H5v10h14V10Z"/></svg>
                            </button>
                            <div class="app-month-popover" data-month-popover hidden>
                                <div class="app-month-top">
                                    <button type="button" data-year-step="-1" aria-label="Ano anterior">&lsaquo;</button>
                                    <strong data-year-label><?= $pickerYear ?></strong>
                                    <button type="button" data-year-step="1" aria-label="Próximo ano">&rsaquo;</button>
                                </div>
                                <div class="app-month-grid">
                                    <?php foreach ($monthNames as $index => $name): ?>
                                        <?php $number = $index + 1; ?>
                                        <button type="button" data-month-option="<?= $number ?>" class="<?= $number === $pickerMonth ? 'is-selected' : '' ?>"><?= e($name) ?></button>
                                    <?php endforeach; ?>
                                </div>
                                <div class="app-month-actions">
                                    <button type="button" data-month-today>Este mês</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </header>
            <?= $content ?>
        </main>
    </div>

    <nav class="mobile-bottom-nav fixed z-20 lg:hidden">
        <div class="mobile-bottom-nav-track" data-mobile-nav-track>
            <?php foreach ($mobileNav as [$href, $label, $icon]): ?>
                <a href="<?= url($href) . $monthQuery ?>" class="mobile-nav <?= $currentPath === $href ? 'is-active' : '' ?>" aria-label="<?= e($label) ?>">
                    <svg viewBox="0 0 24 24"><path d="<?= $icon ?>"/></svg>
                    <span><?= e($label) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>
    <script>window.APP_BASE_URL = <?= json_encode(url('/')) ?>;</script>
    <script src="<?= url('/assets/js/app.js?v=' . $jsVersion) ?>"></script>
</body>
</html>
