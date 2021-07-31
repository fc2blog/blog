<?php

namespace Fc2blog\Model;

class TagsModel extends Model
{

    public static $instance = null;

    const TAG = array(
        // 記事一覧の表示件数リスト
        'LIMIT_LIST' => array(
            10 => '10',
            20 => '20',
            40 => '40',
            60 => '60',
            80 => '80',
            100 => '100',
        ),
        'DEFAULT_LIMIT' => 20,
    );

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new TagsModel();
        }
        return self::$instance;
    }

    public function getTableName(): string
    {
        return 'tags';
    }

    public function getAutoIncrementCompositeKey(): string
    {
        return 'blog_id';
    }

    /**
     * バリデート処理(タグの名前更新のみの予定)
     * @param array $data
     * @param array|null $valid_data
     * @param array $white_list
     * @return array
     */
    public function validate(array $data, ?array &$valid_data = [], array $white_list = []): array
    {
        // バリデートを定義
        $this->validates = array(
            'name' => array(
                'required' => true,
                'trim' => true,
                'minlength' => array('min' => 1),
                'maxlength' => array('max' => 45),
                'own' => array('method' => 'uniqueName'),
            ),
        );

        return parent::validate($data, $valid_data, $white_list);
    }

    /**
     * タグ名のユニークチェック
     * (blog_idとidがある前提)
     * @param $value
     * @param $option
     * @param $key
     * @param $data
     * @param $model
     * @return bool|string
     * @noinspection PhpUnusedParameterInspection
     * @noinspection PhpUnused
     */
    public static function uniqueName($value, $option, $key, $data, $model)
    {
        if (empty($data['id']) || empty($data['blog_id'])) {
            // blog_idとidが指定されない場合エラーメッセージを返却
            return 'Logic Error:[TagsModel][uniqueName]id or blog_id is empty!';
        }
        $options = array(
            'where' => 'blog_id=? AND name=? AND id<>?',
            'params' => array($data['blog_id'], $value, $data['id']),
        );
        if ($model->isExist($options)) {
            return __('Tag with the same name already exists');
        }
        return true;
    }

    /**
     * 検索用の一覧取得
     * @param $blog_id
     * @return array
     */
    public function getSearchList($blog_id)
    {
        $options = array(
            'fields' => 'id, name, count',
            'where' => 'blog_id=?',
            'params' => array($blog_id),
            'order' => 'count DESC, id DESC',
        );
        $tags = $this->find('all', $options);
        $options = array();
        foreach ($tags as $tag) {
            $options[$tag['id']] = $tag['name'] . ' (' . $tag['count'] . ')';
        }
        return $options;
    }


    /**
     * タグ名からタグ情報取得
     * @param string $blog_id
     * @param array $tags
     * @return array
     */
    public function getListByNames(string $blog_id, array $tags = []): array
    {
        if (!count($tags)) {
            return array();
        }
        $options = array(
            'fields' => 'id, name',
            'where' => 'blog_id=? AND name IN (' . implode(',', array_fill(0, count($tags), '?')) . ')',
            'params' => array_merge([$blog_id], $tags),
        );
        return $this->find('list', $options);
    }


    /**
     * nameとblog_idの複合キーからデータを取得
     * @param $name
     * @param $blog_id
     * @param array $options
     * @return array
     * @noinspection PhpUnused
     */
    public function findByNameAndBlogId($name, $blog_id, array $options = array())
    {
        $options['where'] = isset($options['where']) ? 'name=? AND blog_id=? AND ' . $options['where'] : 'name=? AND blog_id=?';
        $options['params'] = isset($options['params']) ? array_merge(array($name, $blog_id), $options['params']) : array($name, $blog_id);
        return $this->find('row', $options);
    }

    /**
     * 良く使用するタグ一覧を取得する
     * @param $blog_id
     * @param array $options
     * @return array
     */
    public function getWellUsedTags($blog_id, array $options = array())
    {
        $options['fields'] = 'id, name';
        $options['where'] = (isset($options['where']) && $options['where'] != "") ? 'blog_id=? AND ' . $options['where'] : 'blog_id=?';
        $options['params'] = isset($options['params']) ? array_merge(array($blog_id), $options['params']) : array($blog_id);
        $options['limit'] = 6;
        $options['order'] = 'count DESC';
        return $this->find('list', $options);
    }

    /**
     * 記事のタグを文字列で取得する
     * @param $blog_id
     * @param $entry_id
     * @return array
     */
    public function getEntryTagNames($blog_id, $entry_id)
    {
        $tags = $this->getEntryTags($blog_id, $entry_id);
        $tag_values = array();
        foreach ($tags as $tag) {
            $tag_values[] = $tag['name'];
        }
        return $tag_values;
    }

    /**
     * 記事のタグを取得する
     * @param $blog_id
     * @param $entry_id
     * @return array
     */
    public function getEntryTags($blog_id, $entry_id)
    {
        $sql = <<<SQL
SELECT tags.*
FROM tags, entry_tags
WHERE entry_tags.blog_id=?
  AND entry_tags.entry_id=?
  AND tags.blog_id=?
  AND entry_tags.tag_id=tags.id
SQL;
        $params = array($blog_id, $entry_id, $blog_id);
        $options = array();
        $options['result'] = PDOQuery::RESULT_ALL;
        return $this->findSql($sql, $params, $options);
    }

    /**
     * 記事のタグを取得する
     * @param $blog_id
     * @param $entry_ids
     * @return array
     */
    public function getEntriesTags($blog_id, $entry_ids)
    {
        if (!count($entry_ids)) {
            return array();
        }
        $in_where = '(' . implode(',', array_fill(0, count($entry_ids), '?')) . ')';

        $sql = <<<SQL
SELECT tags.*, entry_tags.entry_id
FROM tags, entry_tags
WHERE entry_tags.blog_id=?
  AND entry_tags.entry_id IN {$in_where}
  AND tags.blog_id=?
  AND entry_tags.tag_id=tags.id
SQL;
        $params = array_merge(array($blog_id), $entry_ids, array($blog_id));
        $options = array();
        $options['result'] = PDOQuery::RESULT_ALL;
        $tags = $this->findSql($sql, $params, $options);

        $entries_tags = array();
        foreach ($entry_ids as $entry_id) {
            $entries_tags[$entry_id] = array();
        }
        foreach ($tags as $tag) {
            $entries_tags[$tag['entry_id']][] = $tag;
        }
        return $entries_tags;
    }

    /**
     * 件数を増加させる処理
     * @param string $blog_id
     * @param array $ids
     * @return array|int
     */
    public function increaseCount(string $blog_id, array $ids = array())
    {
        if (!count($ids)) {
            return 0;
        }
        $sql = 'UPDATE ' . $this->getTableName() . ' SET count=count+1 WHERE blog_id=? AND id IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
        $params = array_merge(array($blog_id), $ids);
        $options['result'] = PDOQuery::RESULT_SUCCESS;
        return $this->executeSql($sql, $params, $options);
    }

    /**
     * タグの件数を減少させる処理(0件のタグは削除)
     * @param $blog_id
     * @param array $ids
     * @return bool
     */
    public function decreaseCount($blog_id, array $ids = [])
    {
        if (count($ids) === 0) {
            return 0;
        }
        $sql = 'UPDATE ' . $this->getTableName() .
            ' SET count=count-1 WHERE blog_id=? AND count>0 AND id ' .
            ' IN (' . implode(',', array_fill(0, count($ids), '?')) . ')';
        $params = array_merge([$blog_id], $ids);
        $options['result'] = PDOQuery::RESULT_SUCCESS;
        return
            # 有効タグ数の数え直し
            $this->executeSql($sql, $params, $options) &&
            # カウントが0件のtag行を削除
            $this->delete('blog_id=? AND count<=0', [$blog_id]);
    }

    /**
     * idとblog_idをキーとした削除 + 付随情報も削除
     * @param $id
     * @param $blog_id
     * @param array $options
     * @return array|int
     */
    public function deleteByIdAndBlogId($id, $blog_id, array $options = array())
    {
        // タグの紐付け情報削除
        (new EntryTagsModel())->delete('blog_id=? AND tag_id=?', array($blog_id, $id));

        // 記事本体削除
        return parent::deleteByIdAndBlogId($id, $blog_id, $options);
    }

    /**
     * idとblog_idをキーとした削除 + 付随情報も削除
     * @param array|int $ids
     * @param $blog_id
     * @param array $options
     * @return bool
     * @noinspection PhpUnused
     */
    public function deleteByIdsAndBlogId($ids, $blog_id, array $options = array())
    {
        // 単体ID対応
        if (is_numeric($ids)) {
            $ids = array($ids);
        }
        // 数値型配列チェック
        if (!$this->is_numeric_array($ids)) {
            return false;
        }

        // 削除処理(TODO:時間ができればSQLの最適化を行う)
        $flag = true;
        foreach ($ids as $id) {
            $flag = $flag && $this->deleteByIdAndBlogId($id, $blog_id, $options);
        }
        return $flag;
    }

    /**
     * 全タグを取得
     * FC2テンプレートでの表示用
     * @param $blog_id
     * @return array
     */
    public function getTemplateTags($blog_id): array
    {
        $options = [
            'fields' => 'id, name, count',
            'where' => 'blog_id=?',
            'params' => array($blog_id),
            'order' => 'count DESC, id DESC',
        ];
        return $this->find('all', $options);
    }
}
