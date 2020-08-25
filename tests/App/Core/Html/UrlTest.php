<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Html;

use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
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
        'HTTP_USER_AGENT' => 'phpunit',
        'HTTPS' => "on"
      ]
    );
    $url = Html::url($request, [
      'controller' => 'user',
      'action' => 'action',
      'blog_id' => 'testblog1'
    ], false, false);
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
        'HTTP_USER_AGENT' => 'phpunit',
        'HTTPS' => "on"
      ]
    );
    $url = Html::url($request, [
      'controller' => 'user',
      'action' => 'action',
      'blog_id' => 'testblog1'
    ], false, true);
    $this->assertStringStartsWith('https://', $url);
  }
}
