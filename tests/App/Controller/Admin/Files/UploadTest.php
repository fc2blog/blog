<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Files;

use Exception;
use Fc2blog\Tests\DBHelper;
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
        parent::setUp();
    }

    public function testWithOutXRequestedWithRequestHeader(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/files/ajax_index");
        $this->assertEquals(200, $c->get('http_status_code'));

        $c = $this->reqBase(
            false,
            'GET',
            "/admin/files/ajax_index",
            [],
            [],
            [],
            false
        );
        $this->assertEquals(403, $c->get('http_status_code'));
    }

    public function testIndexAndUploadFile(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        // admin/files/ajax_indexは一覧の部分（「ファイル検索」を含んでそれ移行
        // なぜ分離されているんだろうか…？
        $c = $this->reqGet("/admin/files/ajax_index");
        $this->assertInstanceOf(FilesController::class, $c);

        $before_files_count = count($c->get('files'));

        $filename = $this->uploadFile();

        $c = $this->reqGet("/admin/files/ajax_index");
        $this->assertInstanceOf(FilesController::class, $c);
//    var_dump($c->get('files'));
        $this->assertEquals($filename, $c->get('files')[0]['name']);

        $this->assertCount($before_files_count + 1, $c->get('files'), "登録後なので1個増える");
    }

    public function testIndexSearch(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $f1 = $this->uploadFile();
        $this->uploadFile();
        $this->uploadFile();

        ## search test.
        $c = $this->reqGet("/admin/files/ajax_index", [
            "limit" => "5",
            "page" => "0",
            "order" => "created_at_desc",
            "keyword" => "",
        ]);
        $this->assertInstanceOf(FilesController::class, $c);
        $this->assertCount(2/*初期登録が2コあるので*/ + 3, $c->get('files'));

        ## search test.
        $c = $this->reqGet("/admin/files/ajax_index", [
            "limit" => "5",
            "page" => "0",
            "order" => "created_at_desc",
            "keyword" => $f1,
        ]);
        $this->assertInstanceOf(FilesController::class, $c);
        $this->assertCount(1, $c->get('files'));
    }

    public function uploadFile(string $file_name = null): string
    {
        $this->mergeAdminSession();

        $c = $this->reqGet("/admin/files/upload");
        $this->assertInstanceOf(FilesController::class, $c);

        # test file upload.
        $c = $this->reqGet("/admin/files/ajax_index");
        $this->assertInstanceOf(FilesController::class, $c);

        try {
            $orig_file_path = realpath(__DIR__ . "/../../../../test_images/" . random_int(0, 9) . ".png");
        } catch (Exception $e) {
            throw new RuntimeException("failed random_int");
        }

        $tmp_file = __DIR__ . "/../../../../test_images/_temp_img.png";
        copy($orig_file_path, $tmp_file);

        $sig = $this->getSig();

        $request_file = [
            'file' => [
                "name" => ['file' => pathinfo($tmp_file, PATHINFO_BASENAME)],
                "type" => ['file' => "image/png"],
                "size" => ['file' => filesize($tmp_file)],
                "tmp_name" => ['file' => $tmp_file],
                "error" => ['file' => UPLOAD_ERR_OK],
            ]
        ];
        $filename = (!is_null($file_name)) ? $file_name : "test" . microtime(true) . ".png";
        $request_data = [
            'file' => [
                "name" => $filename,
            ],
            'sig' => $sig,
            'MAX_FILE_SIZE' => "5242880"
        ];

        $r = $this->reqPostFileBeRedirect("/admin/files/upload", $request_data, $request_file);
        $this->assertEquals("/admin/files/upload", $r->redirectUrl);
        return $filename;
    }

    // TODO ソート関連のファイル検索テスト拡充

    public function testOrderBy(): void
    {
        DBHelper::clearDbAndInsertFixture();
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();

        $this->uploadFile();
        $this->uploadFile();

        $c = $this->reqGet("/admin/files/ajax_index", [
            "limit" => "5",
            "page" => "0",
            "order" => "name_desc",
            "keyword" => "",
        ]);
        $this->assertInstanceOf(FilesController::class, $c);
        $this->assertCount(2/*初期登録が2個あるので*/ + 2, $c->get('files'));

        $files = $c->getData()['files'];
//    var_dump($files);

        $name_desc_first_id = $files[0]['id'];
        $name_desc_last_id = $files[3]['id'];

        $c = $this->reqGet("/admin/files/ajax_index", [
            "limit" => "5",
            "page" => "0",
            "order" => "name_asc",
            "keyword" => "",
        ]);
        $this->assertInstanceOf(FilesController::class, $c);
        $this->assertCount(4, $c->get('files'));

        $files = $c->getData()['files'];
//    var_dump($files);

        $name_asc_first_id = $files[0]['id'];
        $name_asc_last_id = $files[3]['id'];

        $this->assertEquals($name_asc_first_id, $name_desc_last_id);
        $this->assertEquals($name_asc_last_id, $name_desc_first_id);

    }

    // Todo 他のソート条件も追記していく

}
