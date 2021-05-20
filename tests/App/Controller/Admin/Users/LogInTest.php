<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Users;

use Fc2blog\Tests\DBHelper;
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
        $this->assertStringContainsString("管理画面へログイン", $c->getOutput());
    }

    public function testLogin(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();

        $r = $this->reqPostBeRedirect("/admin/users/login", [
            'user' => [
                'login_id' => 'testadmin@localhost',
                'password' => 'testadmin@localhost',
            ]
        ]);

        $this->assertEquals('/admin/', $r->redirectUrl);
        $this->assertEquals(302, $r->statusCode);

        $this->assertEquals(1, $this->clientTraitSession['user_id']);
        $this->assertEquals('testadmin@localhost', $this->clientTraitSession['login_id']);
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
