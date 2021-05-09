<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Blogs;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogsController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
    use ClientTrait;

    public function testIndex(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/blogs/index");
        $this->assertInstanceOf(BlogsController::class, $c);
        $this->assertEquals("index", $c->getResolvedMethod());
    }

    public function testChangeBlog(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $this->assertEquals('testblog2', $this->clientTraitSession['blog_id']);

        $r = $this->reqGetBeRedirect("/admin/blogs/choice", ['blog_id' => 'testblog1']);
        $this->assertEquals('/admin/', $r->redirectUrl);

        $this->assertEquals('testblog1', $this->clientTraitSession['blog_id']);
    }

    public function testCreateForm(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/blogs/create");
        $this->assertInstanceOf(BlogsController::class, $c);
        $this->assertEquals("create", $c->getResolvedMethod());
    }

    public function testCreate(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();
        $sig = $this->getSig();

        $title = "testblog" . time();

        $request_params = [
            'blog' => [
                'id' => $title,
                'name' => $title . '_name',
                'nickname' => $title . '_nick',
            ],
            'sig' => $sig
        ];

        $r = $this->reqPostBeRedirect("/admin/blogs/create", $request_params);
        $this->assertEquals('/admin/blogs/index', $r->redirectUrl);

        $c = $this->reqGet('/admin/blogs/index');
        $this->assertStringContainsString($title . '_name', $c->getOutput());
//    var_export($c->getData());
        $blogs = $c->getData()['blogs'];
        $is_found = false;
        foreach ($blogs as $blog) {
            if ($blog['id'] === $title) {
                $is_found = true;
                break;
            }
        }
        $this->assertTrue($is_found);
    }
}
