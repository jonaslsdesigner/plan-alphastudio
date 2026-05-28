<?php

if (!function_exists('app_env')) {
    function app_env(string $key, ?string $default = null): ?string
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);

        if ($value === false || $value === null || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}

if (!function_exists('load_app_env')) {
    function load_app_env(string $path): void
    {
        if (!is_file($path) || !is_readable($path)) {
            return;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");

            if ($key !== '' && getenv($key) === false) {
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv($key . '=' . $value);
            }
        }
    }
}

load_app_env(__DIR__ . '/../.env');

return [
    'app' => [
        'name' => app_env('APP_NAME', 'Alpha Planilhas'),
        'url' => app_env('APP_URL', ''),
        'timezone' => app_env('APP_TIMEZONE', 'America/Sao_Paulo'),
        'debug' => app_env('APP_DEBUG', 'false') === 'true',
    ],
    'db' => [
        'host' => app_env('DB_HOST', '127.0.0.1'),
        'port' => app_env('DB_PORT', '3306'),
        'name' => app_env('DB_DATABASE', 'alpha_planilhas'),
        'user' => app_env('DB_USERNAME', 'root'),
        'pass' => app_env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
    ],
];
