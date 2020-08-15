<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Html;

use BlogsModel;

use Html;
use Model;
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

    # Requestはキャッシュされるので、都度消去する
    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    \Fc2blog\Web\Request::resetInstanceForTesting();

    \Fc2blog\Config::read('user.php', true);

    /** @noinspection PhpIncludeInspection */
    if (!class_exists(\Fc2blog\Model\BlogsModel::class)) {
      \Fc2blog\Model\Model::load('blogs');
    }

    parent::setUp();
  }

  public function testNotFullUrl()
  {
    # 擬似的にアクセスURLをセットする
    $_SERVER['HTTP_USER_AGENT'] = "phpunit";
    $_SERVER['HTTPS'] = "on";
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_SERVER['REQUEST_URI'] = "/";
    $_POST = [];
    $url = \Fc2blog\Web\Html::url([
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
    # 擬似的にアクセスURLをセットする
    $_SERVER['HTTP_USER_AGENT'] = "phpunit";
    $_SERVER['HTTPS'] = "on";
    $_SERVER["REQUEST_METHOD"] = "GET";
    $_SERVER['REQUEST_URI'] = "/";
    $_POST = [];
    $url = \Fc2blog\Web\Html::url([
      'controller' => 'user',
      'action' => 'action',
      'blog_id' => 'testblog1'
    ], false, true);
    echo $url;
    $this->assertStringStartsWith('https://', $url);
  }
}
