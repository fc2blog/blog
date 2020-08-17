<?php
declare(strict_types=1);

namespace Fc2blog\Tests;

use Fc2blog\Config;
use Fc2blog\Model\MSDB;
use PDO;
use PHPUnit\Framework\TestCase;

class DBHelper extends TestCase
{
  public static function clearDbAndInsertFixture()
  {
    static::clearDb();

    $pdo = new PDO("mysql:host=" . Config::get('MASTER_DB.HOST') . ";port=3306;dbname=" . Config::get('MASTER_DB.DATABASE') . ";charset=utf8mb4", Config::get('MASTER_DB.USER'), Config::get('MASTER_DB.PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // DB接続確認(DATABASEの存在判定含む)
    $sql = file_get_contents(Config::get('APP_DIR') . 'db/0_initialize.sql');
    if (DB_CHARSET != 'UTF8MB4') {
      $sql = str_replace('utf8mb4', strtolower(DB_CHARSET), $sql);
    }
    $pdo->query($sql);

    // load fixture
    $sql = file_get_contents(__DIR__ . "/test_fixture.sql");
    $pdo->query($sql);
  }

  public static function clearDb()
  {
    $pdo = new PDO("mysql:host=" . Config::get('MASTER_DB.HOST') . ";port=3306;dbname=" . Config::get('MASTER_DB.DATABASE') . ";charset=utf8mb4", Config::get('MASTER_DB.USER'), Config::get('MASTER_DB.PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query(file_get_contents(__DIR__."/test_drop_all_table.sql"));
  }
}
