<?php
declare(strict_types=1);

namespace App\Support;

use PDO;
use PDOException;

final class DB
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = Config::get('database.host', '127.0.0.1');
        $port = (int) Config::get('database.port', 3306);
        $name = Config::get('database.name', '');
        $user = Config::get('database.user', '');
        $pass = Config::get('database.pass', '');
        $charset = Config::get('database.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$name};charset={$charset}";

        try {
            self::$pdo = new PDO($dsn, (string)$user, (string)$pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Niet lekken naar output; alleen loggen
            error_log('[DB] Connection failed: ' . $e->getMessage());
            throw $e;
        }

        return self::$pdo;
    }

    public static function ping(): bool
    {
        try {
            $pdo = self::pdo();
            $pdo->query('SELECT 1');
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
