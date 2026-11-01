<?php

declare(strict_types=1);

namespace Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new RuntimeException('A database connection cannot be unserialized.');
    }

    public static function getConnection(): PDO
    {
        if (self::$instance === null)
        {
            $config = config('database');

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );

            try
            {
                self::$instance = new PDO(
                    $dsn,
                    $config['username'],
                    $config['password'],
                    $config['options'] ?? []
                );

                // MySQL's NOW() on the school's clock too (bootstrap sets PHP's), so a date PHP saves and NOW() agree
                self::$instance->exec("SET time_zone = '" . date('P') . "'");
            }
            catch (PDOException $exception)
            {
                error_log('Database connection error: ' . $exception->getMessage());

                throw new RuntimeException('The database connection failed.', 0, $exception);
            }
        }

        return self::$instance;
    }
}