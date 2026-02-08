<?php
declare(strict_types=1);

namespace App;

final class Db
{
    public static function pdo(): \PDO
    {
        // Railway provides separate variables, build DSN from them
        $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
        $name = $_ENV['DB_NAME'] ?? 'app';
        $port = $_ENV['DB_PORT'] ?? '3306';
        $user = $_ENV['DB_USER'] ?? 'app_user';
        $pass = $_ENV['DB_PASS'] ?? 'change_me';
        
        // Allow custom DSN to override
        $dsn = $_ENV['DB_DSN'] ?? "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

        // PDO / pdo_mysql না থাকলে throw exception
        if (!class_exists(\PDO::class)) {
            throw new \RuntimeException('PDO extension is not available');
        }
        
        if (str_starts_with($dsn, 'mysql:') && !in_array('mysql', \PDO::getAvailableDrivers(), true)) {
            throw new \RuntimeException('PDO MySQL driver is not available');
        }

        try {
            $pdo = new \PDO($dsn, $user, $pass, [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]);

            if (str_starts_with($dsn, 'mysql:')) {
                $pdo->exec("SET SESSION sql_mode='STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");
                $pdo->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

            return $pdo;
        } catch (\PDOException $e) {
            // Log and throw instead of silent exit
            error_log('DB connect error: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }
    }
}
