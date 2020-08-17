<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Controller;

use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\Model;
use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{
  use ClientTrait;

  public static $testBase = '/_for_unit_test_';

  public function setUp(): void
  {
    if (!class_exists(BlogsModel::class)) {
      Model::load('blogs');
    }

    DBHelper::clearDbAndInsertFixture();

    parent::setUp();
  }

  public function testTestTargetTest(): void
  {
    $res = $this->execute(static::$testBase . "/phpinfo", true, "GET", [], "test");
    $this->assertStringContainsString('phpinfo', $res);
  }

  public function testNotFullUriRedirect(): void
  {
    $res = $this->executeWithShouldExit(static::$testBase . "/redirect_test_no_full_url", true, "GET", [], "test");
    $this->assertStringNotContainsString('http', $res);

    $res = $this->executeWithShouldExit(static::$testBase . "/redirect_test_full_url?blog_id=testblog1", true, "GET", [], "test");
    $this->assertStringContainsString('http', $res);
  }
}
