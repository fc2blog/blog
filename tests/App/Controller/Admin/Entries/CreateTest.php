<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Entries;

use Fc2blog\Model\EntriesModel;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\EntriesController;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
  use ClientTrait;

  public function testForm(): void
  {
    $this->mergeAdminSession();

    // Ja表記を確認
    $c = $this->reqGet("/admin/entries/create");
    $this->assertInstanceOf(EntriesController::class, $c);
    $this->assertEquals("create", $c->getResolvedMethod());
    $this->assertStringContainsString("新しく記事を書く", $c->getOutput());
//    var_dump($this->clientTraitSession);
//    var_dump($this->clientTraitCookie);
  }

  public function testPost(): void
  {
    $this->mergeAdminSession();

    $em = new EntriesModel();
    $entries = $em->forTestGetAll('testblog2');
    $entries_count = count($entries);

    $sig = $this->getSig();

    $r = $this->reqPostBeRedirect("/admin/entries/create", [
      "sig" => $sig,
      "entry" => [ // 最低限の投稿
        "title" => "test",
        "body" => "body",
        "extend" => "",
        "posted_at" => "",
        "open_status" => "1",
        "password" => "",
        "auto_linefeed" => "1",
        "comment_accepted" => "1",
      ]
    ]);

    $this->assertEquals("/admin/entries/index", $r->redirectUrl);

    $entries = $em->forTestGetAll('testblog2');
    $this->assertCount($entries_count + 1, $entries);
//    var_dump($entries[count($entries)-1]);
    $latest_entry = $entries[count($entries) - 1];
    $this->assertEquals('test', $latest_entry['title']);
    $this->assertEquals('body', $latest_entry['body']);
  }

  // TODO 様々なパラメタをつかったテストの追加
}
