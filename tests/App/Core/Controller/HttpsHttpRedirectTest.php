<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Controller;

use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;

class HttpsHttpRedirectTest extends TestCase
{
  use ClientTrait;

  public static $https_blog_id_path = '/testblog1/';
  public static $http_blog_id_path = '/testblog2/';

  public function testRedirect(): void
  {
    $res = $this->executeWithShouldExit('/');
    # ブログのIDがランダムに出てくる
    $this->assertStringContainsString("testblog", $res);
  }

  public function testHttpToHttpsRedirect(): void
  {
    $res = $this->executeWithShouldExit(static::$https_blog_id_path, false);
    $this->assertStringContainsString('https://', $res);
  }

  public function testHttpsToHttpRedirect(): void
  {
    $res = $this->executeWithShouldExit(static::$http_blog_id_path, true);
    $this->assertStringContainsString('http://', $res);
  }

  public function testHttpsToHttpsNoRedirect(): void
  {
    $res = $this->execute(static::$https_blog_id_path, true);
    $this->assertStringStartsWith('<!DOCTYPE html', $res);
  }

  public function testHttpToHttpNoRedirect(): void
  {
    $res = $this->execute(static::$http_blog_id_path, false);
    $this->assertStringStartsWith('<!DOCTYPE html', $res);
  }
}
