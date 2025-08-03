<?php namespace App\Shared;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function getInstance(): PDO
    {
        if (self::$connection === null) {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4',
                $_ENV['DB_HOST'],
                $_ENV['DB_NAME'],
                $_ENV['DB_PORT']
            );

            self::$connection = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);

        }
        return self::$connection;
    }
}