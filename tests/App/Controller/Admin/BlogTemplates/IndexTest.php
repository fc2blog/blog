<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogTemplates;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogTemplatesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    parent::setUp();
  }

  public function testIndex(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/blog_templates/index");
    $this->assertInstanceOf(BlogTemplatesController::class, $c);
    $this->assertEquals('index', $c->getResolvedMethod());

    $d = $c->getData();
//    var_export($d);

    $this->assertCount(2, $d['template_ids']);
    $this->assertCount(2, $d['device_blog_templates']);
  }
}
