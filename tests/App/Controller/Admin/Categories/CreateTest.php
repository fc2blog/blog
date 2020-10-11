<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Categories;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleCategory;
use Fc2blog\Web\Controller\Admin\CategoriesController;
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

  public function testIndex(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/categories/create");
    $this->assertInstanceOf(CategoriesController::class, $c);
    $this->assertEquals('create', $c->getResolvedMethod());

    $data = $c->getData();
//    var_export($data);

    $this->assertArrayHasKey('category_parents', $data);
    $this->assertEquals('', $data['category_parents'][0]);
    $this->assertEquals('未分類', $data['category_parents'][1]["value"]);
    $this->assertEquals(1, $data['category_parents'][1]["level"]);
    $this->assertEquals(true, $data['category_parents'][1]["disabled"]);
  }

  public function testCreate(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();
    $sig = $this->getSig();

    $test_category_name = "test_".microtime(true);
    $request_data = [
      'sig' => $sig,
      'category' => [
        'parent_id' => "0",
        'name' => "$test_category_name",
        'category_order' => "0"
      ]
    ];

    $r = $this->reqPostBeRedirect("/admin/categories/create", $request_data);
    $this->assertEquals("/admin/categories/create", $r->redirectUrl);

    $c = $this->reqGet("/admin/categories/create");
    $data = $c->getData();
//    var_export($data);
//    var_export($data['category_parents']);

    $is_found = false;
    $found_index = false;
    foreach($data['category_parents'] as $idx => $row){
      if(!is_array($row)) continue;
      if($row['value']===$test_category_name){
        $is_found=true;
        $found_index=$idx;
//        var_export($row);
      }
    }

    $this->assertTrue($is_found);
    $this->assertEquals(1, $data['category_parents'][$found_index]['level']);
    $this->assertArrayNotHasKey('disabled', $data['category_parents'][$found_index]);
  }

  public function testDelete(): void
  {
    $generator = new GenerateSampleCategory();
    /** @noinspection PhpUnhandledExceptionInspection */
    $list = $generator->generateSampleCategories('testblog2', "0", 5);

    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/categories/create");
    $data = $c->getData();
    $old_count = count($data['category_parents']);
//    echo $old_count;

    $sig = $this->getSig();
    $request_data = [
      'sig' => $sig,
      'ig' => $list[count($list) - 1]['id'],
    ];

    $r = $this->reqPostBeRedirect("/admin/categories/delete", $request_data);
    $this->assertEquals("/admin/categories/create", $r->redirectUrl);

    $c = $this->reqGet("/admin/categories/create");
    $data = $c->getData();
//    var_export($data);
    $this->assertCount($old_count, $data['category_parents']);
  }
}
