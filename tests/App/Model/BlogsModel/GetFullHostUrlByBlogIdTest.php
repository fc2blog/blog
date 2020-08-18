<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use Fc2blog\Model\BlogsModel;
use PHPUnit\Framework\TestCase;

class GetFullHostUrlByBlogIdTest extends TestCase
{
  public function testGetFullHostUrlByBlogIdTest(): void
  {
    $this->assertEquals('https://localhost:8480', BlogsModel::getFullHostUrlByBlogId('testblog1', 'localhost'));
    $this->assertEquals('http://localhost:8080', BlogsModel::getFullHostUrlByBlogId('testblog2', 'localhost'));
  }
}
