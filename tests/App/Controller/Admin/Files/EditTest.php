<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Files;

use Exception;
use Fc2blog\Config;
use Fc2blog\Model\FilesModel;
use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\FilesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class EditTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    DBHelper::clearDbAndInsertFixture();
    Config::set("DEBUG", false);
    parent::setUp();
  }

  public function testForm(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    $ut = new UploadTest();
    $ut->uploadFile();
    $fm = new FilesModel();
    $files = $fm->find('all');
//    var_dump($files);

    $c = $this->reqGet("/admin/files/edit", ['id' => $files[0]['id']]);
    $this->assertInstanceOf(FilesController::class, $c);
    $this->assertEquals('edit', $c->getResolvedMethod());

//    var_dump($c->getData()['file']);

    $this->assertEquals($files[0]['id'], $c->getData()['file']['id']);
    // TODO 増やすと良い
  }

  public function testUpdateFile(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    // get sig(CSRF Token) and entries
    // admin/files/uploadはガワの部分（アップロードフォームまで）
    $c = $this->reqGet("/admin/files/upload");
    $this->assertInstanceOf(FilesController::class, $c);
    $sig = $this->clientTraitSession['sig'];

    $ut = new UploadTest();
    $ut->uploadFile();

    $fm = new FilesModel();
    $files = $fm->find('all');
    $before_count = count($files);

    try {
      $orig_file_path = realpath(__DIR__ . "/../../../../test_images/" . random_int(0, 9) . ".png");
    } catch (Exception $e) {
      throw new RuntimeException("failed random_int");
    }
    $tmp_file = __DIR__ . "/../../../../test_images/_temp_img.png";
    copy($orig_file_path, $tmp_file);
    $tmp_file = realpath(__DIR__ . "/../../../../test_images/_temp_img.png");

    $request_file = [
      'file' => [
        "name" => ['file' => pathinfo($tmp_file, PATHINFO_BASENAME)],
        "type" => ['file' => "image/png"],
        "size" => ['file' => filesize($tmp_file)],
        "tmp_name" => ['file' => $tmp_file],
        "error" => ['file' => UPLOAD_ERR_OK],
      ]
    ];
    $filename = "test" . microtime(true) . ".png";
    $request_data = [
      'id' => $files[0]['id'],
      'file' => [
        "name" => $filename,
      ],
      'sig' => $sig,
      'MAX_FILE_SIZE' => "5242880"
    ];

    $r = $this->reqPostFileBeRedirect("/admin/files/edit", $request_data, $request_file);

    $this->assertEquals('/admin/files/upload', $r->redirectUrl);

    $files = $fm->find('all');
    $this->assertCount($before_count, $files);

    $file = $fm->findByIdAndBlogId($files[0]['id'], "testblog2");

//    var_dump($file);

    $this->assertEquals($filename, $file['name']);
  }
}
