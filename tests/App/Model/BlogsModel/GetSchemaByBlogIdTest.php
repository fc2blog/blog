<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use BlogsModel;
use Config;
use InvalidArgumentException;
use Model;
use PHPUnit\Framework\TestCase;

class GetSchemaByBlogIdTest extends TestCase
{
  public function setUp(): void
  {
    // TODO Make Fixture

    if (file_exists(TEST_APP_DIR . "/../public/config.php")) {
      require_once(TEST_APP_DIR . "/../public/config.php");
    } else {
      require_once(TEST_APP_DIR . "/../docker/docker.config.php");
    }
    require_once(TEST_APP_DIR . "/core/bootstrap.php");

    /** @noinspection PhpIncludeInspection */
    require_once(Config::get('MODEL_DIR') . 'model.php');

    if (!class_exists(BlogsModel::class)) {
      Model::load('blogs');
    }

    parent::setUp();
  }

  public function testCorrectSchemaStrings(): void
  {
    $this->assertEquals('http', BlogsModel::getSchemaByBlogId("testblog"));
    $this->assertEquals('https', BlogsModel::getSchemaByBlogId("test2blog"));
  }

  public function testMissingBlogId(): void
  {
    try {
      BlogsModel::getSchemaByBlogId("--missing-blog-id--");
      $this->fail("存在しない場合は例外が投げられる");
    } catch (InvalidArgumentException $e) {
      $this->assertTrue(true);
    }

  }

}
