<?php
declare(strict_types=1);

namespace Fc2blog\Tests;


use MSDB;
use PDO;
use PHPUnit\Framework\TestCase;

class DBHelper extends TestCase
{
  public static function clearDbAndInsertFixture()
  {
    $msdb = \Fc2blog\Model\MSDB::getInstance();

    // DB接続確認(DATABASEの存在判定含む)
    $msdb->connect();

    // load DDL (DBが初期化されます！)
    $sql_path = \Fc2blog\Config::get('CONFIG_DIR') . 'blog.sql';
    $sql = file_get_contents($sql_path);
    if (DB_CHARSET != 'UTF8MB4') {
      $sql = str_replace('utf8mb4', strtolower(DB_CHARSET), $sql);
    }
    \Fc2blog\Model\MSDB::getInstance()->multiExecute($sql);

    // load fixture
    $sql = file_get_contents(__DIR__ . "/test_fixture.sql");
    $pdo = new PDO("mysql:host=" . \Fc2blog\Config::get('MASTER_DB.HOST') . ";port=3306;dbname=" . \Fc2blog\Config::get('MASTER_DB.DATABASE') . ";charset=utf8mb4", \Fc2blog\Config::get('MASTER_DB.USER'), \Fc2blog\Config::get('MASTER_DB.PASSWORD'));
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->query($sql);
  }
}
