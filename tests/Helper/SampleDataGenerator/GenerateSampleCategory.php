<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Exception;
use Fc2blog\Model\CategoriesModel;
use Fc2blog\Model\EntryCategoriesModel;
use Fc2blog\Util\Log;
use InvalidArgumentException;
use RuntimeException;

class GenerateSampleCategory
{
  use FakerTrait;
  use RandomUtilTrait;

  /**
   * @param string $blog_id
   * @param int $parent_id
   * @param int $num
   * @return array created result list
   * @throws Exception
   */
  public function generateSampleCategories(string $blog_id, $parent_id = 0, int $num = 10): array
  {
    $categories_model = new CategoriesModel();
    $result = [];
    $faker = $this->getFaker();

    while ($num-- > 0) {
      $insert_data = [
        'blog_id' => $blog_id,
        'parent_id' => (string)$parent_id,
        'name' => $faker->word().(string)random_int(1,100),
        'category_order' => static::getRandomKey($categories_model->getOrderList()),
      ];

      $errors = $categories_model->validate($insert_data, $data);

      if (count($errors) > 0) {
        // if set same category name (because low randomness) continue.
        if (
          "Same name exists in the same hierarchy" === ($errors['name'] ?? false) ||
          "同一階層に同じ名前が存在しています" === ($errors['name'] ?? false)
        ) {
          continue;
        }

        throw new InvalidArgumentException("validate error:" . print_r($errors, true));
      }

      $data['blog_id'] = $blog_id;
      $id = $categories_model->addNode($data, 'blog_id=?', array($blog_id));

      if ($id == false) {
        throw new RuntimeException("insert error:" . print_r($data, true));
      }

      $result[] = [
        'id' => $id,
        'parent_id' => $data['parent_id'],
        'name' => $data['name'],
      ];

      if (1 === random_int(1, 4)) {
        static::generateSampleCategories($blog_id, $id, 2);
      }
    }

    return $result;
  }

  /**
   * @param string $blog_id
   * @return array
   */
  public function getCategoryList(string $blog_id): array
  {
    $categories_model = new CategoriesModel();
    return $categories_model->getList($blog_id);
  }

  /**
   * @param string $blog_id
   */
  public function syncRemoveAllCategories(string $blog_id)
  {
    $categories_model = new CategoriesModel();
    $list = static::getCategoryList($blog_id);

    foreach ($list as $row) {
      if ($row['parent_id'] === 0 && $row['name'] === "未分類") {
        continue; // don't delete root category.
      }
      if (1 !== $categories_model->deleteByIdAndBlogId($row['id'], $row['blog_id'])) {
        throw new RuntimeException("delete failed, " . print_r($row, true));
      }
    }
  }

  /**
   * @param string $blog_id
   * @param int $entry_id
   * @param int[] $category_id_list
   */
  public function updateEntryCategories(string $blog_id, int $entry_id, array $category_id_list): void
  {
    $entry_categories_model = new EntryCategoriesModel();
    $data = ['category_id' => $category_id_list];
    // カテゴリと紐付
    $result = $entry_categories_model->save($blog_id, $entry_id, $data);
    if ($result === false) {
      Log::error(__FILE__ . ":" . __LINE__ . " updateEntryCategories failed.", ['blog_id' => $blog_id, 'entry_id' => $entry_id, 'data' => $data]);
      throw new RuntimeException(__FILE__ . ":" . __LINE__ . " updateEntryCategories failed.");
    }
  }
}
