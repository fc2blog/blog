<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Entries;

use Fc2blog\Config;
use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Config::set("DEBUG", false);
    parent::setUp();
  }

  public function testDelete(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/entries/index");
    $some_entry = $c->get('entries')[0];
    $entry_count = count($c->get('entries'));

    $sig = $this->getSig();

    $r = $this->reqGetBeRedirect("/admin/entries/delete", ["id" => $some_entry['id'], "sig" => $sig]);

    $this->assertEquals("/admin/entries/index", $r->redirectUrl);

    $c = $this->reqGet("/admin/entries/index");
    $this->assertCount($entry_count - 1, $c->get('entries'));
  }

  public function testMultiDelete(): void
  {
    # 複数削除テストなので、一回３件に戻す
    DBHelper::clearDbAndInsertFixture();

    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/entries/index");
    $entries = $c->get('entries');
    $entry_id_list = [];
    foreach ($entries as $entry) {
      $entry_id_list[] = $entry['id'];
    }
    $this->assertCount(3, $entries);
    $sig = $this->getSig();

    $request_data = [
      "id" => $entry_id_list,
      'mode' => "entries",
      'process' => 'delete',
      'sig' => $sig
    ];

    $r = $this->reqGetBeRedirect("/admin/entries/delete", $request_data);
    $this->assertEquals("/admin/entries/index", $r->redirectUrl);

    $c = $this->reqGet("/admin/entries/index");
    $entries = $c->get('entries');

    $this->assertCount(0, $entries);
  }
}
