<?php

namespace App\Core;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection) {
            return self::$connection;
        }

        $config = require __DIR__ . '/../config.php';
        $db = $config['db'];
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";

        try {
            self::$connection = new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            $message = 'Erro ao conectar no MySQL. Confira o arquivo .env, app/config.php ou as variaveis de ambiente.';

            if (!empty($config['app']['debug'])) {
                $message .= '<br><small>' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</small>';
            }

            exit($message);
        }

        return self::$connection;
    }
}
