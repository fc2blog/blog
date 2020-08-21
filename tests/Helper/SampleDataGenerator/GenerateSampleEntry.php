<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Fc2blog\Model\EntriesModel;
use Fc2blog\Model\EntryCategoriesModel;
use Fc2blog\Model\EntryTagsModel;
use Fc2blog\Model\TagsModel;
use InvalidArgumentException;

class GenerateSampleEntry
{
  use FakerTrait;
  use RandomUtilTrait;

  /**
   * @param string $blog_id
   * @param int $num
   * @return array tag list
   */
  public function generateSampleEntry(string $blog_id, int $num = 10): array
  {
    $entries = [];

    $entries_model = new EntriesModel();
    $entry_categories_model = new EntryCategoriesModel();
    $tags_model = new TagsModel();

    $faker = static::getFaker();

    while ($num-- > 0) {
      $all_tags = $tags_model->getTemplateTags($blog_id);
      $tags = static::getRandomSlice($all_tags, 4);

      $entry = [
        'title' => $faker->sentence(3),
        'body' => $faker->sentence(20),
        'extend' => $faker->sentence(20),
        'tag' => $tags,
        'open_status' => static::getRandomKey(EntriesModel::getOpenStatusList()),
        'password' => static::getRandomChoice("password", ""),
        'auto_linefeed' => static::getRandomkey(EntriesModel::getAutoLinefeedList()),
        'comment_accepted' => static::getRandomkey(EntriesModel::getCommentAcceptedList()),
        'posted_at' => date('Y-m-d H:i:s'),
      ];

      $generate_sample_category = new GenerateSampleCategory();
      $entry_categories =
        static::getRandomSlice($generate_sample_category->getCategoryList($blog_id), 5);
      $entry_category_input = [];
      //entry_categories[category_id][
      foreach ($entry_categories as $entry_category) {
        $entry_category_input['category_id'][] = $entry_category['id'];
      }

      $whitelist_entry = ['title', 'body', 'extend', 'open_status', 'password', 'auto_linefeed', 'comment_accepted', 'posted_at'];

      $errors_entry = $entries_model->validate($entry, $entry_insert_data, $whitelist_entry);
      if (count($errors_entry) > 0) {
        throw new InvalidArgumentException("invalid entry data:" . print_r($errors_entry, true));
      }

      $errors_entry_categories = $entry_categories_model->validate($entry_category_input, $entry_categories_data, ['category_id']);
      if (count($errors_entry) > 0) {
        throw new InvalidArgumentException("invalid entry's category data:" . print_r($errors_entry_categories, true));
      }

      $entry_insert_data['blog_id'] = $blog_id;

      $entry_id = $entries_model->insert($entry_insert_data);
      if ($entry_id === false) {
        throw new \RuntimeException("entry insert failed");
      }

      // カテゴリと紐付
      $res = $entry_categories_model->save($blog_id, $entry_id, $entry_categories_data);
      if ($res === false) {
        throw new \RuntimeException("insert category failed:");
      }

      // タグと紐付
      $entry_tags = new EntryTagsModel();
      $entry_tags->save($blog_id, $entry_id, $tags);

      $entries[] = $entries_model->findByIdAndBlogId($entry_id, $blog_id);
    }

    return $entries;
  }
}
