<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Users;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
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

    $request_data = [
      'user' => [
        "delete" => "on",
      ]
    ];
    $r = $this->reqPostBeRedirect("/admin/users/withdrawal", $request_data);
    $this->assertEquals("/admin/users/login", $r->redirectUrl);

    // ログインのトライ(退会しているので、失敗する
    $c = $this->reqPost("/admin/users/login", [
      'user' => [
        'login_id' => 'testadmin',
        'password' => 'testadmin',
      ]
    ]);
    $this->assertInstanceOf(UsersController::class, $c);
    $this->assertEquals('login', $c->getResolvedMethod());
  }
}
