<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Exception;
use Fc2blog\Config;
use Fc2blog\Model\BlogSettingsModel;
use Fc2blog\Model\CommentsModel;
use InvalidArgumentException;
use RuntimeException;

class GenerateSampleComment
{
  use FakerTrait;
  use RandomUtilTrait;

  /**
   * @param string $blog_id
   * @param int $entry_id
   * @param int $num
   * @param bool $sortable_uniq_name fakerで名前を生成すると衝突する可能性があるので、衝突しづらいランダムを生成するか？
   * @return array
   */
  public function generateSampleComment(string $blog_id, int $entry_id, int $num = 10, bool $sortable_uniq_name = false): array
  {
    $faker = static::getFaker();
    $comment_list = [];

    $open_status_list = [
      Config::get('COMMENT.OPEN_STATUS.PUBLIC'),
      Config::get('COMMENT.OPEN_STATUS.PRIVATE')
    ];

    try {
      $counter = random_int(1, 10000);
    } catch (Exception $e) {
      throw new RuntimeException("maybe random_int failed");
    }
    while ($num-- > 0) {
      try {
        $counter = $counter + random_int(1,10);
        $_name = $sortable_uniq_name ?
          "TT" . sprintf("%05d", random_int(1, 99999)) . sprintf("%05d", $counter) :
          $faker->name;
      } catch (Exception $e) {
        throw new RuntimeException("maybe random_int failed");
      }
      $request_comment = [
        'entry_id' => $entry_id,
        'name' => $_name,
        'title' => $faker->sentence(3),
        'mail' => $faker->email,
        'url' => $faker->url,
        'body' => $faker->text(500),
        'password' => "password",
        'open_status' => static::getRandomValue($open_status_list)
      ];

      // 入力チェック処理
      $white_list = ['entry_id', 'name', 'title', 'mail', 'url', 'body', 'password', 'open_status'];

      $comments_model = new CommentsModel();
      $errors_comment = $comments_model->registerValidate($request_comment, $data, $white_list);
      if (count($errors_comment) > 0) {
        throw new InvalidArgumentException("validation failed:" . print_r($errors_comment, true) . print_r($request_comment, true));
      }

      $data['blog_id'] = $blog_id;  // ブログIDの設定

      $blog_setting_model = new BlogSettingsModel();
      $blog_setting = $blog_setting_model->findByBlogId($blog_id);

      $id = $comments_model->insertByBlogSettingWithOutCookie($data, $blog_setting);

      if ($id === false) {
        throw new RuntimeException("insert failed:" . print_r($data, true) . print_r($blog_setting, true));
      }

      $comment_list[] = $comments_model->findById($id);
    }

    return $comment_list;
  }

  /**
   * Blogと記事を指定してコメントを全削除
   * @param string $blog_id
   * @param int $entry_id
   * @return int 削除した行数
   */
  public function removeAllComments(string $blog_id, int $entry_id): int
  {
    $cm = new CommentsModel();
    $all_comments = $cm->find('all', $cm->forTestGetCommentListOptionsByBlogSetting($blog_id, $entry_id));

    $i = 0;
    foreach ($all_comments as $comment) {
      $cm->deleteById($comment['id']);
      $i++;
    }
    return $i;
  }
}
