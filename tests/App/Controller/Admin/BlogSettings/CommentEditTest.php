<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogSettings;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogSettingsController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class CommentEditTest extends TestCase
{
    use ClientTrait;

    public function testForm(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/blog_settings/comment_edit");
        $this->assertInstanceOf(BlogSettingsController::class, $c);
        $this->assertEquals("comment_edit", $c->getResolvedMethod());

        $d = $c->getRequest()->getData()['blog_setting'];
//    var_export($d);

        $this->assertEquals(0, $d['comment_confirm']);
        $this->assertEquals(10, $d['comment_display_count']);
        // todo チェック項目増やすと良い
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
                'comment_confirm' => '1',
                'comment_display_approval' => '0',
                'comment_display_private' => '0',
                'comment_cookie_save' => '1',
                'comment_captcha' => '1',
                'comment_display_count' => '20',
                'comment_order' => '0',
                'comment_quote' => '0',
            ],
            'sig' => $sig
        ];

        $r = $this->reqPostBeRedirect("/admin/blog_settings/comment_edit", $request_data);
        $this->assertEquals('/admin/blog_settings/comment_edit', $r->redirectUrl);

        $c = $this->reqGet("/admin/blog_settings/comment_edit");
        $d = $c->getRequest()->get('blog_setting');

        $this->assertEquals(1, $d['comment_confirm']);
        $this->assertEquals(20, $d['comment_display_count']);
        // todo チェック項目増やすと良い
    }
}
