<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Users;

use Fc2blog\Tests\Helper\ClientTrait2;
use Fc2blog\Web\Controller\Admin\CommonController;
use Fc2blog\Web\Controller\Admin\UsersController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class LogInTest extends TestCase
{
  use ClientTrait2;

  public function testBeforeLoginRedirect(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $e = $this->reqGetWithExit("/admin/");

    $this->assertStringContainsString("/admin/users/login", $e->getMessage());
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
    $e = $this->reqPostWithExit("/admin/users/login", [
      'user' => [
        'login_id' => 'testadmin',
        'password' => 'testadmin',
      ]
    ]);

    $this->assertStringContainsString('redirect to /admin/ status code:302', $e->getMessage());

    $this->assertEquals(1, $_SESSION['user_id']);
    $this->assertEquals('testadmin', $_SESSION['login_id']);
    $this->assertEquals(1, $_SESSION['user_type']);
    $this->assertEquals('testblog2', $_SESSION['blog_id']);
    $this->assertEquals('testnick2', $_SESSION['nickname']);
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
    $e = $this->reqGetWithExit("/admin/users/logout");

    $this->assertStringContainsString(' /admin/users/login ', $e->getMessage());
    $this->assertEmpty($_SESSION);
  }
}
