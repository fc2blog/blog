<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Entries;

use Fc2blog\Config;
use Fc2blog\Model\EntriesModel;
use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\EntriesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class EditTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Config::set("DEBUG", false);
    parent::setUp();
  }

  public function testEdit(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    // get sig(CSRF Token)
    $this->reqGet("/admin/entries/index");
    $sig = $this->clientTraitSession['sig'];

    $entry_id = 1;
    $c = $this->reqGet("/admin/entries/edit?id={$entry_id}");

    $this->assertInstanceOf(EntriesController::class, $c);
    $this->assertEquals('edit', $c->getResolvedMethod());
    $this->assertStringContainsString('記事の編集', $c->getOutput());

//    var_dump($this->clientTraitSession);
//    var_dump($c->getRequest()->getData());

    # 投稿データ組み立て
    $request_data = [
      "id" => $entry_id,
      "sig" => $sig,
      "entry" => [
        "title" => "AAA",
        "body" => "BBB",
        "extend" => "CCC",
        "posted_at" => date("Y-m-d H:i:s"),
        "open_status" => "1",
        "password" => "",
        "auto_linefeed" => "1",
        "comment_accepted" => "1",
      ],
      "entry_categories" => [
        "category_id" => [
          1
        ]
      ]
    ];

    $r = $this->reqPostBeRedirect("/admin/entries/edit", $request_data);

    $this->assertEquals("/admin/entries/index", $r->redirectUrl);

    $em = new EntriesModel();
    $updated_entry = $em->findByIdAndBlogId($entry_id, "testblog2");
//    var_dump($updated_entry);

    $this->assertEquals("AAA", $updated_entry['title']);
    $this->assertEquals("BBB", $updated_entry['body']);
    $this->assertEquals("CCC", $updated_entry['extend']);
    // TODO 増やし、カテゴリの更新などを確認する
  }
}
