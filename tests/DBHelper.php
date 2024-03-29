<?php
declare(strict_types=1);

namespace Fc2blog\Tests;

use Fc2blog\App;
use Fc2blog\Model\PDOConnection;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DBHelper extends TestCase
{
    public static function clearDbAndInsertFixture()
    {
        static::clearDb();

        $pdo = static::getPdo();

        // コメントを含むようなダンプSQLを流し込み実行する場合、EMULATE_PREPARESの有効化が必要となる。
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        // DB接続確認(DATABASEの存在判定含む)
        $sql = file_get_contents(App::APP_DIR . 'db/0_initialize.sql');
        if (DB_CHARSET != 'UTF8MB4') {
            $sql = str_replace('utf8mb4', strtolower(DB_CHARSET), $sql);
        }
        $pdo->query($sql);

        // load fixture
        $sql = file_get_contents(__DIR__ . "/test_fixture.sql");
        $pdo->query($sql);

        // EMULATE_PREPARESを戻す
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        // copy test files
        static::copyTestImages();
    }

    public static function clearDb()
    {
        $pdo = static::getPdo();
        // コメントを含むようなダンプSQLを流し込み実行する場合、EMULATE_PREPARESの有効化が必要となる。
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $pdo->query(file_get_contents(__DIR__ . "/test_drop_all_table.sql"));
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    }

    public static function getPdo(): PDO
    {
        return PDOConnection::createConnection();
    }

    public static function copyTestImages()
    {
        $pdo = static::getPdo();

        $stmt = $pdo->prepare("select * from files");
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $move_file_path = App::getUserFilePath($row, true);
            App::mkdir($move_file_path);
            $source_file_path = __DIR__ . '/test_images/' . $row['name'];
            if (!is_file($source_file_path)) {
                throw new RuntimeException("sample image not found. please run `make test-download-images` before tests.");
            }
            if (!copy($source_file_path, $move_file_path)) {
                throw new RuntimeException("copy failed");
            }
        }
    }
}
