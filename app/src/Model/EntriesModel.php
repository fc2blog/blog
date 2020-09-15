<?php

namespace Fc2blog\Model;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Web\Request;

class EntriesModel extends Model
{

  public static $instance = null;

  public function __construct()
  {
  }

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new EntriesModel();
    }
    return self::$instance;
  }

  public function getTableName(): string
  {
    return 'entries';
  }

  public function getAutoIncrementCompositeKey(): string
  {
    return 'blog_id';
  }

  /**
   * バリデート処理
   * @param $data
   * @param $valid_data
   * @param array $white_list
   * @return array
   */
  public function validate($data, &$valid_data, $white_list = []): array
  {
    // バリデートを定義
    $this->validates = array(
      'title' => array(
        'required' => true,
        'maxlength' => array('max' => 100),
      ),
      'body' => array(
        'maxlength' => array('max' => 50000),
      ),
      'extend' => array(
        'maxlength' => array('max' => 50000),
      ),
      'tag' => array(
        'trim' => true,
        'maxlength' => array('max' => 255),
      ),
      'open_status' => array(
        'default_value' => Config::get('ENTRY.OPEN_STATUS.OPEN'),
        'in_array' => array('values' => array_keys(self::getOpenStatusList())),
      ),
      'password' => array(
        'maxlength' => array('max' => 50),
      ),
      'auto_linefeed' => array(
        'default_value' => Config::get('ENTRY.AUTO_LINEFEED.USE'),
        'in_array' => array('values' => array_keys(self::getAutoLinefeedList())),
      ),
      'comment_accepted' => array(
        'default_value' => Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED'),
        'in_array' => array('values' => array_keys(self::getCommentAcceptedList())),
      ),
      'posted_at' => array(
        'default_value' => date('Y-m-d H:i:s'),
        'datetime' => array(),
      ),
    );

    return parent::validate($data, $valid_data, $white_list);
  }

  public static function getOpenStatusList()
  {
    return array(
      Config::get('ENTRY.OPEN_STATUS.OPEN') => __('Publication'),
      Config::get('ENTRY.OPEN_STATUS.DRAFT') => __('Draft'),
      Config::get('ENTRY.OPEN_STATUS.PASSWORD') => __('Password Protection'),
      Config::get('ENTRY.OPEN_STATUS.LIMIT') => __('Limited time offer'),
      Config::get('ENTRY.OPEN_STATUS.RESERVATION') => __('Reservations Posts'),
    );
  }

  public static function getCommentAcceptedList()
  {
    return array(
      Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED') => __('Be accepted'),
      Config::get('ENTRY.COMMENT_ACCEPTED.REJECT') => __('Reject'),
    );
  }

  public static function getAutoLinefeedList()
  {
    return array(
      Config::get('ENTRY.AUTO_LINEFEED.USE') => __('I do automatic line feed'),
      Config::get('ENTRY.AUTO_LINEFEED.NONE') => __('HTML tags only a new line'),
    );
  }

  /**
   * アーカイブ一覧を取得
   * FC2テンプレートでの表示用
   * @param $blog_id
   * @return mixed
   */
  public function getArchives($blog_id)
  {
    // 表示項目リスト
    $open_status_list = array(
      Config::get('ENTRY.OPEN_STATUS.OPEN'),      // 公開
      Config::get('ENTRY.OPEN_STATUS.PASSWORD'),  // パスワード保護
      Config::get('ENTRY.OPEN_STATUS.LIMIT'),     // 期間限定
    );
    $add_where = ' AND open_status IN (' . implode(',', $open_status_list) . ')';

    $sql = <<<SQL
      SELECT
        DATE_FORMAT(posted_at, '%Y') as year,
        DATE_FORMAT(posted_at, '%m') as month,
        COUNT(*) as count
      FROM entries
      WHERE blog_id=? {$add_where}
      GROUP BY year, month
      ORDER BY concat(year, month) DESC;
    SQL;
    $params = array($blog_id);
    $options = array('result' => DBInterface::RESULT_ALL);
    return $this->findSql($sql, $params, $options);
  }

  /**
   * タグ、カテゴリーも含んだ記事を取得
   * @param $id
   * @param $blog_id
   * @return array|mixed
   */
  public function getEntry($id, $blog_id)
  {
    // 記事詳細取得
    $entry = $this->findByIdAndBlogId($id, $blog_id);
    if (!$entry) {
      return array();
    }

    if ($entry['open_status'] == Config::get('ENTRY.OPEN_STATUS.DRAFT')
      || $entry['open_status'] == Config::get('ENTRY.OPEN_STATUS.RESERVATION')
    ) {
      return array();
    }

    // 記事のカテゴリ一覧を取得 TODO:後でcacheを使用する形に
    $entry['categories'] = Model::load('Categories')->getEntryCategories($entry['blog_id'], $entry['id']);
    $entry['tags'] = Model::load('Tags')->getEntryTags($entry['blog_id'], $entry['id']);

    return $entry;
  }

  /**
   * 次の記事取得
   * @param $entry
   * @return mixed
   */
  public function nextEntry($entry)
  {
    $where = 'blog_id=? AND (posted_at>? OR (posted_at=? AND id>?))';
    $params = array($entry['blog_id'], $entry['posted_at'], $entry['posted_at'], $entry['id']);
    $options = array(
      'fields' => array('id', 'title'),
      'where' => $where,
      'params' => $params,
      'order' => 'posted_at ASC, id ASC'
    );

    // 表示項目リスト
    $open_status_list = array(
      Config::get('ENTRY.OPEN_STATUS.OPEN'),      // 公開
      Config::get('ENTRY.OPEN_STATUS.PASSWORD'),  // パスワード保護
      Config::get('ENTRY.OPEN_STATUS.LIMIT'),     // 期間限定
    );
    $options['where'] .= ' AND entries.open_status IN (' . implode(',', $open_status_list) . ')';

    return $this->find('row', $options);
  }

  /**
   * 前の記事取得
   * @param $entry
   * @return mixed
   */
  public function prevEntry($entry)
  {
    $where = 'blog_id=? AND (posted_at<? OR (posted_at=? AND id<?))';
    $params = array($entry['blog_id'], $entry['posted_at'], $entry['posted_at'], $entry['id']);
    $options = array(
      'fields' => array('id', 'title'),
      'where' => $where,
      'params' => $params,
      'order' => 'posted_at DESC, id DESC'
    );

    // 表示項目リスト
    $open_status_list = array(
      Config::get('ENTRY.OPEN_STATUS.OPEN'),      // 公開
      Config::get('ENTRY.OPEN_STATUS.PASSWORD'),  // パスワード保護
      Config::get('ENTRY.OPEN_STATUS.LIMIT'),     // 期間限定
    );
    $options['where'] .= ' AND entries.open_status IN (' . implode(',', $open_status_list) . ')';

    return $this->find('row', $options);
  }

  /**
   * コメント受付中の記事取得
   * @param $entry_id
   * @param $blog_id
   * @return array|mixed
   */
  public function getCommentAcceptedEntry($entry_id, $blog_id)
  {
    return $this->findByIdAndBlogId($entry_id, $blog_id,
      array('where' => 'comment_accepted=' . Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED')));
  }

  public function insert($data, $options = array())
  {
    // 最初に登場する画像を設定
    $data['first_image'] = $this->getFirstImage($data);

    // 登録日時を設定
    $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');

    $flag = parent::insert($data, $options);

    if ($flag && isset($data['blog_id'])) {
      Model::load('Blogs')->updateLastPostedAt($data['blog_id']);
    }
    return $flag;
  }

  public function updateByIdAndBlogId($data, $comment_id, $blog_id, $options = array())
  {
    // 最初に登場する画像を設定
    $data['first_image'] = $this->getFirstImage($data);

    // 更新日時を設定
    $data['updated_at'] = date('Y-m-d H:i:s');

    return parent::updateByIdAndBlogId($data, $comment_id, $blog_id, $options);
  }

  /**
   * 本文から最初に登場する画像を取得する
   * @param $data
   * @return mixed|string
   */
  private function getFirstImage($data)
  {
    $html = $data['body'];
//    $html = $data['body'] . $data['extend'];
    if (preg_match('/<img [^>]*?src=["\' ](.*?)["\' ]/i', $html, $matches)) {
      return $matches[1];
    }
    return '';
  }

  /**
   * idとblog_idをキーとした削除 + 付随情報も削除
   * @param $entry_id
   * @param $blog_id
   * @param array $options
   * @return array|false|int|mixed
   */
  public function deleteByIdAndBlogId($entry_id, $blog_id, $options = array())
  {
    // コメント削除
    Model::load('Comments')->deleteEntryRelation($blog_id, $entry_id);

    // カテゴリー削除
    Model::load('EntryCategories')->deleteEntryRelation($blog_id, $entry_id);

    // タグ削除
    Model::load('EntryTags')->deleteEntryRelation($blog_id, $entry_id);

    // 記事本体削除
    return parent::deleteByIdAndBlogId($entry_id, $blog_id, $options);
  }

  /**
   * idとblog_idをキーとした削除 + 付随情報も削除
   * @param array $ids
   * @param $blog_id
   * @param array $options
   * @return bool
   */
  public function deleteByIdsAndBlogId($ids, $blog_id, $options = array())
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
   * 予約投稿エントリーの更新
   * @param null $blog_id
   */
  public function updateReservation($blog_id = null)
  {
    $where = '';
    $params = array();
    if (!empty($blog_id)) {
      $where .= 'blog_id=? AND ';
      $params[] = $blog_id;
    }
    $where .= ' open_status=' . Config::get('ENTRY.OPEN_STATUS.RESERVATION') . ' ';
    $where .= " AND posted_at <= '" . date('Y-m-d H:i:s') . "'";

    $options = array('where' => $where, 'params' => $params);
    $count = $this->find('count', $options);
    if (!$count) {
      // 該当ファイル無し
      return;
    }

    // 予約投稿のエントリーを公開に変更
    $data = array('open_status' => Config::get('ENTRY.OPEN_STATUS.OPEN'));
    $this->update($data, $where, $params);
  }

  /**
   * 期間限定エントリーの更新
   * @param null $blog_id
   */
  public function updateLimited($blog_id = null)
  {
    $where = '';
    $params = array();
    if (!empty($blog_id)) {
      $where .= 'blog_id=? AND ';
      $params[] = $blog_id;
    }
    $where .= ' open_status=' . Config::get('ENTRY.OPEN_STATUS.LIMIT') . ' ';
    $where .= " AND posted_at <= '" . date('Y-m-d H:i:s') . "'";

    $options = array('where' => $where, 'params' => $params);
    $count = $this->find('count', $options);
    if (!$count) {
      // 該当ファイル無し
      return;
    }

    // 期間限定投稿のエントリーを下書きに変更
    $data = array('open_status' => Config::get('ENTRY.OPEN_STATUS.DRAFT'));
    $this->update($data, $where, $params);
  }

  /**
   * コメント件数を増加させる処理
   * @param $blog_id
   * @param $entry_id
   * @return array|false|int|mixed
   */
  public function increaseCommentCount($blog_id, $entry_id)
  {
    $sql = 'UPDATE ' . $this->getTableName() . ' SET comment_count=comment_count+1 WHERE blog_id=? AND id=?';
    $params = array($blog_id, $entry_id);
    $options['result'] = DBInterface::RESULT_SUCCESS;
    return $this->executeSql($sql, $params, $options);
  }

  /**
   * コメント件数を減少させる処理
   * @param $blog_id
   * @param $entry_id
   * @return array|false|int|mixed
   */
  public function decreaseCommentCount($blog_id, $entry_id)
  {
    $sql = 'UPDATE ' . $this->getTableName() . ' SET comment_count=comment_count-1 WHERE blog_id=? AND id=? AND comment_count>0';
    $params = array($blog_id, $entry_id);
    $options['result'] = DBInterface::RESULT_SUCCESS;
    return $this->executeSql($sql, $params, $options);
  }

  /**
   * 最近の記事一覧を取得
   * FC2テンプレートでの表示用
   * @param Request $request
   * @param $blog_id
   * @return mixed
   */
  public function getTemplateRecents(Request $request, $blog_id)
  {
    $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);
    $options = array(
      'where' => 'blog_id=?',
      'params' => array($blog_id),
      'limit' => $blog_setting['entry_recent_display_count'],
      'order' => 'entries.posted_at DESC, entries.id DESC',
    );

    // 表示項目リスト
    $open_status_list = array(
      Config::get('ENTRY.OPEN_STATUS.OPEN'),      // 公開
      Config::get('ENTRY.OPEN_STATUS.PASSWORD'),  // パスワード保護
      Config::get('ENTRY.OPEN_STATUS.LIMIT'),     // 期間限定
    );
    $options['where'] .= ' AND entries.open_status IN (' . implode(',', $open_status_list) . ')';
    $entries = $this->find('all', $options);

    // テンプレート用変数追加
    foreach ($entries as $key => $value) {
      $entries[$key]['title'] = strip_tags($value['title']);
      $entries[$key]['link'] = App::userURL($request, array('controller' => 'Entries', 'action' => 'view', 'blog_id' => $value['blog_id'], 'id' => $value['id']));

      list($entries[$key]['year'], $entries[$key]['month'], $entries[$key]['day'],
        $entries[$key]['hour'], $entries[$key]['minute'], $entries[$key]['second'], $entries[$key]['youbi'], $entries[$key]['month_short']
        ) = explode('/', date('Y/m/d/H/i/s/D/M', strtotime($value['posted_at'])));
      $entries[$key]['wayoubi'] = __($entries[$key]['youbi']);
    }

    return $entries;
  }

  /**
   * テンプレート表示用のカレンダーデータを取得
   * @param Request $request
   * @param $blog_id
   * @param null $year
   * @param null $month
   * @return array
   */
  public function getTemplateCalendar(Request $request, $blog_id, $year = null, $month = null)
  {
    $year = $year == null ? date('Y') : $year;
    $month = $month == null ? date('m') : $month;
    $timestamp = strtotime($year . '-' . $month . '-01 00:00:00');
    $options = array(
      'fields' => 'DAY(posted_at) as day',
      'where' => 'blog_id=? AND ? <= posted_at AND posted_at <= ?',
      'params' => array($blog_id, date('Y-m-01 00:00:00', $timestamp), date('Y-m-t 23:59:59', $timestamp)),
      'group' => 'DAY(posted_at), posted_at',
      'order' => 'posted_at ASC',
    );
    $days = $this->find('all', $options);

    $exist_days = array();
    foreach ($days as $day) {
      $exist_days[] = $day['day'];
    }

    $calendar = array();
    $first_day = date('w', $timestamp);

    $last_day = date('t', $timestamp);
    for ($i = 1 - $first_day, $c = 0; $i <= $last_day; $i += 7, $c++) {
      $calendar[$c] = array();
      for ($j = 0; $j < 7; $j++) {
        $day = $i + $j;
        if ($day < 1 || $last_day < $day) {
          $calendar[$c][] = '-';
          continue;
        }
        if (!in_array($day, $exist_days)) {
          $calendar[$c][] = $day;
          continue;
        }
        $calendar[$c][] = '<a href="' . App::userURL($request, array('controller' => 'entries', 'action' => 'date',
            'blog_id' => $blog_id, 'date' => sprintf('%04d%02d%02d', $year, $month, $day))) . '">' . $day . '</a>';
      }
    }

    return $calendar;
  }

  /**
   * テスト用、全記事を取得
   * @param string $blog_id
   * @return array
   */
  public function forTestGetAll(string $blog_id): array
  {
    $options = [
      'where' => 'blog_id=?',
      'params' => [$blog_id],
      'order' => 'id',
    ];

    return $this->find('all', $options);
  }
}
