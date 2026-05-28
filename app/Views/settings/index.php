<?php
$pageTitle = 'Perfil';
$selectedTheme = strtolower($user['theme_color'] ?? '#066ab5');
$themeOptions = [
    ['name' => 'Alpha Green', 'value' => '#066ab5', 'colors' => ['#0f1b2d', '#22c88a', '#ffffff']],
    ['name' => 'Forest', 'value' => '#1a3a2a', 'colors' => ['#1a3a2a', '#4caf7d', '#ffffff']],
    ['name' => 'Navy Cyan', 'value' => '#0d1f3c', 'colors' => ['#0d1f3c', '#00bcd4', '#ffffff']],
    ['name' => 'Violet', 'value' => '#1e1040', 'colors' => ['#1e1040', '#a78bfa', '#ffffff']],
];
if (!in_array($selectedTheme, array_column($themeOptions, 'value'), true)) {
    $selectedTheme = '#066ab5';
}
?>
<section class="max-w-3xl">
    <form method="post" enctype="multipart/form-data" class="app-panel space-y-4" data-auto-save-profile>
        <?= csrf_field() ?><input type="hidden" name="_back" value="/settings">
        <div class="flex items-center gap-4">
            <label class="app-profile-photo-edit" aria-label="Alterar foto de perfil">
                <?php if (!empty($user['avatar_path'])): ?>
                    <img class="app-profile-photo" src="<?= url('/' . $user['avatar_path']) ?>" alt="">
                <?php else: ?>
                    <div class="app-profile-avatar app-profile-photo"><?= e(substr($user['name'], 0, 1)) ?></div>
                <?php endif; ?>
                <span class="app-profile-photo-badge" aria-hidden="true">
                    <svg viewBox="0 0 24 24"><path d="M4 17.2V20h2.8l8.3-8.3-2.8-2.8L4 17.2Zm12-10.4 2.8 2.8 1.4-1.4a1 1 0 0 0 0-1.4l-1.4-1.4a1 1 0 0 0-1.4 0L16 6.8Z"/></svg>
                </span>
                <input class="app-profile-photo-input" type="file" name="avatar" accept="image/*">
            </label>
            <div>
                <h2 class="text-lg font-black">Personalização do perfil</h2>
                <p class="text-sm font-semibold text-[#77798a]">Nome, idade, foto e tema para deixar o app com sua cara.</p>
            </div>
        </div>
        <label class="app-label">Seu nome<input class="app-input" name="name" value="<?= e($user['name']) ?>" required></label>
        <label class="app-label">Idade<input class="app-input" type="number" name="age" min="1" max="120" value="<?= e($user['age'] ?? '') ?>"></label>
        <input type="hidden" name="monthly_income" value="<?= e($user['monthly_income'] ?? 0) ?>">
        <label class="app-label">Moeda<input class="app-input" name="currency" value="<?= e($user['currency'] ?? 'BRL') ?>"></label>

        <fieldset class="space-y-2">
            <legend class="app-label">Tema de cores</legend>
            <div class="grid gap-2 sm:grid-cols-2">
                <?php foreach ($themeOptions as $theme): ?>
                    <label class="theme-option <?= $selectedTheme === $theme['value'] ? 'is-selected' : '' ?>">
                        <input type="radio" name="theme_color" value="<?= e($theme['value']) ?>" <?= $selectedTheme === $theme['value'] ? 'checked' : '' ?>>
                        <span class="theme-swatch">
                            <?php foreach ($theme['colors'] as $color): ?><i style="background: <?= e($color) ?>"></i><?php endforeach; ?>
                        </span>
                        <strong><?= e($theme['name']) ?></strong>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>

        <div class="rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-500">
            O app já possui manifest e service worker. No celular, abra no navegador e use "Adicionar à Tela Inicial".
        </div>
        <div class="rounded-2xl bg-slate-50 p-4 text-sm font-semibold text-slate-500">
            As alterações do perfil são salvas automaticamente.
        </div>
    </form>
    <a class="app-logout-panel" href="<?= url('/logout') ?>">
        <span class="app-logout-panel-brand">
            <span class="app-brand-mark grid h-9 w-9 place-items-center rounded-full text-sm font-black text-white">A</span>
            <span>
                <span class="block text-sm font-bold">Alpha Planilhas</span>
                <span class="block text-[11px] text-slate-500">Dashboard</span>
            </span>
        </span>
        <span class="app-logout-panel-action">
            <span>Sair da conta</span>
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 4H5v16h5v-2H7V6h3V4Zm6.6 4.4-1.4 1.4L16.4 11H10v2h6.4l-1.2 1.2 1.4 1.4L20.8 12l-4.2-3.6Z"/></svg>
        </span>
    </a>
</section>
