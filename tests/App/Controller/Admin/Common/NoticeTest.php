<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Common;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class NoticeTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    parent::setUp();
  }

  public function testCheckNoNewNotice(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/admin/common/notice");
    $this->assertEquals(0, $c->get("unread_count"));
    $this->assertEquals(0, $c->get("unapproved_count"));
  }

  public function testCheckNewNotice(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $generator = new GenerateSampleComment();
    $generator->generateSampleComment('testblog2', 1, 1);

    $c = $this->reqGet("/admin/common/notice");
    $this->assertEquals(1, $c->get("unread_count"));
    $this->assertEquals(0, $c->get("unapproved_count"));

    $generator->generateSampleComment('testblog2', 1, 5);
    $c = $this->reqGet("/admin/common/notice");
    $this->assertEquals(6, $c->get("unread_count"));
    $this->assertEquals(0, $c->get("unapproved_count"));
  }

  // TODO 未承認コメントカウント、コメント設定周りの設定テストを書いてからか
}
