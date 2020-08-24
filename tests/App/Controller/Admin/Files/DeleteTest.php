<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Files;

use Fc2blog\Config;
use Fc2blog\Model\FilesModel;
use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\FilesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class DeleteTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Config::set("DEBUG", false);
    parent::setUp();
  }

  public function testDelete(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $ut = new UploadTest();
    $ut->uploadFile();
    $ut->uploadFile();
    $ut->uploadFile();

    // admin/files/uploadはガワの部分（アップロードフォームまで）
    $c = $this->reqGet("/admin/files/upload");
    $this->assertInstanceOf(FilesController::class, $c);

    $fm = new FilesModel();
    $before_files = $fm->find('all');

    $sig = $this->getSig();

    $delete_file_id = $before_files[0]['id'];
    $r = $this->reqGetBeRedirect("/admin/files/delete", ['id' => $delete_file_id, 'sig' => $sig]);

    $this->assertEquals('/admin/files/upload', $r->redirectUrl);

    $after_files = $fm->find('all');

    $this->assertCount(count($before_files) - 1, $after_files);

    $deleted_file = $fm->findByIdAndBlogId($delete_file_id, 'testblog2');

    $this->assertEmpty($deleted_file);
  }
}
