<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Fc2blog\Model\BlogsModel;
use InvalidArgumentException;
use RuntimeException;

class GenerateSampleBlog
{
  use FakerTrait;
  use RandomUtilTrait;

  /**
   * @param int $user_id
   * @param int $num
   * @return array blog list
   */
  public function generateSampleBlog(int $user_id, int $num = 10): array
  {
    $blogs_model = new BlogsModel();
    $faker = static::getFaker();
    $generated_blog_list = [];

    while ($num-- > 0) {
      $request_data = [
        "id" => $faker->word."blog",
        "name" => $faker->sentence(2)."なブログ",
        "nickname" => $faker->name
      ];

      // 新規登録処理
      $errors_blog = $blogs_model->validate($request_data, $blog_data, array('id', 'name', 'nickname'));
      if (count($errors_blog) > 0) {
        throw new InvalidArgumentException("invalid request data:". print_r($errors_blog, true));
      }

      $blog_data['user_id'] = $user_id;

      $id = $blogs_model->insert($blog_data);

      if ($id === false) {
        throw new RuntimeException("blog insert failed");
      }

      $generated_blog_list[] = $blogs_model->findById($id);
    }

    return $generated_blog_list;
  }
}
