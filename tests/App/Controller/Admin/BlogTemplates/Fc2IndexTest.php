<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogTemplates;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogTemplatesController;
use Fc2blog\Web\Controller\User\EntriesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class Fc2IndexTest extends TestCase
{
    use ClientTrait;

    public function setUp(): void
    {
        DBHelper::clearDbAndInsertFixture();
        parent::setUp();
    }

    public function testIndexDeviceType1(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/blog_templates/fc2_index", ['device_type' => 1/*pc*/]);
        $this->assertInstanceOf(BlogTemplatesController::class, $c);
        $this->assertEquals('fc2_index', $c->getResolvedMethod());

        $d = $c->getData();
//    var_export($d);

        $this->assertIsArray($d['templates']);
        $this->assertGreaterThan(1, count($d['templates']));
        $this->assertArrayHasKey('id', $d['templates'][0]);
        $this->assertArrayHasKey('name', $d['templates'][0]);
        $this->assertArrayHasKey('discription'/*not typo*/, $d['templates'][0]);
        $this->assertArrayHasKey('type', $d['templates'][0]);
        $this->assertArrayHasKey('image', $d['templates'][0]);

        $this->assertIsArray($d['paging']);
        $this->assertArrayHasKey('count', $d['paging']);
        $this->assertGreaterThan(1, $d['paging']['count']);
        $this->assertArrayHasKey('max_page', $d['paging']);
        $this->assertArrayHasKey('page', $d['paging']);
        $this->assertArrayHasKey('is_next', $d['paging']);
        $this->assertTrue($d['paging']['is_next']);
        $this->assertFalse($d['paging']['is_prev']);
    }

    public function testIndexDeviceType4(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/blog_templates/fc2_index", ['device_type' => 4/*sp*/]);
        $this->assertInstanceOf(BlogTemplatesController::class, $c);
        $this->assertEquals('fc2_index', $c->getResolvedMethod());

        $d = $c->getData();
//    var_export($d);

        $this->assertIsArray($d['templates']);
        $this->assertGreaterThan(1, count($d['templates']));
        $this->assertArrayHasKey('id', $d['templates'][0]);
        $this->assertArrayHasKey('name', $d['templates'][0]);
        $this->assertArrayHasKey('discription'/*not typo*/, $d['templates'][0]);
        $this->assertArrayHasKey('type', $d['templates'][0]);
        $this->assertArrayHasKey('image', $d['templates'][0]);

        $this->assertIsArray($d['paging']);
        $this->assertArrayHasKey('count', $d['paging']);
        $this->assertGreaterThan(1, $d['paging']['count']);
        $this->assertArrayHasKey('max_page', $d['paging']);
        $this->assertArrayHasKey('page', $d['paging']);
        $this->assertArrayHasKey('is_next', $d['paging']);
        $this->assertTrue($d['paging']['is_next']);
        $this->assertFalse($d['paging']['is_prev']);
    }

    public function testPreview(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/blog_templates/fc2_index", ['device_type' => 1/*pc*/]);
        $this->assertInstanceOf(BlogTemplatesController::class, $c);
        $this->assertEquals('fc2_index', $c->getResolvedMethod());

        $d = $c->getData();
        $templates_id = $d['templates'][0]['id'];

        $c = $this->reqGet("/testblog2/index.php", [
            'mode' => 'entries',
            'process' => 'preview',
            'fc2_id' => $templates_id,
            'device_type' => 1
        ]);
//    var_dump($c);
        $this->assertInstanceOf(EntriesController::class, $c);
        $this->assertEquals('preview', $c->getResolvedMethod());
    }

    public function testDownload(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/blog_templates/fc2_index", ['device_type' => 1/*pc*/]);
        $this->assertInstanceOf(BlogTemplatesController::class, $c);
        $this->assertEquals('fc2_index', $c->getResolvedMethod());

        $d = $c->getData();
        $templates_id = $d['templates'][0]['id'];

        $sig = $this->getSig();
        $c = $this->reqGet("/admin/blog_templates/create", [
            'fc2_id' => $templates_id,
            'device_type' => 1,
            'sig' => $sig
        ]);

        $this->assertInstanceOf(BlogTemplatesController::class, $c);
        $this->assertEquals('create', $c->getResolvedMethod());

        // 新規と同じなので省略したが、 保存までやる？
    }

}
