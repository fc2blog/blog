<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use DirectoryIterator;
use InvalidArgumentException;
use PDO;

class DbSchemaUpdateModel
{
    public $pdo;
    const MIGRATE_SQL_DIR = __DIR__ . "/../../db/migrate/";

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getMigrateSqlList($gth_tick): array
    {
        $dir = new DirectoryIterator(static::MIGRATE_SQL_DIR);

        $migrate_list = [];
        foreach ($dir as $row) {
            if (
                $row->isFile() &&
                $row->getExtension() === "sql" &&
                $this->getTickStringFromFileName($row->getFilename()) > $gth_tick

            ) {
                $migrate_list[$this->getTickStringFromFileName($row->getFilename())] = $row->getFilename();
            }
        }

        ksort($migrate_list);

        return $migrate_list;
    }

    public function getExistsTables(): array
    {
        $stmt = $this->pdo->prepare("SHOW TABLES");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function getNowTick(): int
    {
        $stmt = $this->pdo->prepare("SELECT tick FROM db_migrate_tick ORDER BY tick DESC");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN, 0);
    }

    public function getTickStringFromFileName(string $file_name): int
    {
        if (
            !preg_match("/\A([0-9]+).*.sql\z/u", $file_name, $_) &&
            !isset($_[1])
        ) {
            throw new InvalidArgumentException("invalid filename");
        }

        return (int)$_[1];
    }

}
