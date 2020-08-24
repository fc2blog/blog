<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Blogs;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogsController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class EditTest extends TestCase
{
  use ClientTrait;

  public function testInfoForm(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/blogs/edit");
    $this->assertInstanceOf(BlogsController::class, $c);
    $this->assertEquals("edit", $c->getResolvedMethod());

    $d = $c->getRequest()->get('blog');
    var_export($d);

    $this->assertEquals("testblog2", $d['name']);
    $this->assertEquals("", $d['introduction']);
    // todo チェック項目を増やしても良い
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
      'blog' => [
        'name' => 'testblog2',
        'introduction' => 'blogdescription', // update
        'nickname' => 'testnick2',
        'open_status' => '0',
        'blog_password' => '',
        'timezone' => 'Asia/Tokyo',
        'ssl_enable' => '0',
        'redirect_status_code' => '301'
      ],
      'sig' => $sig
    ];

    $r = $this->reqPostBeRedirect("/admin/blogs/edit", $request_data);
    $this->assertEquals('/admin/blogs/edit', $r->redirectUrl);

    $c = $this->reqGet("/admin/blogs/edit");
    $d = $c->getRequest()->get('blog');
    $this->assertEquals("blogdescription", $d['introduction']);
    // todo チェック項目を増やしても良い
  }
}
