<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogSettings;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogSettingsController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class EntryEditTest extends TestCase
{
  use ClientTrait;

  public function testInfoForm(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/blog_settings/entry_edit");
    $this->assertInstanceOf(BlogSettingsController::class, $c);
    $this->assertEquals("entry_edit", $c->getResolvedMethod());

    $d = $c->getRequest()->get('blog_setting');
//    var_export($d);

    $this->assertEquals(5, $d['entry_display_count']);
    // todo チェック項目増やすと良い
  }

  public function testUpdateInfo(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();
    $sig = $this->getSig();

    $request_data = [
      'blog_setting' => [ // not "blog_settings", actual "blog_setting". take care!!
        'entry_recent_display_count' => '10',
        'entry_display_count' => '10',
        'entry_order' => '1',
        'entry_password' => ''
      ],
      'sig' => $sig
    ];

    $r = $this->reqPostBeRedirect("/admin/blog_settings/entry_edit", $request_data);
    $this->assertEquals('/admin/blog_settings/entry_edit', $r->redirectUrl);

    $c = $this->reqGet("/admin/blog_settings/entry_edit");
    $d = $c->getRequest()->get('blog_setting');

    $this->assertEquals(10, $d['entry_recent_display_count']);
    $this->assertEquals(10, $d['entry_display_count']);
    // todo チェック項目増やすと良い
  }
}
