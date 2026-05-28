<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $configUrl = rtrim((require __DIR__ . '/../config.php')['app']['url'], '/');
    $base = $configUrl ?: app_base_url();

    return rtrim($base, '/') . '/' . ltrim($path, '/');
}

function app_base_path(): string
{
    $script = rawurldecode(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
    $base = rtrim(dirname($script), '/');

    if (str_ends_with($base, '/public')) {
        $base = substr($base, 0, -7);
    }

    return $base === '.' ? '' : $base;
}

function app_base_url(): string
{
    return implode('/', array_map('rawurlencode', explode('/', app_base_path())));
}

function current_path(): string
{
    $path = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
    $base = app_base_path();

    if ($base && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base));
    }

    if ($path === '/public' || str_starts_with($path, '/public/')) {
        $path = substr($path, 7) ?: '/';
    }

    $path = '/' . trim($path, '/');

    return $path === '//' ? '/' : $path;
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf']))) {
        http_response_code(419);
        exit('Sessão expirada. Volte e tente novamente.');
    }
}

function money_br(float|int|string|null $value): string
{
    return 'R$ ' . number_format((float) $value, 2, ',', '.');
}

function money_value(float|int|string|null $value): float
{
    if (is_float($value) || is_int($value)) {
        return (float) $value;
    }

    $normalized = trim((string) $value);
    if ($normalized === '') {
        return 0.0;
    }

    $normalized = preg_replace('/[^\d,.-]/', '', $normalized) ?? '0';
    $hasComma = str_contains($normalized, ',');
    $hasDot = str_contains($normalized, '.');

    if ($hasComma && $hasDot) {
        $normalized = str_replace('.', '', $normalized);
        $normalized = str_replace(',', '.', $normalized);
    } elseif ($hasComma) {
        $normalized = str_replace(',', '.', $normalized);
    } elseif ($hasDot && preg_match('/^\d{1,3}(\.\d{3})+$/', $normalized)) {
        $normalized = str_replace('.', '', $normalized);
    }

    return (float) $normalized;
}

function edit_icon(): string
{
    return '<svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 17.3V20h2.7L17.8 8.9l-2.7-2.7L4 17.3Zm15.9-10.5a1 1 0 0 0 0-1.4l-1.3-1.3a1 1 0 0 0-1.4 0l-1 1 2.7 2.7 1-1Z"/></svg>';
}

function month_label(string $month): string
{
    $date = DateTime::createFromFormat('Y-m', $month) ?: new DateTime();
    $months = ['JAN', 'FEV', 'MAR', 'ABR', 'MAI', 'JUN', 'JUL', 'AGO', 'SET', 'OUT', 'NOV', 'DEZ'];
    return $months[(int) $date->format('n') - 1] . ' ' . $date->format('Y');
}
