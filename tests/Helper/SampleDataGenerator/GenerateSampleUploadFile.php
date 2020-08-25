<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Exception;
use Fc2blog\App;
use Fc2blog\Model\FilesModel;
use InvalidArgumentException;
use RuntimeException;

class GenerateSampleUploadFile
{
  use FakerTrait;
  use RandomUtilTrait;

  public function generateSampleUploadImage(string $blog_id, int $num = 10): array
  {
    $faker = static::getFaker();
    $upload_image_list = [];

    $files_model = new FilesModel();

    while ($num-- > 0) {
      try {
        $file_path = realpath(__DIR__ . "/../../test_images/" . random_int(0, 9) . ".png");
      } catch (Exception $e) {
        throw new RuntimeException("failed random_int");
      }

      $file = [];
      $file['file'] = [
        "name" => pathinfo($file_path, PATHINFO_BASENAME),
        "type" => "image/png",
        "size" => filesize($file_path),
        "tmp_name" => $file_path,
        "error" => UPLOAD_ERR_OK,
      ];

      $request_upload_file = [
        'name' => $faker->colorName,
      ];

      $error_upload_image = $files_model->insertValidate($file, $request_upload_file, $data_file);
      if (count($error_upload_image) > 0) {
        throw new InvalidArgumentException("error request data");
      }

      $data_file['blog_id'] = $blog_id;
      $tmp_name = $data_file['tmp_name'];

      unset($data_file['tmp_name']);

      $id = $files_model->insert($data_file);
      if ($id === false) {
        throw new RuntimeException("insert file failed:" . print_r($data_file, true));
      }

      // ファイルの移動
      $data_file['id'] = $id;
      $move_file_path = App::getUserFilePath($data_file, true);

      App::mkdir($move_file_path);

      copy($tmp_name, $move_file_path);
      // 本来なら、move_uploaded_file($tmp_name, $move_file_path);だが、これはテストなので。

      $upload_image_list[] = $files_model->findById($id);
    }

    return $upload_image_list;
  }
}
