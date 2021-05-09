<?php

namespace Fc2blog\Model;

use Fc2blog\Config;

class CategoriesModel extends Model
{

    public static $instance = null;

    public function __construct()
    {
    }

    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new CategoriesModel();
        }
        return self::$instance;
    }

    public function getTableName(): string
    {
        return 'categories';
    }

    public function getAutoIncrementCompositeKey(): string
    {
        return 'blog_id';
    }

    /**
     * バリデート処理
     * @param array $data
     * @param ?array $valid_data
     * @param array $white_list
     * @return array
     */
    public function validate(array $data, ?array &$valid_data = [], array $white_list = []): array
    {
        // バリデートを定義
        $this->validates = array(
            'parent_id' => array(
                'required' => true,
                'numeric' => array(),
            ),
            'name' => array(
                'required' => true,
                'trim' => true,
                'minlength' => array('min' => 1),
                'maxlength' => array('max' => 50),
                'own' => array('method' => 'uniqueName')
            ),
            'category_order' => array(
                'default_value' => Config::get('CATEGORY.ORDER.ASC'),
                'in_array' => array('values' => array_keys($this->getOrderList())),
            ),
        );

        return parent::validate($data, $valid_data, $white_list);
    }

    /**
     * カテゴリ名のユニークチェック
     * (blog_idとidがある前提)
     * @param $value
     * @param $option
     * @param $key
     * @param $data
     * @param $model
     * @return bool|string
     */
    public static function uniqueName($value, $option, $key, $data, $model)
    {
        if (empty($data['blog_id'])) {
            // blog_idとidが指定されない場合エラーメッセージを返却
            return 'Logic Error:[CategoriesModel][uniqueName]blog_id is empty!';
        }
        $where = 'blog_id=? AND name=? AND parent_id=?';
        $params = array($data['blog_id'], $data['name'], $data['parent_id']);
        if (!empty($data['id'])) {
            // 編集時は自分を除外
            $where .= ' AND id<>?';
            $params[] = $data['id'];
        }
        $options = array(
            'where' => $where,
            'params' => $params,
        );
        if ($model->isExist($options)) {
            return __('Same name exists in the same hierarchy');
        }
        return true;
    }

    /**
     * カテゴリーの表示順
     * @return array
     */
    public static function getOrderList(): array
    {
        return [
            Config::get('CATEGORY.ORDER.DESC') => __('Latest order'),
            Config::get('CATEGORY.ORDER.ASC') => __('Oldest First'),
        ];
    }

    /**
     * 一覧リスト($idによる親判定付き)
     * @param $blog_id
     * @param null $id
     * @return array
     */
    public function getParentList($blog_id, $id = null)
    {
        $categories = $this->getList($blog_id);
        $value = array();
        $options = array();
        foreach ($categories as $category) {
            $options[$category['id']] = array('value' => $category['name'], 'level' => $category['level']);
            if ($category['id'] == 1 || $id == 1) {
                $options[$category['id']]['disabled'] = true;
            }
            if (!$id) {
                continue;
            }
            if ($category['id'] == $id) {
                $value = $category;
            }
            if ($value && $value['lft'] <= $category['lft'] && $value['rgt'] >= $category['rgt']) {
                $options[$category['id']]['disabled'] = true;
            }
        }
        return $options;
    }

    /**
     * 検索用の一覧取得
     * @param $blog_id
     * @return array
     */
    public function getSearchList($blog_id)
    {
        $categories = $this->getList($blog_id);
        $options = array();
        foreach ($categories as $category) {
            $options[$category['id']] = array('value' => $category['name'] . ' (' . $category['count'] . ')', 'level' => $category['level']);
        }
        return $options;
    }

    /**
     * 一覧を取得する
     * @param $blog_id
     * @param array $options
     * @return mixed
     */
    public function getList($blog_id, $options = array())
    {
        $options['where'] = (isset($options['where']) && $options['where'] != '') ? 'blog_id=? AND ' . $options['where'] : 'blog_id=?';
        $options['params'] = isset($options['params']) ? array_merge(array($blog_id), $options['params']) : array($blog_id);
        $options['order'] = isset($options['order']) ? $options['order'] : 'categories.lft';
        return $this->findNode($options);
    }

    /**
     * 記事のカテゴリを取得する
     * @param $blog_id
     * @param $entry_id
     * @return mixed
     */
    public function getEntryCategories($blog_id, $entry_id)
    {
        $sql = <<<SQL
SELECT *
FROM categories, entry_categories
WHERE entry_categories.blog_id=?
  AND entry_categories.entry_id=?
  AND categories.blog_id=?
  AND entry_categories.category_id=categories.id
SQL;
        $params = array($blog_id, $entry_id, $blog_id);
        $options = array();
        $options['result'] = DBInterface::RESULT_ALL;
        return $this->findSql($sql, $params, $options);
    }

    /**
     * 記事のカテゴリを取得する
     * @param $blog_id
     * @param array $entry_ids
     * @return array
     */
    public function getEntriesCategories($blog_id, $entry_ids = array())
    {
        if (!count($entry_ids)) {
            return array();
        }
        $in_where = '(' . implode(',', array_fill(0, count($entry_ids), '?')) . ')';

        $sql = <<<SQL
SELECT *
FROM categories, entry_categories
WHERE entry_categories.blog_id=?
  AND entry_categories.entry_id IN {$in_where}
  AND categories.blog_id=?
  AND entry_categories.category_id=categories.id
SQL;
        $params = array_merge(array($blog_id), $entry_ids, array($blog_id));
        $options = array();
        $options['result'] = DBInterface::RESULT_ALL;
        $categories = $this->findSql($sql, $params, $options);

        $entries_categories = array();
        foreach ($entry_ids as $entry_id) {
            $entries_categories[$entry_id] = array();
        }
        foreach ($categories as $category) {
            $entries_categories[$category['entry_id']][] = $category;
        }
        return $entries_categories;
    }

    /**
     * カテゴリーIDの配列からカテゴリー情報を取得する
     * @param $ids
     * @param $blog_id
     * @return array|mixed
     */
    public function findByIdsAndBlogId($ids, $blog_id)
    {
        if (!is_array($ids) || count($ids) < 1) {
            return array();
        }
        return $this->find('all', array(
            'where' => 'blog_id=? AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')',
            'params' => array_merge(array($blog_id), $ids),
        ));
    }

    /**
     * 件数を増加させる処理
     * @param $blog_id
     * @param array $ids
     * @return array|false|int|mixed
     */
    public function increaseCount($blog_id, $ids = array())
    {
        if (!count($ids)) {
            return 0;
        }
        $sql = 'UPDATE ' . $this->getTableName() . ' SET count=count+1 WHERE blog_id=? AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
        $params = array_merge(array($blog_id), $ids);
        $options['result'] = DBInterface::RESULT_SUCCESS;
        return $this->executeSql($sql, $params, $options);
    }

    /**
     * 件数を減少させる処理
     * @param $blog_id
     * @param array $ids
     * @return array|false|int|mixed
     */
    public function decreaseCount($blog_id, $ids = array())
    {
        if (!count($ids)) {
            return 0;
        }
        $sql = 'UPDATE ' . $this->getTableName() . ' SET count=count-1 WHERE blog_id=? AND count>0 AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
        $params = array_merge(array($blog_id), $ids);
        $options['result'] = DBInterface::RESULT_SUCCESS;
        return $this->executeSql($sql, $params, $options);
    }

    /**
     * idとblog_idをキーとした削除 + 付随情報も削除
     * @param $id
     * @param $blog_id
     * @return array|false|int|mixed
     */
    public function deleteNodeByIdAndBlogId($id, $blog_id)
    {
        // 対象のカテゴリー取得
        $category = $this->findByIdAndBlogId($id, $blog_id);
        if (empty($category)) {
            return 0;
        }

        // カテゴリーの紐付け情報削除(子カテゴリの紐付けも削除する)
        $sql = <<<SQL
DELETE FROM entry_categories
WHERE entry_categories.blog_id=?
  AND entry_categories.category_id IN (
    SELECT id
    FROM categories
    WHERE categories.blog_id=?
      AND categories.lft>=?
      AND categories.rgt<=?
  )
SQL;
        $params = array($blog_id, $blog_id, $category['lft'], $category['rgt']);
        $this->executeSql($sql, $params);

        // カテゴリーに紐付いていないEntryに未分類エントリーと紐付ける
        $sql = <<<SQL
INSERT INTO entry_categories
SELECT entries.blog_id, entries.id, 1
FROM entries
WHERE entries.blog_id=?
  AND NOT EXISTS (
    SELECT 1
    FROM entry_categories
    WHERE entry_categories.blog_id=?
      AND entries.id=entry_categories.entry_id
  )
SQL;
        $params = array($blog_id, $blog_id);
        $this->executeSql($sql, $params);

        // 未分類エントリーの件数を更新
        $options = array(
            'fields' => 'COUNT(*)',
            'where' => 'blog_id=? AND category_id=1',
            'params' => array($blog_id),
        );
        $count = Model::load('EntryCategories')->find('one', $options);
        $this->updateByIdAndBlogId(array('count' => $count), 1, $blog_id);

        // カテゴリー削除
        return parent::deleteNodeById($id, 'blog_id=?', array($blog_id));
    }

    /**
     * テンプレートで使用するカテゴリー一覧を取得
     * @param $blog_id
     * @return mixed
     */
    public function getTemplateCategories($blog_id)
    {
        $categories = $this->getList($blog_id);
        $parent = null;
        foreach ($categories as $key => $category) {
            if ($category['parent_id'] == 0) {
                $parent = $category;
            }
            $categories[$key]['is_parent'] = ($category['rgt'] - $category['lft']) > 1;
            $categories[$key]['is_nosub'] = ($category['rgt'] - $category['lft']) == 1;
            $categories[$key]['is_sub_begin'] = $category['parent_id'] != 0 && !empty($categories[$key - 1]) && ($categories[$key - 1]['lft'] + 1 == $category['lft']);
            $categories[$key]['is_sub_end'] = $category['parent_id'] != 0 && (empty($categories[$key + 1]) || $categories[$key + 1]['lft'] != $category['rgt'] + 1);
            if ($categories[$key]['is_sub_end']) {
                if (empty($categories[$key + 1])) {
                    $categories[$key]['climb_hierarchy'] = $category['level'] - 1;
                } else {
                    $categories[$key]['climb_hierarchy'] = $category['level'] - $categories[$key + 1]['level'];
                }
                $categories[$key]['is_sub_end'] = !empty($parent) && (empty($categories[$key + 1]) || $categories[$key + 1]['lft'] > $parent['rgt']);
            }
            $categories[$key]['is_sub_hasnext'] = $category['parent_id'] != 0 && !$categories[$key]['is_sub_end'];
        }
        return $categories;
    }
}
