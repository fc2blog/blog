<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Blogs;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogsController;
use Fc2blog\Web\Controller\Admin\CommonController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
  use ClientTrait;

  public function testForm(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/blogs/delete");
    $this->assertInstanceOf(BlogsController::class, $c);
    $this->assertEquals("delete", $c->getResolvedMethod());
  }

  public function testDelete(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();
    $sig = $this->getSig();

    $request_data = [
      'blog' => [
        'delete' => '1',
      ],
      'sig' => $sig
    ];

    $r = $this->reqPostBeRedirect("/admin/blogs/delete", $request_data);
    $this->assertEquals('/admin/blogs/index', $r->redirectUrl);

    $c = $this->reqGet("/admin/blogs/index");
    $this->assertStringContainsString('testblog1', $c->getOutput());
    $this->assertStringNotContainsString('testblog2', $c->getOutput());
  }
}
