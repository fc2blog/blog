<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Blogs;

use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogsController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class ChoiceTest extends TestCase
{
    use ClientTrait;

    public function testDefaultSelectedBlog(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        // Ja表記を確認
        $c = $this->reqGet("/admin/common/notice");
        $this->assertStringContainsString("testblog2", $c->getOutput());
        $this->assertEquals("testblog2", $this->clientTraitSession['blog_id']);
    }

    public function testChangeSelectedBlog(): void
    {
        $this->mergeAdminSession();
        $this->assertEquals("testblog2", $this->clientTraitSession['blog_id']);

        $r = $this->reqGetBeRedirect("/admin/blogs/choice", ['blog_id' => 'testblog1']);
        $this->assertEquals('/admin/', $r->redirectUrl);
        $this->assertEquals("testblog1", $this->clientTraitSession['blog_id']);
    }

    public function testCreateNewBlog(): void
    {
        $this->mergeAdminSession();
        $this->assertEquals("testblog2", $this->clientTraitSession['blog_id']);

        $c = $this->reqGet("/admin/blogs/create");
        $this->assertInstanceOf(BlogsController::class, $c);
        $this->assertEquals('create', $c->getResolvedMethod());
    }
}
