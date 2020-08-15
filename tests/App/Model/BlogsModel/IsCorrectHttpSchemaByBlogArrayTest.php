<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use BlogsModel;

use Model;
use PHPUnit\Framework\TestCase;

class IsCorrectHttpSchemaByBlogArrayTest extends TestCase
{
  public function setUp(): void
  {
    if (!class_exists(\Fc2blog\Model\BlogsModel::class)) {
      \Fc2blog\Model\Model::load('blogs');
    }

    parent::setUp();
  }

  public function testIsCorrectHttpSchemaByBlogArray(): void
  {
    $_SERVER['HTTPS'] = "on";
    $this->assertFalse(\Fc2blog\Model\BlogsModel::isCorrectHttpSchemaByBlogArray(['ssl_enable' => \Fc2blog\Config::get('BLOG.SSL_ENABLE.DISABLE')]));
    unset($_SERVER['HTTPS']);
    $this->assertTrue(\Fc2blog\Model\BlogsModel::isCorrectHttpSchemaByBlogArray(['ssl_enable' => \Fc2blog\Config::get('BLOG.SSL_ENABLE.DISABLE')]));

    $_SERVER['HTTPS'] = "on";
    $this->assertTrue(\Fc2blog\Model\BlogsModel::isCorrectHttpSchemaByBlogArray(['ssl_enable' => \Fc2blog\Config::get('BLOG.SSL_ENABLE.ENABLE')]));
    unset($_SERVER['HTTPS']);
    $this->assertFalse(\Fc2blog\Model\BlogsModel::isCorrectHttpSchemaByBlogArray(['ssl_enable' => \Fc2blog\Config::get('BLOG.SSL_ENABLE.ENABLE')]));
  }
}
