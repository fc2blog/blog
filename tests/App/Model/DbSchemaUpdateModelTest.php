<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model;

use Fc2blog\Model\DbSchemaUpdateModel;
use Fc2blog\Model\PDOConnection;
use PHPUnit\Framework\TestCase;

class DbSchemaUpdateModelTest extends TestCase
{
    public function testGetExistsTables()
    {
        $pdo = PDOConnection::createConnection();
        $dbs = new DbSchemaUpdateModel($pdo);
        $table_list = $dbs->getExistsTables();
        $this->assertIsArray($table_list);
    }

    public function testGetMigrateSqlList()
    {
        $pdo = PDOConnection::createConnection();
        $dbs = new DbSchemaUpdateModel($pdo);
        $sql_file_list = $dbs->getMigrateSqlList(0);
        $this->assertIsArray($sql_file_list);

        $sql_file_list = $dbs->getMigrateSqlList(99991010160000);
        $this->assertEmpty($sql_file_list);
    }

    public function testGetTick()
    {
        $pdo = PDOConnection::createConnection();
        $dbs = new DbSchemaUpdateModel($pdo);
        $tick = $dbs->getNowTick();
        $this->assertIsInt($tick);
    }

    public function testGetTickNumFromFileName()
    {
        $pdo = PDOConnection::createConnection();
        $dbs = new DbSchemaUpdateModel($pdo);
        $num = $dbs->getTickStringFromFileName("2021100101000000_test.sql");
        $this->assertGreaterThan(1, $num);

        try {
            $dbs->getTickStringFromFileName("a2021100101000000_test.sql");
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $dbs->getTickStringFromFileName("a2021100101000000_test.sq");
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}
