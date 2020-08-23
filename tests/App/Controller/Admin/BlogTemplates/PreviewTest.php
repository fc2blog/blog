<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogTemplates;

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\User\EntriesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class PreviewTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    parent::setUp();
  }

  public function testPreview(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $c = $this->reqGet("/testblog2/index.php?mode=entries&process=preview&template_id=1&pc=1");
//    var_dump($c);
    $this->assertInstanceOf(EntriesController::class, $c);
    $this->assertEquals('preview', $c->getResolvedMethod());
  }
}
