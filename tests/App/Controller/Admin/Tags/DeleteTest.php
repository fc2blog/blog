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

class DeleteTest extends TestCase
{
    use ClientTrait;

    public function setUp(): void
    {
        DBHelper::clearDbAndInsertFixture();
        parent::setUp();
    }

    public function testMultiDelete(): void
    {
        $generator = new GenerateSampleTag();
        $generator->generateSampleTagsToSpecifyEntry('testblog2', 0, 5);

        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/tags/index");
        $this->assertInstanceOf(TagsController::class, $c);
        $this->assertEquals('index', $c->getResolvedMethod());

        $data = $c->getData();
//    var_export($data);

        $tags = $data['tags'];

        $before_count = count($tags);

        $delete_ids = [];
        foreach ($tags as $tag) {
            if ($tag['id'] === 1) continue;
            $delete_ids[] = $tag['id'];
        }

        $sig = $this->getSig();

        $request_data = [
            'id' => $delete_ids,
            'sig' => $sig,
            'process' => 'delete',
            'mode' => 'tags',
        ];

        $r = $this->reqPostBeRedirect("/admin/tags/delete", $request_data);
        $this->assertEquals('/admin/tags/index', $r->redirectUrl);

        $c = $this->reqGet("/admin/tags/index");

        $data = $c->getData();
//    var_export($data);
        $tags = $data['tags'];
        $this->assertNotCount($before_count, $tags);
        $this->assertCount(1, $tags);
    }
}
