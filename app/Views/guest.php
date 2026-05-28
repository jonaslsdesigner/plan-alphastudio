<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0052a8">
    <title><?= e($title ?? 'Alpha Planilhas') ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= url('/icon.svg') ?>">
    <link rel="shortcut icon" href="<?= url('/icon.svg') ?>">
    <link rel="apple-touch-icon" href="<?= url('/icon.svg') ?>">
    <link rel="manifest" href="<?= url('/manifest.webmanifest') ?>">
    <link rel="stylesheet" href="<?= url('/assets/css/app.css?v=' . (@filemtime(__DIR__ . '/../../public/assets/css/app.css') ?: time())) ?>">
</head>
<body class="app-shell auth-page min-h-screen<?= !empty($authPageClass) ? ' ' . e($authPageClass) : '' ?>">
    <main class="auth-page-main flex min-h-screen items-center justify-center px-3 py-3 sm:px-5 sm:py-6">
        <section class="auth-shell auth-shell-alt">
            <aside class="auth-visual">
                <?php if (!empty($authShowCornerBrand)): ?>
                    <div class="auth-corner-bar">
                        <div class="auth-corner-brand">
                            <span class="auth-corner-brand-mark" aria-hidden="true">
                                <svg viewBox="0 0 24 24"><path d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2h2V2Zm12 8H5v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8ZM7 6v2h10V6H7Z" fill="currentColor"/></svg>
                            </span>
                            <span class="auth-corner-brand-copy">
                                <strong>Alpha Plan</strong>
                                <span>Alpha Planilhas</span>
                            </span>
                        </div>
                        <span class="auth-corner-locale"><?= e($authLocaleLabel ?? 'PT') ?></span>
                    </div>
                <?php endif; ?>
                <div class="auth-dots" aria-hidden="true"><i></i><i></i><i></i><i></i><i></i></div>
                <div class="auth-visual-content auth-visual-content-logoonly">
                    <div class="auth-logo auth-logo-centered">
                        <span class="auth-logo-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M7 2h2v2h6V2h2v2h2a2 2 0 0 1 2 2v12a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V6a2 2 0 0 1 2-2h2V2Zm12 8H5v8a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-8ZM7 6v2h10V6H7Z" fill="currentColor"/></svg>
                        </span>
                        <span class="auth-logo-copy">
                            <strong>Alpha Plan</strong>
                            <span>Alpha Planilhas</span>
                        </span>
                    </div>
                    <?php if (!empty($authHeroDescription)): ?>
                        <p class="auth-visual-caption"><?= e($authHeroDescription) ?></p>
                    <?php endif; ?>
                </div>
            </aside>
            <div class="auth-form-panel">
                <?= $content ?>
            </div>
        </section>
    </main>
    <script>window.APP_BASE_URL = <?= json_encode(url('/')) ?>;</script>
    <script src="<?= url('/assets/js/app.js?v=' . (@filemtime(__DIR__ . '/../../public/assets/js/app.js') ?: time())) ?>"></script>
</body>
</html>
