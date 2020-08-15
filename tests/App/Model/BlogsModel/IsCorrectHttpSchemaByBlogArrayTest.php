<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use Fc2blog\Model\BlogsModel;
use Fc2blog\Config;
use Fc2blog\Model\Model;
use PHPUnit\Framework\TestCase;

class IsCorrectHttpSchemaByBlogArrayTest extends TestCase
{
  public function setUp(): void
  {
    if (!class_exists(BlogsModel::class)) {
      Model::load('blogs');
    }

    parent::setUp();
  }

  public function testIsCorrectHttpSchemaByBlogArray(): void
  {
    $_SERVER['HTTPS'] = "on";
    $this->assertFalse(BlogsModel::isCorrectHttpSchemaByBlogArray(['ssl_enable' => Config::get('BLOG.SSL_ENABLE.DISABLE')]));
    unset($_SERVER['HTTPS']);
    $this->assertTrue(BlogsModel::isCorrectHttpSchemaByBlogArray(['ssl_enable' => Config::get('BLOG.SSL_ENABLE.DISABLE')]));

    $_SERVER['HTTPS'] = "on";
    $this->assertTrue(BlogsModel::isCorrectHttpSchemaByBlogArray(['ssl_enable' => Config::get('BLOG.SSL_ENABLE.ENABLE')]));
    unset($_SERVER['HTTPS']);
    $this->assertFalse(BlogsModel::isCorrectHttpSchemaByBlogArray(['ssl_enable' => Config::get('BLOG.SSL_ENABLE.ENABLE')]));
  }
}
