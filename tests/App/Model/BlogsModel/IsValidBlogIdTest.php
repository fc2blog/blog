<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use Fc2blog\Tests\DBHelper;
use PHPUnit\Framework\TestCase;

class IsValidBlogIdTest extends TestCase
{
  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();

    parent::setUp();
  }

  public function testValid(): void
  {
    $this->assertTrue(\Fc2blog\Model\BlogsModel::isValidBlogId("testblog1"));
    $this->assertFalse(\Fc2blog\Model\BlogsModel::isValidBlogId("containSymbol!"));
    $this->assertFalse(\Fc2blog\Model\BlogsModel::isValidBlogId("aa"), "require more than 3 chars");
    $this->assertTrue(\Fc2blog\Model\BlogsModel::isValidBlogId("aaa"), "require more than 3 chars");
    $this->assertTrue(\Fc2blog\Model\BlogsModel::isValidBlogId(str_repeat("a", 50)), "require less 50 chars or less");
    $this->assertFalse(\Fc2blog\Model\BlogsModel::isValidBlogId(str_repeat("a", 51)), "require less 50 chars or less");
    $this->assertFalse(\Fc2blog\Model\BlogsModel::isValidBlogId("CAPITALCASENOTALLOWED"));
    $this->assertFalse(\Fc2blog\Model\BlogsModel::isValidBlogId("no space allowed"));
  }

}
