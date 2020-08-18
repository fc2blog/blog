<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use Fc2blog\Model\BlogsModel;
use Fc2blog\Config;
use PHPUnit\Framework\TestCase;

class GetSchemaBySslEnableValueTest extends TestCase
{
  public function testGetSchemaBySslEnableValue(): void
  {
    $this->assertEquals('https:', BlogsModel::getSchemaBySslEnableValue(Config::get('BLOG.SSL_ENABLE.ENABLE')));
    $this->assertEquals('http:', BlogsModel::getSchemaBySslEnableValue(Config::get('BLOG.SSL_ENABLE.DISABLE')));
  }
}
