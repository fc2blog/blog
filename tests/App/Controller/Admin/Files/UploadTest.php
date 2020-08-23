<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Files;

use Exception;
use Fc2blog\Config;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Controller\Admin\FilesController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class UploadTest extends TestCase
{
  use ClientTrait;

  public function setUp(): void
  {
    Config::set("DEBUG", false);
    parent::setUp();
  }

  public function testIndexAndUploadFile(): void
  {
    Session::destroy(new Request());
    $this->resetSession();
    $this->resetCookie();
    $this->mergeAdminSession();

    // get sig(CSRF Token) and entries
    // admin/files/uploadはガワの部分（アップロードフォームまで）
    $c = $this->reqGet("/admin/files/upload");

    $this->assertInstanceOf(FilesController::class, $c);

    // admin/files/ajax_indexは一覧の部分（「ファイル検索」を含んでそれ移行
    // なぜ分離されているんだろうか…？
    $c = $this->reqGet("/admin/files/ajax_index");
    $this->assertInstanceOf(FilesController::class, $c);
    $sig = $this->clientTraitSession['sig'];

    $before_files_count = count($c->get('files'));

    try {
      $orig_file_path = realpath(__DIR__ . "/../../../../test_images/" . random_int(0, 9) . ".png");
    } catch (Exception $e) {
      throw new RuntimeException("failed random_int");
    }

    $tmp_file = __DIR__ . "/../../../../test_images/_tmpimg.png";
    copy($orig_file_path, $tmp_file);

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
      'file' => [
        "name" => $filename,
      ],
      'sig' => $sig,
      'MAX_FILE_SIZE' => "5242880"
    ];

    $r = $this->reqPostFileBeRedirect("/admin/files/upload", $request_data, $request_file);
    $this->assertEquals("/admin/files/upload", $r->redirectUrl);

    $c = $this->reqGet("/admin/files/ajax_index");
    $this->assertInstanceOf(FilesController::class, $c);
//    var_dump($c->get('files'));
    $this->assertEquals($filename, $c->get('files')[0]['name']);

    $this->assertCount($before_files_count + 1, $c->get('files'), "登録後なので1個増える");
  }

}
