<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogSettings;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogSettingsController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class EtcEditTest extends TestCase
{
    use ClientTrait;

    public function testForm(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/blog_settings/etc_edit");
        $this->assertInstanceOf(BlogSettingsController::class, $c);
        $this->assertEquals("etc_edit", $c->getResolvedMethod());

        $d = $c->getRequest()->getData()['blog_setting'];
//    var_export($d);

        $this->assertEquals(0, $d['start_page']);

        $r = $this->reqGetBeRedirect("/admin/");
        $this->assertEquals("/admin/common/notice", $r->redirectUrl);
    }

    public function testUpdate(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();
        $sig = $this->getSig();

        $request_data = [
            'blog_setting' => [ // not "blog_settings", actual "blog_setting". take care!!
                'start_page' => '1',
            ],
            'sig' => $sig
        ];

        $r = $this->reqPostBeRedirect("/admin/blog_settings/etc_edit", $request_data);
        $this->assertEquals('/admin/blog_settings/etc_edit', $r->redirectUrl);

        $c = $this->reqGet("/admin/blog_settings/etc_edit");
        $d = $c->getRequest()->get('blog_setting');

        $this->assertEquals(1, $d['start_page']);

        $r = $this->reqGetBeRedirect("/admin/");
        $this->assertEquals("/admin/entries/create", $r->redirectUrl);
    }
}
