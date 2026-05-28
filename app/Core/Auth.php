<?php

namespace App\Core;

class Auth
{
    private static function normalizeEmail(string $email): string
    {
        return trim(mb_strtolower($email));
    }

    public static function emailExists(string $email): bool
    {
        $stmt = Database::connection()->prepare('SELECT 1 FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([self::normalizeEmail($email)]);
        return (bool) $stmt->fetchColumn();
    }

    public static function user(): ?array
    {
        if (empty($_SESSION['user_id'])) {
            return null;
        }

        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch() ?: null;

        if ($user === null) {
            unset($_SESSION['user_id']);
        }

        return $user;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            redirect('/login');
        }
    }

    public static function attempt(string $email, string $password): bool
    {
        $email = self::normalizeEmail($email);
        if ($email === '' || $password === '') {
            return false;
        }

        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $update = Database::connection()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            $update->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = (int) $user['id'];
        return true;
    }

    public static function register(string $name, string $email, string $password): int
    {
        $name = trim($name);
        $email = self::normalizeEmail($email);
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, monthly_income) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 0]);
        $userId = (int) $pdo->lastInsertId();

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION['user_id'] = $userId;

        return $userId;
    }

    public static function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool) $params['secure'], (bool) $params['httponly']);
        }

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}
