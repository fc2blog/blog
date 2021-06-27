<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Common;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\CommonController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use League\Flysystem\Adapter\Local;
use PHPUnit\Framework\TestCase;

class InstallTest extends TestCase
{
    use ClientTrait;

    public function testInstallCheck(): void
    {
        // 初期化（もろもろ削除）
        $fs = new Local(__DIR__ . '/../../../../../');
        if ($fs->has('app/temp/installed.lock')) {
            $this->assertTrue($fs->delete('app/temp/installed.lock'));
        }
        if ($fs->has('app/temp/blog_template')) {
            $this->assertTrue($fs->deleteDir('app/temp/blog_template'));
        }
        if ($fs->has('app/temp/log')) {
            $this->assertTrue($fs->deleteDir('app/temp/log'));
        }
        DBHelper::clearDb();

        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();

        $c = $this->reqGet("/admin/common/install");
        $d = $c->getData();
//    var_export($d);

        // すべて成功する前提のテスト
        $this->assertTrue($d['is_write_temp']);
        $this->assertTrue($d['is_write_upload']);
        $this->assertTrue($d['is_connect']);
        $this->assertEquals(0, strlen($d['connect_message']));
        $this->assertTrue($d['is_domain']);
        $this->assertTrue($d['is_gd']);
        $this->assertTrue($d['is_all_ok']);
    }

// TODO: コードを堅牢にした結果、失敗時ケースが書きにくくなったので一回コメントアウト

//    public function testInstallFailCheck(): void
//    {
//        $temp_dir = App::TEMP_DIR;
//        $www_upload_dir = App::WWW_UPLOAD_DIR;
//        try {
//            $fs = new Local(__DIR__ . '/../../../../../');
//            $fs->deleteDir('app/temp/blog_template');
//            $fs->deleteDir('app/temp/log');
//            DBHelper::clearDb();
//            Session::destroy(new Request());
//            $this->resetSession();
//            $this->resetCookie();
//
//            $c = $this->reqGet("/admin/common/install");
//            $d = $c->getData();
////      var_export($d);
//
//            // 確認がむずかしい
//            $this->assertTrue($d['is_write_temp']);
//            $this->assertTrue($d['is_write_upload']);
//            // 確認がむずかしい
//            $this->assertTrue($d['is_connect']);
//            // 確認がむずかしい
//            $this->assertEquals(0, strlen($d['connect_message']));
//            // 確認がむずかしい
//            $this->assertTrue($d['is_domain']);
//            // 確認がむずかしい
//            $this->assertTrue($d['is_gd']);
//
//            $this->assertFalse($d['is_all_ok']);
//
//        } finally {
//            // 復元している
//            chmod($temp_dir, 0777);
//            chmod($www_upload_dir, 0777);
//        }
//    }

    public function testInstallState1Check(): void
    {
        $this->testInstallCheck();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();

        $r = $this->reqGetBeRedirect("/admin/common/install", ['state' => 1]);
        $this->assertEquals('/admin/common/install?state=2', $r->redirectUrl);
    }

    public function testInstallState2Check(): void
    {
        $this->testInstallState1Check();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();

        $c = $this->reqGet("/admin/common/install", ['state' => 2]);
        $this->assertInstanceOf(CommonController::class, $c);
        $this->assertEquals('install', $c->getResolvedMethod());
    }

    public function testInstallState2PostCheck(): void
    {
        $this->testInstallState2Check();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();

        $request_data = [
            'state' => 2,
            'user' => [
                'login_id' => 'testadmintest@localhost',
                'password' => 'testadmintest@localhost',
            ],
            'blog' => [
                'id' => 'testblog',
                'name' => 'testname',
                'nickname' => 'testnick',
            ]
        ];

        $r = $this->reqPostBeRedirect("/admin/common/install", $request_data);
        $this->assertEquals('/admin/common/install?state=3', $r->redirectUrl);
    }

    public function testInstallState3Check(): void
    {
        $this->testInstallState2PostCheck();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();

        $c = $this->reqGet("/admin/common/install", ['state' => 3]);
        $this->assertInstanceOf(CommonController::class, $c);
        $this->assertEquals('install', $c->getResolvedMethod());

        $this->assertStringContainsString("インストール完了", $c->getOutput());

        // generate sig
        $this->resetSigOnlySession();
        // 本当に登録できたかログインテスト
        $r = $this->reqPostBeRedirect("/admin/session/doLogin", [
            'sig' => $this->getSig(),
            'user' => [
                'login_id' => 'testadmintest@localhost',
                'password' => 'testadmintest@localhost',
            ]
        ]);
        $this->assertEquals('/admin/', $r->redirectUrl);
        $this->assertEquals(302, $r->statusCode);
        $this->assertEquals('testadmintest@localhost', $this->clientTraitSession['login_id']);
    }
}
