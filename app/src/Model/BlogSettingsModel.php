<?php

namespace Fc2blog\Model;

use Fc2blog\Config;

class BlogSettingsModel extends Model
{

  public $validates = array();

  public static $instance = null;

  public function __construct()
  {
    // フィールドで定義できない部分をコンストラクタで設定(Configの設定)
    $this->validates = array(
      // コメントの確認設定
      'comment_confirm' => array(
        'default_value' => Config::get('COMMENT.COMMENT_CONFIRM.THROUGH'),
        'in_array' => array('values' => array(
          Config::get('COMMENT.COMMENT_CONFIRM.THROUGH'),
          Config::get('COMMENT.COMMENT_CONFIRM.CONFIRM'),
        )),
      ),
      'comment_display_approval' => array(
        'default_value' => Config::get('COMMENT.COMMENT_DISPLAY.SHOW'),
        'in_array' => array('values' => array(
          Config::get('COMMENT.COMMENT_DISPLAY.SHOW'),
          Config::get('COMMENT.COMMENT_DISPLAY.HIDE'),
        )),
      ),
      'comment_display_private' => array(
        'default_value' => Config::get('COMMENT.COMMENT_DISPLAY.SHOW'),
        'in_array' => array('values' => array(
          Config::get('COMMENT.COMMENT_DISPLAY.SHOW'),
          Config::get('COMMENT.COMMENT_DISPLAY.HIDE'),
        )),
      ),
      'comment_cookie_save' => array(
        'default_value' => Config::get('COMMENT.COMMENT_COOKIE_SAVE.SAVE'),
        'in_array' => array('values' => array(
          Config::get('COMMENT.COMMENT_COOKIE_SAVE.NOT_SAVE'),
          Config::get('COMMENT.COMMENT_COOKIE_SAVE.SAVE'),
        )),
      ),
      'comment_captcha' => array(
        'default_value' => Config::get('COMMENT.COMMENT_CAPTCHA.USE'),
        'in_array' => array('values' => array(
          Config::get('COMMENT.COMMENT_CAPTCHA.NOT_USE'),
          Config::get('COMMENT.COMMENT_CAPTCHA.USE'),
        )),
      ),
      'comment_display_count' => array(
        'required' => true,
        'numeric' => true,
        'min' => array('min' => 1),
        'max' => array('max' => 50),
      ),
      'comment_order' => array(
        'in_array' => array('values' => array_keys($this->getCommentOrderList())),
      ),
      'comment_quote' => array(
        'in_array' => array('values' => array_keys($this->getCommentQuoteList())),
      ),
      'entry_recent_display_count' => array(
        'required' => true,
        'numeric' => true,
        'min' => array('min' => 1),
        'max' => array('max' => 50),
      ),
      'entry_display_count' => array(
        'required' => true,
        'numeric' => true,
        'min' => array('min' => 1),
        'max' => array('max' => 50),
      ),
      'entry_order' => array(
        'in_array' => array('values' => array_keys($this->getEntryOrderList())),
      ),
      'entry_password' => array(
        'maxlength' => array('max' => 50),
      ),
      'start_page' => array(
        'in_array' => array('values' => array_keys($this->getStartPageList())),
      ),
    );
  }

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new BlogSettingsModel();
    }
    return self::$instance;
  }

  public function getTableName(): string
  {
    return 'blog_settings';
  }

  public static function getCommentConfirmList()
  {
    return array(
      Config::get('COMMENT.COMMENT_CONFIRM.THROUGH') => __('Displayed as it is'),
      Config::get('COMMENT.COMMENT_CONFIRM.CONFIRM') => __('I check the comments'),
    );
  }

  public static function getCommentDisplayApprovalList()
  {
    return array(
      Config::get('COMMENT.COMMENT_DISPLAY.SHOW') => __('View "This comment is awaiting moderation" and'),
      Config::get('COMMENT.COMMENT_DISPLAY.HIDE') => __('Do not show'),
    );
  }

  public static function getCommentDisplayPrivateList()
  {
    return array(
      Config::get('COMMENT.COMMENT_DISPLAY.SHOW') => __('Display'),
      Config::get('COMMENT.COMMENT_DISPLAY.HIDE') => __('Do not show'),
    );
  }

  public static function getCommentCookieSaveList()
  {
    return array(
      Config::get('COMMENT.COMMENT_COOKIE_SAVE.NOT_SAVE') => __('Not save'),
      Config::get('COMMENT.COMMENT_COOKIE_SAVE.SAVE') => __('Record'),
    );
  }

  public static function getCommentCaptchaList()
  {
    return array(
      Config::get('COMMENT.COMMENT_CAPTCHA.NOT_USE') => __('Do not use'),
      Config::get('COMMENT.COMMENT_CAPTCHA.USE') => __('CAPTCHA to Use'),
    );
  }

  public static function getCommentOrderList()
  {
    return array(
      Config::get('COMMENT.ORDER.ASC') => __('Oldest First'),
      Config::get('COMMENT.ORDER.DESC') => __('Latest order'),
    );
  }

  public static function getCommentQuoteList()
  {
    return array(
      Config::get('COMMENT.QUOTE.USE') => __('Quote'),
      Config::get('COMMENT.QUOTE.NONE') => __('Do not quote'),
    );
  }

  public static function getEntryOrderList()
  {
    return array(
      Config::get('ENTRY.ORDER.DESC') => __('Latest order'),
      Config::get('ENTRY.ORDER.ASC') => __('Oldest First'),
    );
  }

  public static function getStartPageList()
  {
    return array(
      Config::get('BLOG.START_PAGE.NOTICE') => __('Notice'),
      Config::get('BLOG.START_PAGE.ENTRY') => __('New article'),
    );
  }

  /**
   * 主キーをキーにしてデータを取得
   * @param $blog_id
   * @param array $options
   * @return mixed
   */
  public function findByBlogId($blog_id, $options = array())
  {
    $options['where'] = isset($options['where']) ? 'blog_id=? AND ' . $options['where'] : 'blog_id=?';
    $options['params'] = isset($options['params']) ? array_merge(array($blog_id), $options['params']) : array($blog_id);
    return $this->find('row', $options);
  }


  /**
   * idをキーとした更新
   * @param $values
   * @param $blog_id
   * @param array $options
   * @return array|false|int|mixed
   */
  public function updateByBlogId($values, $blog_id, $options = array())
  {
    return $this->update($values, 'blog_id=?', array($blog_id), $options);
  }

  /**
   * コメント返信の表示タイプ更新
   * @param $device_type
   * @param $reply_type
   * @param $blog_id
   * @return array|false|int|mixed
   */
  public function updateReplyType($device_type, $reply_type, $blog_id)
  {
    $values = array();
    $values[Config::get('BLOG_TEMPLATE_REPLY_TYPE_COLUMN.' . $device_type)] = $reply_type;
    return $this->updateByBlogId($values, $blog_id);
  }
}
