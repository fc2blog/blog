<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Fc2blog\Model\EntryTagsModel;
use Fc2blog\Model\TagsModel;
use RuntimeException;

class GenerateSampleTag
{
  use FakerTrait;
  use RandomUtilTrait;

  /**
   * @param string $blog_id
   * @param int $entry_id
   * @param int $num
   * @return array tag list
   */
  public function generateSampleTagsToSpecifyEntry(string $blog_id, int $entry_id, int $num = 10): array
  {
    $entry_tags_model = new EntryTagsModel();
    $tags_model = new TagsModel();
    $faker = $this->getFaker();

    $input_tags = [];
    while ($num-- > 0) {
      $input_tags[] = $faker->word();
    }

    # validate、空文字の拒否
    foreach ($input_tags as $key => $val) {
      if (!is_string($val) && $val === "") {
        unset($input_tags[$key]);
      }
    }

    # 既存のタグを取得
    $exists_tags = $tags_model->getListByNames($blog_id, $input_tags);

    $new_tags = [];
    # 存在していないタグを作成
    foreach ($input_tags as $id => $tag) {
      if (!in_array($tag, $exists_tags)) {
        # タグが存在していないので作成
        $data_tag = [
          'blog_id' => $blog_id,
          'name' => $tag,
          'count' => 0,
        ];
        $tag_id = $tags_model->insert($data_tag);
        $new_tags[$tag_id] = $tag;
      } else {
        $new_tags[] = $tag;
      }
    }

    # 既存タグリストと、結果タグリストの差分で増減を確定させる
    $delete_tag_ids = array_diff(array_keys($exists_tags), array_keys($new_tags));
    $insert_tag_ids = array_diff(array_keys($new_tags), array_keys($exists_tags));

    # 削除されたタグの紐付け分件数を更新
    if (count($delete_tag_ids) > 0) {
      # 削除されたタグを削除
      $res = $entry_tags_model->delete('blog_id=? AND entry_id=? AND tag_id IN (' . implode(',', $delete_tag_ids) . ')', [$blog_id, $entry_id]);
      if ($res === false) {
        throw new RuntimeException("delete form entry_tags failed.");
      }
      # タグの記事件数を減少
      $tags_model->decreaseCount($blog_id, $delete_tag_ids);
      if ($res === false) {
        throw new RuntimeException("decreaseCount failed.");
      }
    }

    # 追加されたタグの紐付け分件数を更新
    if (count($insert_tag_ids) > 0) {
      $columns = ['blog_id', 'entry_id', 'tag_id'];
      $values = [];
      # blog_id, entry_id, tag_id の3つが１ループ毎にふえ、3*n個のvaluesになる
      foreach ($insert_tag_ids as $tag_id) {
        $values[] = $blog_id;
        $values[] = $entry_id;
        $values[] = $tag_id;
      }
      $res = $entry_tags_model->multipleInsert($columns, $values);
      if ($res === false) {
        throw new RuntimeException("multipleInsert failed.");
      }
      // タグの記事数増加処理
      $res = $tags_model->increaseCount($blog_id, $insert_tag_ids);
      if ($res === false) {
        throw new RuntimeException("increaseCount failed.");
      }
    }

    return $new_tags;
  }
}
