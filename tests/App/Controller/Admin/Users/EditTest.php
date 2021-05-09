<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Users;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\UsersController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class EditTest extends TestCase
{
    use ClientTrait;

    public function testEditForm(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/users/edit");
        $this->assertInstanceOf(UsersController::class, $c);
        $this->assertEquals("edit", $c->getResolvedMethod());
    }

    public function testUpdateUser(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();
        $sig = $this->getSig();

        $request_data = [
            'sig' => $sig,
            'user' => [
                "login_blog_id" => "testblog1",
                "password" => "password123",
            ]
        ];
        $r = $this->reqPostBeRedirect("/admin/users/edit", $request_data);
        $this->assertEquals("/admin/users/edit", $r->redirectUrl);

        // ログインのトライ
        $this->resetSession();
        $this->resetCookie();

        $r = $this->reqPostBeRedirect("/admin/users/login", [
            'user' => [
                'login_id' => 'testadmin',
                'password' => 'password123',
            ]
        ]);

        $this->assertEquals('/admin/', $r->redirectUrl);
        $this->assertEquals(302, $r->statusCode);
        $this->assertEquals('testblog1', $this->clientTraitSession['blog_id']);

        DBHelper::clearDbAndInsertFixture();
    }
}
