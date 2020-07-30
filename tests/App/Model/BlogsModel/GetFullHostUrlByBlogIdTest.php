<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use BlogsModel;
use Config;
use Fc2blog\Tests\LoaderHelper;
use Model;
use PHPUnit\Framework\TestCase;

class GetFullHostUrlByBlogIdTest extends TestCase
{
  public function setUp(): void
  {
    LoaderHelper::requireBootStrap();

    /** @noinspection PhpIncludeInspection */
    require_once(Config::get('MODEL_DIR') . 'model.php');
    if (!class_exists(BlogsModel::class)) {
      Model::load('blogs');
    }

    parent::setUp();
  }

  public function testGetFullHostUrlByBlogIdTest(): void
  {
    $this->assertEquals('https://localhost:8480', BlogsModel::getFullHostUrlByBlogId('testblog1', 'localhost'));
    $this->assertEquals('http://localhost:8080', BlogsModel::getFullHostUrlByBlogId('testblog2', 'localhost'));
  }
}
