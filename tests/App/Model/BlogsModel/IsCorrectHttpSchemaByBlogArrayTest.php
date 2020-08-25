<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class IsCorrectHttpSchemaByBlogArrayTest extends TestCase
{
  public function testIsCorrectHttpSchemaByBlogArray(): void
  {
    $request = new Request(
      'GET', '/', null, null, null, null,
      [
        'HTTP_USER_AGENT' => 'phpunit',
        'HTTPS' => "on"
      ]
    );
    $this->assertFalse(BlogsModel::isCorrectHttpSchemaByBlogArray($request, ['ssl_enable' => Config::get('BLOG.SSL_ENABLE.DISABLE')]));
    $request = new Request(
      'GET', '/', null, null, null, null,
      [
        'HTTP_USER_AGENT' => 'phpunit',
      ]
    );
    $this->assertTrue(BlogsModel::isCorrectHttpSchemaByBlogArray($request, ['ssl_enable' => Config::get('BLOG.SSL_ENABLE.DISABLE')]));

    $request = new Request(
      'GET', '/', null, null, null, null,
      [
        'HTTP_USER_AGENT' => 'phpunit',
        'HTTPS' => "on"
      ]
    );
    $this->assertTrue(BlogsModel::isCorrectHttpSchemaByBlogArray($request, ['ssl_enable' => Config::get('BLOG.SSL_ENABLE.ENABLE')]));
    $request = new Request(
      'GET', '/', null, null, null, null,
      [
        'HTTP_USER_AGENT' => 'phpunit',
      ]
    );
    $this->assertFalse(BlogsModel::isCorrectHttpSchemaByBlogArray($request, ['ssl_enable' => Config::get('BLOG.SSL_ENABLE.ENABLE')]));
  }
}
