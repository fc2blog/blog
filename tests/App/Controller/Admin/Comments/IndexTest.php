<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Comments;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
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

    public function testEmptyNoticeIndex(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1"]);

        $this->assertCount(0, $c->get('comments'));
        $this->assertEquals(0, $c->get('paging')['count']);
        $this->assertEquals(0, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']); // page は 0-originである。つまり0=1ページ目
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);
    }

    public function test1CommentNoticeIndex(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleComment();
        $generator->removeAllComments('testblog2', 1);
        $generator->generateSampleComment('testblog2', 1, 1);

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1"]);
//    var_dump($c);
//    var_dump($c->get('comments'));

        $this->assertCount(1, $c->get('comments'));
        $this->assertEquals(1, $c->get('paging')['count']);
        $this->assertEquals(1, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);
    }

    public function test100CommentsNoticeIndexPagingAndLimit(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleComment();
        $generator->removeAllComments('testblog2', 1);
        $generator->generateSampleComment('testblog2', 1, 100);

        // == ページングテスト
        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1"]);
//    var_dump($c->get('paging'));
        $this->assertCount(20, $c->get('comments'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(true, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", "page" => "1"]);
//    var_dump($c->get('paging'));
        $this->assertCount(20, $c->get('comments'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(1, $c->get('paging')['page']);
        $this->assertEquals(true, $c->get('paging')['is_next']);
        $this->assertEquals(true, $c->get('paging')['is_prev']);

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", "page" => "4"]);
//    var_dump($c->get('paging'));
        $this->assertCount(20, $c->get('comments'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(4, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(true, $c->get('paging')['is_prev']);

        // TODO ありえないページ数をひらけてしまうのはよいのか悪いのか微妙
        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", "page" => "5"]);
//    var_dump($c->get('paging'));
        $this->assertCount(0, $c->get('comments'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(5, $c->get('paging')['max_page']);
        $this->assertEquals(5, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(true, $c->get('paging')['is_prev']);

        // == １ページ件数設定テスト

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", "limit" => "1"]);
//    var_dump($c->get('paging'));
        $this->assertCount(1, $c->get('comments'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(100, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(true, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", "limit" => "100"]);
//    var_dump($c->get('paging'));
        $this->assertCount(100, $c->get('comments'));
        $this->assertEquals(100, $c->get('paging')['count']);
        $this->assertEquals(1, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);

    }

    public function testNoticeIndexEntryId(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleComment();
        $generator->removeAllComments('testblog2', 1);
        $generator->generateSampleComment('testblog2', 1, 5);
        $generator->removeAllComments('testblog2', 2);
        $generator->generateSampleComment('testblog2', 2, 5);

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1"]);
//    var_dump($c->get('paging'));
        $this->assertCount(10, $c->get('comments'));
        $this->assertEquals(10, $c->get('paging')['count']);
        $this->assertEquals(1, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);


        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", 'entry_id' => 1]);
//    var_dump($c->get('paging'));
        $this->assertCount(5, $c->get('comments'));
        $this->assertEquals(5, $c->get('paging')['count']);
        $this->assertEquals(1, $c->get('paging')['max_page']);
        $this->assertEquals(0, $c->get('paging')['page']);
        $this->assertEquals(false, $c->get('paging')['is_next']);
        $this->assertEquals(false, $c->get('paging')['is_prev']);
    }

    public function testNoticeIndexOpenStatus(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleComment();
        $generator->removeAllComments('testblog2', 1);
        $generator->generateSampleComment('testblog2', 1, 20);
        $generator->removeAllComments('testblog2', 2); // 冪等のため

        // とりあえず、数が違えばなんらかフィルタがきいているであろう
        // もっとしっかり数える方が良いだろうが、それは問題が起きてからで。
        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1"]);
        $this->assertEquals(20, $c->get('paging')['count']);
        $count_all_status = $c->get('paging')['count'];

        // TODO このあたり確率でコケる
        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", 'open_status' => 0]);
        $this->assertNotEquals($count_all_status, $c->get('paging')['count']);

        // TODO このあたり確率でコケる
        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", 'open_status' => 1]);
        $this->assertNotEquals($count_all_status, $c->get('paging')['count']);

        // TODO このあたり確率でコケる
        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", 'open_status' => 2]);
        $this->assertNotEquals($count_all_status, $c->get('paging')['count']);

    }

    public function testNoticeIndexOrder(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleComment();
        $generator->removeAllComments('testblog2', 1);
        $generator->generateSampleComment('testblog2', 1, 20, true);
        $generator->removeAllComments('testblog2', 2); // 冪等のため

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1"]);
        $this->assertEquals(20, $c->get('paging')['count']);

        $first_comment = $c->get('comments')[0];
        $last_comment = $c->get('comments')[19];

        // created_at_asc は隠しコマンドぽい
        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", 'order' => "created_at_asc"]);
        $this->assertEquals(20, $c->get('paging')['count']);
        $this->assertEquals($c->get('comments')[0]['id'], $last_comment['id']);
        $this->assertEquals($c->get('comments')[19]['id'], $first_comment['id']);

        // name_asc とりあえず、先頭と終端が違うなら変わっているだろう…。
        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1", 'order' => "name_asc"]);
        $this->assertEquals(20, $c->get('paging')['count']);
        $this->assertNotEquals($c->get('comments')[0]['id'], $last_comment['id']);

        if ($c->get('comments')[19]['id'] == $first_comment['id']) { // 確立で失敗する調査用
            var_dump([$c->get('comments')[19]['name'], $first_comment['name']]);
        }
        $this->assertNotEquals($c->get('comments')[19]['id'], $first_comment['id']); // 確立で失敗することがある

        // TODO other patterns created_at_desc,name_asc,body_asc
    }

    public function testNoticeIndexKeyword(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $generator = new GenerateSampleComment();
        $generator->removeAllComments('testblog2', 1);
        $generator->generateSampleComment('testblog2', 1, 5);
        $generator->removeAllComments('testblog2', 2); // 冪等のため

        $c = $this->reqGet("/admin/comments/index", ["reply_status" => "1"]);
        $this->assertEquals(5, $c->get('paging')['count']);
        $first_comment_body = $c->get('comments')[0]['body'];

        $c = $this->reqGet(
            "/admin/comments/index",
            ["reply_status" => "1", 'keyword' => mb_substr($first_comment_body, 10, 10, 'UTF-8')]
        );
        $this->assertGreaterThanOrEqual(1, $c->get('paging')['count']);
        if ($c->get('paging')['count'] == 5 || $c->get('paging')['count'] == 4) { // 確立で失敗する調査用
            var_dump($c->getData());
        }
        $this->assertLessThan(4, $c->get('paging')['count']); // 確立で失敗することがある
    }
}
