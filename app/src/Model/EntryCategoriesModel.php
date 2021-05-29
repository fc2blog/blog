<?php

namespace Fc2blog\Model;

use LogicException;

class EntryCategoriesModel extends Model
{

    public static $instance = null;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new EntryCategoriesModel();
        }
        return self::$instance;
    }

    public function getTableName(): string
    {
        return 'entry_categories';
    }

    /**
     * バリデート処理
     * @param array $data
     * @param array|null $valid_data
     * @param array $white_list
     * @return array
     */
    public function validate(array $data, ?array &$valid_data = [], array $white_list = []): array
    {
        // バリデートを定義
        $this->validates = array(
            'category_id' => array(
                'multiple' => array(
                    'int' => true,            // int型にキャスト
                    'min' => array('min' => 0), // 0以上
                ),
                'array_unique' => true,     // 重複排除
            ),
        );

        return parent::validate($data, $valid_data, $white_list);
    }

    /**
     * 記事のカテゴリID一覧を取得
     * @param $blog_id
     * @param $entry_id
     * @return array
     */
    public function getCategoryIds($blog_id, $entry_id)
    {
        $categories = $this->find('all', array(
            'fields' => 'category_id',
            'where' => 'blog_id=? AND entry_id=?',
            'params' => array($blog_id, $entry_id)
        ));
        $ids = array();
        foreach ($categories as $category) {
            $ids[] = $category['category_id'];
        }
        return $ids;
    }

    /**
     * 記事とカテゴリの紐付けを保存する
     * カテゴリの記事数の変動も行う
     * @param $blog_id
     * @param $entry_id
     * @param $data
     * @return bool 全体としての成功・失敗（トランザクションは無い）
     */
    public function save($blog_id, $entry_id, $data)
    {
        $categories_model = Model::load('Categories');

        // 登録済みのカテゴリを取得
        $now_category_ids = $this->getCategoryIds($blog_id, $entry_id);

        // 新しく登録するデータと差分を取る
        $category_ids = $data['category_id'];
        if (!count($category_ids)) {
            // カテゴリ未選択の場合は未分類カテゴリを追加する
            $category_ids = array(1);
        }

        $delete_ids = array_diff($now_category_ids, $category_ids);
        $insert_ids = array_diff($category_ids, $now_category_ids);

        $ret = true;

        // 削除された紐付け分件数を減らす
        if (count($delete_ids)) {
            $ret = $ret && $this->delete('blog_id=? AND entry_id=? AND category_id IN (' . implode(',', $delete_ids) . ')', array($blog_id, $entry_id));
            // カテゴリーの記事件数減少処理
            $categories_model->decreaseCount($blog_id, $delete_ids);
        }

        // 新たに追加する紐付け分件数を増やす
        if (count($insert_ids)) {
            $columns = array('blog_id', 'entry_id', 'category_id');
            $values = array();
            foreach ($insert_ids as $categoryId) {
                $values[] = $blog_id;
                $values[] = $entry_id;
                $values[] = $categoryId;
            }
            $ret = $ret && ($this->multipleInsert($columns, $values) !== false);
            // カテゴリーの記事数増加処理
            $categories_model->increaseCount($blog_id, $insert_ids);
        }

        return $ret;
    }

    /**
     * 記事に付随する情報を削除
     * @param $blog_id
     * @param $entry_id
     * @return array|false|int|mixed
     */
    public function deleteEntryRelation($blog_id, $entry_id)
    {
        $category_ids = $this->getCategoryIds($blog_id, $entry_id);
        if (count($category_ids)) {
            Model::load('Categories')->decreaseCount($blog_id, $category_ids);
        }
        return $this->delete('blog_id=? AND entry_id=?', array($blog_id, $entry_id));
    }

    public function findByIdAndBlogId($id, ?string $blog_id, $options = [])
    {
        // entry_categories table does not have id column.
        throw new LogicException("this method not works in EntryCategoriesModel");
    }

    public function findsByEntryIdAndBlogId($entry_id, ?string $blog_id, $options = [])
    {
        if (empty($entry_id) || empty($blog_id)) {
            return [];
        }
        $options['where'] = isset($options['where']) ? 'blog_id=? AND entry_id=? AND ' . $options['where'] : 'blog_id=? AND entry_id=?';
        $options['params'] = isset($options['params']) ? array_merge([$blog_id, $entry_id], $options['params']) : [$blog_id, $entry_id];
        $entry_category_list = $this->find('all', $options);

        $category_list = [];
        $category_model = new CategoriesModel();
        foreach ($entry_category_list as $entry_category) {
            $category_list[] = $category_model->findByIdAndBlogId($entry_category['category_id'], $blog_id);
        }
        return $category_list;
    }

    public function findByCategoryIdAndBlogId($category_id, ?string $blog_id, $options = [])
    {
        if (empty($category_id) || empty($blog_id)) {
            return [];
        }
        $options['where'] = isset($options['where']) ? 'blog_id=? AND category_id=? AND ' . $options['where'] : 'blog_id=? AND category_id=?';
        $options['params'] = isset($options['params']) ? array_merge([$blog_id, $category_id], $options['params']) : [$blog_id, $category_id];
        $entry_category_row = $this->find('row', $options);

        if ($entry_category_row === false) {
            return false;
        }

        $category_model = new CategoriesModel();
        return $category_model->findByIdAndBlogId($entry_category_row['category_id'], $blog_id);
    }
}
