<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use BlogsModel;

use Model;
use PHPUnit\Framework\TestCase;

class GetSchemaByBlogIdTest extends TestCase
{
  public function setUp(): void
  {
    /** @noinspection PhpIncludeInspection */
    require_once(\Fc2blog\Config::get('MODEL_DIR') . 'model.php');
    if (!class_exists(BlogsModel::class)) {
      Model::load('blogs');
    }

    parent::setUp();
  }

  public function testGetSchemaByBlogIdTest(): void
  {
    $this->assertEquals('https:', BlogsModel::getSchemaByBlogId('testblog1'));
    $this->assertEquals('http:', BlogsModel::getSchemaByBlogId('testblog2'));
  }
}
