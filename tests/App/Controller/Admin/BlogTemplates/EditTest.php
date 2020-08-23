<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogTemplates;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogTemplatesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class EditTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    parent::setUp();
  }

  public function testForm(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/blog_templates/edit", ['id' => 1]);
    $this->assertInstanceOf(BlogTemplatesController::class, $c);
    $this->assertEquals('edit', $c->getResolvedMethod());

    $d = $c->getRequest()->getData();
//    var_export($d);

    $this->assertArrayHasKey('blog_template', $d);
    $this->assertEquals(1, $d['blog_template']['id']);
    $this->assertArrayHasKey('css', $d['blog_template']);
    $this->assertArrayHasKey('html', $d['blog_template']);

  }

  public function testUpdate(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();
    $sig = $this->getSig();

    $request_data = [
      'id' => 1,
      'sig' => $sig,
      'blog_template' => [
        'title' => "初期テンプレート",
        'html' => "<html lang='ja'><body>hello</body></html>",
        'css' => "/* test */",
      ]
    ];

    $r = $this->reqPostBeRedirect('/admin/blog_templates/edit', $request_data);
    $this->assertEquals('/admin/blog_templates/index', $r->redirectUrl);

    // check
    $c = $this->reqGet("/admin/blog_templates/edit", ['id' => 1]);
    $this->assertInstanceOf(BlogTemplatesController::class, $c);
    $this->assertEquals('edit', $c->getResolvedMethod());

    $d = $c->getRequest()->getData();
//    var_export($d);

    $this->assertEquals("初期テンプレート", $d['blog_template']['title']);
    $this->assertEquals("<html lang='ja'><body>hello</body></html>", $d['blog_template']['html']);
    $this->assertEquals("/* test */", $d['blog_template']['css']);
  }

  // TODO 更新するテンプレートをランダムにするなどもやったほうが良い
}
