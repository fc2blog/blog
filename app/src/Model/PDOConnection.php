<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use PDO;

class PDOConnection
{
    // TODO singleton は最終的に削除する
    private static $instance;

    public static function getInstance(bool $rebuild = false): self
    {
        if (!isset(self::$instance) || $rebuild) {
            self::$instance = new self();
            self::$instance->pdo = static::createConnection();
        }
        return self::$instance;
    }

    /** @var PDO */
    public $pdo;

    public static function createConnection(): PDO
    {
        $host = DB_HOST;
        $port = DB_PORT;
        $user = DB_USER;
        $password = DB_PASSWORD;
        $database = DB_DATABASE;
        $charset = DB_CHARSET;

        return new PDO(
            "mysql:host={$host};port={$port};dbname={$database};charset={$charset};",
            $user,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
