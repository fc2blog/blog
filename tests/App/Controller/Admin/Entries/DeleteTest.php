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

    // get sig(CSRF Token) and entries
    $c = $this->reqGet("/admin/entries/index");
    $sig = $this->clientTraitSession['sig'];
    $some_entry = $c->get('entries')[0];
    $entry_count = count($c->get('entries'));

    $r = $this->reqGetBeRedirect("/admin/entries/delete", ["id" => $some_entry['id'], "sig" => $sig]);

    $this->assertEquals("/admin/entries/index", $r->redirectUrl);

    // get sig(CSRF Token) and entries
    $c = $this->reqGet("/admin/entries/index");
    $this->assertCount($entry_count - 1, $c->get('entries'));
  }
}
