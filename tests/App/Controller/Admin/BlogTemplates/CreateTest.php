<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogTemplates;

use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogTemplatesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
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

    $c = $this->reqGet("/admin/blog_templates/create", ['device_type' => 1]);
    $this->assertInstanceOf(BlogTemplatesController::class, $c);
    $this->assertEquals('create', $c->getResolvedMethod());

    $d = $c->getRequest()->getData();
//    var_export($d);

    $this->assertEquals('1', $d['device_type']);
    $this->assertEquals('1', $d['blog_template']['device_type']);
  }

  public function testCreate(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();
    $sig = $this->getSig();

    $request_data = [
      'sig' => $sig,
      'blog_template' => [
        'device_type'=>"1",
        'title' => "test template title",
        'html' => "<html lang='ja'><body>test template html</body></html>",
        'css' => "/*test template css*/",
      ]
    ];

    $r = $this->reqPostBeRedirect('/admin/blog_templates/create', $request_data);
    $this->assertEquals('/admin/blog_templates/index', $r->redirectUrl);

    // check
    $c = $this->reqGet("/admin/blog_templates/index");
//    var_export($c);

    $d = $c->getData();
//    var_export($d);
    $is_found = false;
    $device_blog_templates = $d['device_blog_templates'];
    foreach($device_blog_templates[1]/*PC*/ as $device_blog_template){
//      var_export($device_blog_template);
      if($device_blog_template['title'] === "test template title"){
        $is_found=true;
        $tm = new BlogTemplatesModel();
        $template = $tm->findByIdAndBlogId($device_blog_template['id'], 'testblog2');
//        var_dump($template);
        $this->assertEquals("<html lang='ja'><body>test template html</body></html>", $template['html']);
        $this->assertEquals("/*test template css*/", $template['css']);
      }
    }
    $this->assertTrue($is_found);
  }

  // TODO create 後に自動適用されるのをチェックなど？
}
