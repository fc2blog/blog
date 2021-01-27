<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Blogs;

use Fc2blog\Exception\RedirectExit;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\BlogsController;
use PHPUnit\Framework\TestCase;

class CreateTest extends TestCase
{
  use ClientTrait;

  public function testCreateNewBlogForm(): void
  {
    $this->mergeAdminSession();
    $this->assertEquals("testblog2", $this->clientTraitSession['blog_id']);

    $c = $this->reqGet("/admin/blogs/create");
    $this->assertInstanceOf(BlogsController::class, $c);
    $this->assertEquals('create', $c->getResolvedMethod());

    $this->assertStringContainsString('<h2>ブログの新規登録</h2>', $c->getOutput());

    $blog_suffix = time();
    $sig = $this->getSig();

    try {
      $c = $this->reqPost(
        "/admin/blogs/create",
        [
          "blog" => [
            "id" => "id" . $blog_suffix,
            "name" => "name" . $blog_suffix,
            "nickname" => "nickname" . $blog_suffix
          ],
          "sig" => $sig
        ]
      );
      $this->assertStringNotContainsString("入力エラーがあります", $c->getOutput());
      $this->fail();
    } catch (RedirectExit $e) {
      // ok
    }

    $blog_model = new BlogsModel();
    $blog = $blog_model->findById("id" . $blog_suffix);
//    var_dump($blog);

    $this->assertEquals("id" . $blog_suffix, $blog['id']);
    $this->assertEquals("name" . $blog_suffix, $blog['name']);
    $this->assertEquals("nickname" . $blog_suffix, $blog['nickname']);
    $this->assertGreaterThan(160, strlen($blog['trip_salt']));
  }
}
