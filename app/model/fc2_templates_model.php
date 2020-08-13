<?php
/**
* FC2テンプレートを取得する
* データソース元はAPIの予定
*/

class Fc2TemplatesModel extends \Fc2blog\Model\Model
{

  public static $instance = null;

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new Fc2TemplatesModel();
    }
    return self::$instance;
  }

  public function getTableName()
  {
    return 'fc2_templates';
  }

  /**
  * テンプレートを検索する
  * TODO:後で取得できなかった場合などの例外処理を入れる
  */
  public function getListAndPaging($condition)
  {
    $url = 'https://admin.blog.fc2.com/oss_api.php?action=template_search';
    $url .= '&page=' . $condition['page'];
    if (!empty($condition['device'])) {
      $url .= '&device=' . $condition['device'];
    }

    $json = file_get_contents($url);
    $json = json_decode($json, true);

    // ページング用変数装飾
    $paging = $json['pages'];

    $page = intval($paging['page']);
    $limit = intval($paging['limit']);
    $count = intval($paging['count']);

    $pages = array();
    $pages['count'] = $count;
    $pages['max_page'] = ceil($count / $limit);
    $pages['page'] = $page;
    $pages['is_next'] = $page < $pages['max_page'] - 1;
    $pages['is_prev'] = $page > 0;

    $json['pages'] = $pages;

    return $json;
  }

  /**
  * 単一テンプレートを取得する
  * TODO:後で取得できなかった場合などの例外処理を入れる
  */
  public function findByIdAndDevice($id, $device)
  {
    $url = 'https://admin.blog.fc2.com/oss_api.php?action=template_view&id=' . $id . '&device=' . $device;

    $json = file_get_contents($url);
    $json = json_decode($json, true);
    $json['id'] = $id;

    return $json;
  }

}

