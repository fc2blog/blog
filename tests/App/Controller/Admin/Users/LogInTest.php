<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Users;

use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\CommonController;
use Fc2blog\Web\Controller\Admin\UsersController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class LogInTest extends TestCase
{
  use ClientTrait;

  public function testBeforeLoginRedirect(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $r = $this->reqGetBeRedirect("/admin/");

    $this->assertEquals("/admin/users/login", $r->redirectUrl);
  }

  public function testLoginForm(): void
  {
    $c = $this->reqGet("/admin/users/login");

    $this->assertInstanceOf(UsersController::class, $c);
    $this->assertEquals("管理画面へログイン", $c->get('html_title'));
    $this->assertEquals("admin/layouts/default.php", $c->getLayoutFilePath());
    $this->assertEquals("admin/users/login.php", $c->getTemplateFilePath());
  }

  public function testLogin(): void
  {
    $r = $this->reqPostWithExit("/admin/users/login", [
      'user' => [
        'login_id' => 'testadmin',
        'password' => 'testadmin',
      ]
    ]);

    $this->assertEquals('/admin/', $r->redirectUrl);
    $this->assertEquals(302, $r->statusCode);

    $this->assertEquals(1, $this->clientTraitSession['user_id']);
    $this->assertEquals('testadmin', $this->clientTraitSession['login_id']);
    $this->assertEquals(1, $this->clientTraitSession['user_type']);
    $this->assertEquals('testblog2', $this->clientTraitSession['blog_id']);
    $this->assertEquals('testnick2', $this->clientTraitSession['nickname']);
  }

  public function testKeepLogin(): void
  {
    $this->mergeAdminSession();
    $c = $this->reqGet("/admin/common/notice");

    $this->assertInstanceOf(CommonController::class, $c);
    $this->assertEquals("notice", $c->getResolvedMethod());
    $this->assertEquals("admin/layouts/default.php", $c->getLayoutFilePath());
    $this->assertEquals("admin/common/notice.php", $c->getTemplateFilePath());
  }

  public function testLogout(): void
  {
    $this->mergeAdminSession();
    $r = $this->reqGetBeRedirect("/admin/users/logout");

    $this->assertEquals('/admin/users/login', $r->redirectUrl);
    $this->assertEquals(302, $r->statusCode);
    $this->assertEmpty($this->clientTraitSession);
  }
}
