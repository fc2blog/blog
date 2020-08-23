<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Tags;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\TagsController;
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
    DBHelper::clearDbAndInsertFixture();

    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/tags/edit", ["id" => 1]);

    $this->assertInstanceOf(TagsController::class, $c);
    $this->assertEquals('edit', $c->getResolvedMethod());

    $d = $c->getData();
//    var_export($d);

    $this->assertArrayHasKey('tag', $d);
    $this->assertEquals('alphnum', $d['tag']['name']);
  }

  public function testUpdate(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();
    $sig = $this->getSig();

    $request_data = [
      'id' => "1",
      'sig' => $sig,
      'tag' => ['name' => "testtagname"]
    ];

    $r = $this->reqGetBeRedirect("/admin/tags/edit", $request_data);
    $this->assertEquals('/admin/tags/index', $r->redirectUrl);

    $c = $this->reqGet("/admin/tags/index");

    $data = $c->getData();
//    var_export($data);
    $this->assertEquals('testtagname', $data['tags'][0]['name']);
  }
}
