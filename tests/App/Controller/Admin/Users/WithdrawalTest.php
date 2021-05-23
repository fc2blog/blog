<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Users;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\SessionController;
use Fc2blog\Web\Controller\Admin\UsersController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class WithdrawalTest extends TestCase
{
    use ClientTrait;

    public function testForm(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/users/withdrawal");
        $this->assertInstanceOf(UsersController::class, $c);
        $this->assertEquals("withdrawal", $c->getResolvedMethod());
    }

    public function testWithdrawal(): void
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
                "delete" => "on",
            ]
        ];
        $r = $this->reqPostBeRedirect("/admin/users/withdrawal", $request_data);
        $this->assertEquals("/admin/session/login", $r->redirectUrl);

        $this->resetSigOnlySession();

        // ログインのトライ(退会しているので、失敗する
        $c = $this->reqPost("/admin/session/doLogin", [
            'sig' => $this->getSig(),
            'user' => [
                'login_id' => 'testadmin@localhost',
                'password' => 'testadmin@localhost',
            ]
        ]);
        $this->assertInstanceOf(SessionController::class, $c);
        $this->assertEquals('doLogin', $c->getResolvedMethod());

        DBHelper::clearDbAndInsertFixture();
    }
}
