<?php

namespace Fc2blog\Model;

class EntryTagsModel extends \Fc2blog\Model\Model
{

  public static $instance = null;

  public function __construct(){}

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new EntryTagsModel();
    }
    return self::$instance;
  }

  public function getTableName()
  {
    return 'entry_tags';
  }

  /**
  * バリデート処理
  */
  public function validate($data, &$valid_data, $white_list=array())
  {
    // バリデートを定義
    $this->validates = array(
      'tag_id' => array(
        'multiple' => array(
          'int' => true,            // int型にキャスト
          'min' => array('min'=>0), // 0以上
        ),
        'array_unique' => true,     // 重複排除
      ),
    );

    return parent::validate($data, $valid_data, $white_list);
  }

  /**
  * 記事のタグID一覧を取得
  */
  public function getTagIds($blog_id, $entry_id)
  {
    $tags = $this->find('all', array(
      'fields' => 'tag_id',
      'where'  => 'blog_id=? AND entry_id=?',
      'params' => array($blog_id, $entry_id)
    ));
    $ids = array();
    foreach($tags as $tag) {
      $ids[] = $tag['tag_id'];
    }
    return $ids;
  }

  /**
  * 記事とタグの紐付けを保存する
  * タグの記事数の変動も行う
  */
  public function save($blog_id, $entry_id, $tags)
  {
    $tags_model = \Fc2blog\Model\Model::load('Tags');

    // 入力値整形処理
    if (!is_array($tags)) {
      // 配列でない場合はデータ無しとして扱う
      $tags = array();
    }
    $temp_tags = array();
    foreach($tags as $key => $tag){
      if (is_string($tag) && $tag!='') {
        // 文字列のみ許可
        $temp_tags[] = $tag;
      }
    }
    $tags = array_unique($temp_tags);

    $now_tags = $tags_model->getListByNames($blog_id, $tags);
    $now_tag_values = array_values($now_tags);

    // 存在していないタグを作成
    foreach ($tags as $tag) {
      if ($tag!=='' && !in_array($tag, $now_tag_values)) {
        // タグが存在していないので作成
        $data_tag = array(
          'blog_id' => $blog_id,
          'name'    => $tag,
          'count'   => 0,
        );
        $tag_id = $tags_model->insert($data_tag);
        $now_tags[$tag_id] = $tag;
      }
    }

    // 登録済みのタグを取得
    $tag_ids = array_keys($now_tags);
    $now_tag_ids = $this->getTagIds($blog_id, $entry_id);

    $delete_ids = array_diff($now_tag_ids, $tag_ids);
    $insert_ids = array_diff($tag_ids, $now_tag_ids);

    $ret = true;

    // 削除された紐付け分件数を減らす
    if (count($delete_ids)) {
      $ret = $ret && $this->delete('blog_id=? AND entry_id=? AND tag_id IN (' . implode(',', $delete_ids) . ')', array($blog_id, $entry_id));
      // タグの記事件数減少処理
      $tags_model->decreaseCount($blog_id, $delete_ids);
    }

    // 新たに追加する紐付け分件数を増やす
    if (count($insert_ids)) {
      $columns = array('blog_id', 'entry_id', 'tag_id');
      $values = array();
      foreach($insert_ids as $tag_id){
        $values[] = $blog_id;
        $values[] = $entry_id;
        $values[] = $tag_id;
      }
      $ret = $ret && $this->multipleInsert($columns, $values);
      // タグの記事数増加処理
      $tags_model->increaseCount($blog_id, $insert_ids);
    }

    return $ret;
  }

  /**
  * 記事に付随する情報を削除
  */
  public function deleteEntryRelation($blog_id, $entry_id)
  {
    $tag_ids = $this->getTagIds($blog_id, $entry_id);
    if (count($tag_ids)) {
      \Fc2blog\Model\Model::load('Tags')->decreaseCount($blog_id, $tag_ids);
    }
    return $this->delete('blog_id=? AND entry_id=?', array($blog_id, $entry_id));
  }

}

