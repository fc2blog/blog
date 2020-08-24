<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Blogs;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogsController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
  use ClientTrait;

  public function testIndex(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/blogs/index");
    $this->assertInstanceOf(BlogsController::class, $c);
    $this->assertEquals("index", $c->getResolvedMethod());
  }

  public function testChangeBlog(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $this->assertEquals('testblog2', $this->clientTraitSession['blog_id']);

    $r = $this->reqGetBeRedirect("/admin/blogs/choice", ['blog_id' => 'testblog1']);
    $this->assertEquals('/admin/', $r->redirectUrl);

    $this->assertEquals('testblog1', $this->clientTraitSession['blog_id']);
  }
}
