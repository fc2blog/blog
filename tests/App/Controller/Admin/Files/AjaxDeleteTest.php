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

class AjaxDeleteTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Config::set("DEBUG", false);
    parent::setUp();
  }

  public function testAjaxMultiDelete(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $ut = new UploadTest();
    $ut->uploadFile();
    $ut->uploadFile();
    $ut->uploadFile();

    // get sig(CSRF Token) and entries
    // admin/files/uploadはガワの部分（アップロードフォームまで）
    $c = $this->reqGet("/admin/files/upload");
    $this->assertInstanceOf(FilesController::class, $c);
    $sig = $this->clientTraitSession['sig'];

    $fm = new FilesModel();
    $before_files = $fm->find('all');
//    var_dump($before_files);
    $before_files_count = count($before_files);

    $delete_file_id_list = [];
    foreach($before_files as $before_file){
      $delete_file_id_list[] = $before_file['id'];
    }
    $will_be_delete_files_count = count($delete_file_id_list);

    $c = $this->reqPost("/admin/files/ajax_delete", ['id' => $delete_file_id_list, 'sig' => $sig]);

    $this->assertEquals(0/*success*/, $c->get('json')['status']);

//    var_dump($c);

    $after_files = $fm->find('all');
//    var_dump($after_files);
    $this->assertCount($before_files_count - $will_be_delete_files_count, $after_files);
  }
}
