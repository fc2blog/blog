<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Entries;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleEntry;
use Fc2blog\Web\Controller\Admin\EntriesController;
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

        $c = $this->reqGet("/admin/entries/index");

        $this->assertInstanceOf(EntriesController::class, $c);
        $this->assertCount(3, $c->get('entries'));
        $this->assertIsArray($c->get('entries')[0]);

        $this->assertEquals(3, $c->get('paging')['count']);
        $this->assertEquals(1, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']); // page は 0-originである。つまり0=1ページ目
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);
    }

    public function test100ItemIndex(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleEntry();
        $generator->removeAllEntry('testblog2');
        $generator->generateSampleEntry('testblog2', 100);

        $c = $this->reqGet("/admin/entries/index");

        $this->assertInstanceOf(EntriesController::class, $c);
        $this->assertCount(20, $c->get('entries'));
        $this->assertIsArray($c->get('entries')[0]);

        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']); // page は 0-originである。つまり0=1ページ目
        $this->assertEquals(true, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);
    }

    public function testIndexPagingAndLimit(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleEntry();
        $generator->removeAllEntry('testblog2');
        $generator->generateSampleEntry('testblog2', 100);

        // == ページングテスト
        $c = $this->reqGet("/admin/entries/index", []);
//    var_dump($c->get('paging'));
        $this->assertCount(20, $c->get('entries'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(true, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);

        $c = $this->reqGet("/admin/entries/index", ["page" => "1"]);
//    var_dump($c->get('paging'));
        $this->assertCount(20, $c->get('entries'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(1, $c->get('paging')['page']);
        $this->assertEquals(true, $c->get('paging')['is_next']);
        $this->assertEquals(true, $c->get('paging')['is_prev']);

        $c = $this->reqGet("/admin/entries/index", ["page" => "4"]);
//    var_dump($c->get('paging'));
        $this->assertCount(20, $c->get('entries'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(4, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(true, $c->get('paging')['is_prev']);

        // TODO ありえないページ数をひらけてしまうのはよいのか悪いのか微妙
        $c = $this->reqGet("/admin/entries/index", ["page" => "5"]);
//    var_dump($c->get('paging'));
        $this->assertCount(0, $c->get('entries'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(5, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(true, $c->get('paging')['is_prev']);

        // == １ページ件数設定テスト

        $c = $this->reqGet("/admin/entries/index", ["limit" => "1"]);
//    var_dump($c->get('paging'));
        $this->assertCount(1, $c->get('entries'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(100, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(true, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);

        $c = $this->reqGet("/admin/entries/index", ["limit" => "100"]);
//    var_dump($c->get('paging'));
        $this->assertCount(100, $c->get('entries'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(1, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);

    }

    public function testIndexOrder(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleEntry();
        $generator->removeAllEntry('testblog2');
        $generator->generateSampleEntry('testblog2', 20);

        $c = $this->reqGet("/admin/entries/index", ['order' => "posted_at_desc"]);
        $this->assertEquals(20, $c->get('paging')['count']);

//    var_dump($c->get('entries'));

        $first_comment = $c->get('entries')[0];
        $last_comment = $c->get('entries')[19];

        // created_at_asc は隠しコマンドぽい
        $c = $this->reqGet("/admin/entries/index", ['order' => "posted_at_asc"]);
        $this->assertEquals(20, $c->get('paging')['count']);
        $this->assertEquals($c->get('entries')[0]['id'], $last_comment['id']);
        $this->assertEquals($c->get('entries')[19]['id'], $first_comment['id']);

        // name_asc とりあえず、違うなら変わっているだろう…。
        // TODO: 確率によって成功してしまう可能性がある…
        $c = $this->reqGet("/admin/entries/index", ['order' => "name_asc"]);
        $this->assertEquals(20, $c->get('paging')['count']);
        $this->assertNotEquals($c->get('entries')[0]['id'], $last_comment['id']);
        $this->assertNotEquals($c->get('entries')[19]['id'], $first_comment['id']);

        // TODO other patterns created_at_desc,name_asc,body_asc
    }

    public function testIndexKeyword(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleEntry();
        // 全エントリ削除
        $generator->removeAllEntry('testblog2');
        // 全エントリ生成しなおし
        $generator->generateSampleEntry('testblog2', 5);

        // 5件あるか取得
        $c = $this->reqGet("/admin/entries/index", []);
        $this->assertEquals(5, $c->get('paging')['count']);
        $first_comment_body = $c->get('entries')[0]['body'];

        $c = $this->reqGet("/admin/entries/index", ['keyword' => mb_substr($first_comment_body, 10, 10, 'UTF-8')]);
        // 1件以上あればOKとする（重複の可能性があるので）
        $this->assertGreaterThanOrEqual(1, $c->get('paging')['count']);
    }

    public function testCountCommentNum(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleEntry();
        $generator->removeAllEntry('testblog2');
        $entries = $generator->generateSampleEntry('testblog2', 1);

        $comment_generator = new GenerateSampleComment();
        $comment_generator->generateSampleComment('testblog2', $entries[0]['id'], 5);

        $c = $this->reqGet("/admin/entries/index", []);
//    var_dump($first_comment_body = $c->get('entries')[0]);
        $this->assertEquals(5, $c->get('entries')[0]['comment_count']);
        // TODO 直近「HTML」までは見なくても良いと思われるが（十分に気づくので）
        // 見るとしたら、Symfony/DomCrawlerかなと思われる
    }

    // TODO 各種検索機能の拡充
}
