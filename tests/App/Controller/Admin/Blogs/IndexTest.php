<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Blogs;

use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\CommonController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
  use ClientTrait;

  public function testDefaultSelectedBlog(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/common/index");
    $this->assertInstanceOf(CommonController::class, $c);
    $this->assertEquals("index", $c->getResolvedMethod());
  }

  // TODO ブログを選択
}
