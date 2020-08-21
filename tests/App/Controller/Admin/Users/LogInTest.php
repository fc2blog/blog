<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Users;

use Fc2blog\Exception\PseudoExit;
use Fc2blog\Web\Controller\Admin\CommonController;
use Fc2blog\Web\Controller\Admin\UsersController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Router\Router;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class LogInTest extends TestCase
{
  public function testBeforeLoginRedirect(): void
  {
    Session::destroy(new Request());

    $request = new Request(
      "GET",
      "/admin/",
    );

    $router = new Router($request);
    $resolve = $router->resolve();

    try {
      new $resolve['className']($resolve['request'], $resolve['methodName']);
      $this->fail();
    } catch (PseudoExit $e) {
      $this->assertStringContainsString("/admin/users/login", $e->getMessage());
    }
  }

  public function testLoginForm(): void
  {
    $request = new Request(
      "GET",
      "/admin/users/login",
    );

    $router = new Router($request);
    $resolve = $router->resolve();

    $c = new $resolve['className']($resolve['request'], $resolve['methodName']);
    $this->assertInstanceOf(UsersController::class, $c);
    $this->assertEquals("管理画面へログイン", $c->get('html_title'));
    $this->assertEquals("admin/layouts/default.php", $c->getLayoutFilePath());
    $this->assertEquals("admin/users/login.php", $c->getTemplateFilePath());
  }

  public function testLogin(): void
  {
    Session::destroy(new Request());

    $request = new Request(
      "POST",
      "/admin/users/login",
      [],
      [
        'user' => [
          'login_id' => 'testadmin',
          'password' => 'testadmin',
        ]
      ],
    );
    $router = new Router($request);
    $resolve = $router->resolve();

    try {
      new $resolve['className']($resolve['request'], $resolve['methodName']);
      $this->fail();
    } catch (PseudoExit $e) {
      $this->assertStringContainsString(' /admin/ ', $e->getMessage());
    }

    var_export($_SESSION);
    $this->assertEquals(1, $_SESSION['user_id']);
    $this->assertEquals('testadmin', $_SESSION['login_id']);
    $this->assertEquals(1, $_SESSION['user_type']);
    $this->assertEquals('testblog2', $_SESSION['blog_id']);
    $this->assertEquals('testnick2', $_SESSION['nickname']);
  }

  public function testKeepLogin(): void
  {
    $_SESSION = [
      'user_id' => 1,
      'login_id' => 'testadmin',
      'user_type' => 1,
      'blog_id' => 'testblog2',
      'nickname' => 'testnick2',
    ];

    $request = new Request(
      "GET",
      "/admin/common/notice",
      $_SESSION,
    );
    $router = new Router($request);
    $resolve = $router->resolve();

    $c = new $resolve['className']($resolve['request'], $resolve['methodName']);
    $this->assertInstanceOf(CommonController::class, $c);
    $this->assertEquals("notice", $c->getResolvedMethod());
    $this->assertEquals("admin/layouts/default.php", $c->getLayoutFilePath());
    $this->assertEquals("admin/common/notice.php", $c->getTemplateFilePath());
  }

  public function testLogout(): void
  {
    $_SESSION = [
      'user_id' => 1,
      'login_id' => 'testadmin',
      'user_type' => 1,
      'blog_id' => 'testblog2',
      'nickname' => 'testnick2',
    ];

    $request = new Request(
      "GET",
      "/admin/users/logout",
      $_SESSION,
    );
    $router = new Router($request);
    $resolve = $router->resolve();

    try {
      new $resolve['className']($resolve['request'], $resolve['methodName']);
      $this->fail();
    } catch (PseudoExit $e) {
      $this->assertStringContainsString(' /admin/users/login ', $e->getMessage());
    }

    $this->assertEmpty($_SESSION);
  }
}
