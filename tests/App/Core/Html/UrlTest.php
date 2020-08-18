<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Html;

use Fc2blog\Config;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
  public function setUp(): void
  {
    # 擬似的にアクセスURLをセットする
    $_SERVER['HTTP_USER_AGENT'] = "phpunit";
    $_SERVER['HTTPS'] = "on";
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_SERVER['REQUEST_URI'] = "/";
    $_POST = [];

    Config::read('user.php', true);

    parent::setUp();
  }

  public function testNotFullUrl()
  {
    # 擬似的にアクセスURLをセットする
    $request = new Request(
      'GET',
      '/',
      null,
      null,
      null,
      null,
      [
        'HTTP_USER_AGENT'=>'phpunit',
        'HTTPS'=>"on"
      ]
    );
    $url = Html::url($request, [
      'controller' => 'user',
      'action' => 'action',
      'blog_id' => 'testblog1'
    ], false, false);
    echo $url;
    $this->assertStringStartsWith('/', $url);
    $this->assertStringNotContainsString('http', $url);
  }

  public function testFullUrl()
  {
    $request = new Request(
      'GET',
      '/',
      null,
      null,
      null,
      null,
      [
        'HTTP_USER_AGENT'=>'phpunit',
        'HTTPS'=>"on"
      ]
    );
    $url = Html::url($request, [
      'controller' => 'user',
      'action' => 'action',
      'blog_id' => 'testblog1'
    ], false, true);
    echo $url;
    $this->assertStringStartsWith('https://', $url);
  }
}
