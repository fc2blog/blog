<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Tags;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleTag;
use Fc2blog\Web\Controller\Admin\TagsController;
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

        $c = $this->reqGet("/admin/tags/index");
        $this->assertInstanceOf(TagsController::class, $c);
        $this->assertEquals('index', $c->getResolvedMethod());

        $data = $c->getData();
//    var_export($data);

        $this->assertArrayHasKey("tags", $data);
        $this->assertEquals("alphnum", $data['tags'][0]['name']);

        $this->assertEquals(1, $data['paging']['count']);
    }

    public function testSearch(): void
    {
        DBHelper::clearDbAndInsertFixture();
        $generator = new GenerateSampleTag();
        $generator->generateSampleTagsToSpecifyEntry('testblog2', 0, 5);

        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/tags/index");
        $data = $c->getData();
        $this->assertEquals(6, $data['paging']['count']);

        $request_data = [
            'mode' => 'tags',
            'process' => 'index',
            'limit' => 20,
            'page' => 0,
            'order' => 'count_desc',
            'name' => 'alphnum'
        ];

        $c = $this->reqGet("/admin/tags/index", $request_data);

        $data = $c->getData();
//    var_export($data);

        $this->assertEquals(1, $data['paging']['count']);
        // TODO more complex search condition
    }

    public function testDelete(): void
    {
        DBHelper::clearDbAndInsertFixture();
        $generator = new GenerateSampleTag();
        $generator->generateSampleTagsToSpecifyEntry('testblog2', 0, 5);

        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/tags/index");
        $data = $c->getData();

        $tags = $data['tags'];
        $before_count = count($tags);
        shuffle($tags);
        $delete_tag_id = $tags[0]['id'];

        $sig = $this->getSig();

        $r = $this->reqGetBeRedirect('/admin/tags/delete', ['id' => $delete_tag_id, 'sig' => $sig]);
        $this->assertEquals('/admin/tags/index', $r->redirectUrl);

        $c = $this->reqGet("/admin/tags/index");
        $data = $c->getData();
        $tags = $data['tags'];
        $this->assertCount($before_count - 1, $data['tags']);
    }
}
