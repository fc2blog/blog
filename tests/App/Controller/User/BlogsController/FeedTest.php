<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\User\BlogsController;

use Fc2blog\Model\EntriesModel;
use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;
use SimplePie;
use SimplePie_Item;

class FeedTest extends TestCase
{
  use ClientTrait;

  public function testFeed(): void
  {
    $c = $this->reqGet('/testblog2/?xml');
    $this->assertStringContainsString('<?xml version=', $c->getOutput());
    $this->assertStringContainsString('<rss version="2.0">', $c->getOutput());

    // 実際にRSSとしてパースして確認
    $feed = new SimplePie();
    $feed->set_raw_data($c->getOutput());
    $feed->init();

    /** @var SimplePie_Item[] $items */
    $items = $feed->get_items();

    $this->assertCount(3, $items); // 壊れやすい

    // 比較用に記事一覧取得
    $entries_model = new EntriesModel();
    $entries = $entries_model->find('all', [
      'where' => 'blog_id=?',
      'params' => ["testblog2"],
      'limit' => 5
    ]);
    foreach($items as $i => $item){
      $this->assertEquals($entries[$i]['title'], $item->get_title());
      $this->assertEquals('testnick2', $item->get_author()->email); // emailだが、RSSの仕様がゆれているので
      $this->assertStringContainsString("blog-entry-{$entries[$i]['id']}.html", $item->get_link());
    }
  }

  /**
   * Private設定なブログは403が返される
   */
  public function testPrivateBlogFeed(): void
  {
    $c = $this->reqGet('/testblog3/?xml');
    $this->assertStringNotContainsString('<?xml version=', $c->getOutput());
    $this->assertEquals(403, $c->getStatusCode());
  }
}
