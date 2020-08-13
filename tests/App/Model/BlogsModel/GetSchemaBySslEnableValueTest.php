<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use BlogsModel;

use Model;
use PHPUnit\Framework\TestCase;

class GetSchemaBySslEnableValueTest extends TestCase
{
  public function setUp(): void
  {
    /** @noinspection PhpIncludeInspection */
    if (!class_exists(BlogsModel::class)) {
      \Fc2blog\Model\Model::load('blogs');
    }

    parent::setUp();
  }

  public function testGetSchemaBySslEnableValue(): void
  {
    $this->assertEquals('https:', BlogsModel::getSchemaBySslEnableValue(\Fc2blog\Config::get('BLOG.SSL_ENABLE.ENABLE')));
    $this->assertEquals('http:', BlogsModel::getSchemaBySslEnableValue(\Fc2blog\Config::get('BLOG.SSL_ENABLE.DISABLE')));
  }
}
