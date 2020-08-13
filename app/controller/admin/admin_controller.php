<?php

require_once(\Fc2blog\Config::get('CONTROLLER_DIR') . 'app_controller.php');

abstract class AdminController extends AppController
{

  public function __construct($method)
  {
    parent::__construct($method);
  }

  protected function beforeFilter()
  {
    // 親のフィルター呼び出し
    parent::beforeFilter();

    if (!$this->isLogin()) {
      // 未ログイン時は新規登録とログイン以外させない
      $allows = array(
        'Users'  => array('login', 'register'),
        'Common' => array('lang', 'install', 'debug'),
      );
      $controller_name = \Fc2blog\Config::get('ControllerName');
      $action_name = \Fc2blog\Config::get('ActionName');
      if (!isset($allows[$controller_name]) || !in_array($action_name, $allows[$controller_name])) {
        $this->redirect(array('controller'=>'Users', 'action'=>'login'));
      }
      return ;
    }

    if (!$this->isSelectedBlog()) {
      // ブログ未選択時はブログの新規、編集、削除、一覧、選択以外させない
      $allows = array(
        'Users'  => array('logout'),
        'Blogs'  => array('index', 'create', 'delete', 'choice'),
        'Common' => array('lang', 'install'),
      );
      $controller_name = \Fc2blog\Config::get('ControllerName');
      $action_name = \Fc2blog\Config::get('ActionName');
      if (!isset($allows[$controller_name]) || !in_array($action_name, $allows[$controller_name])) {
        $this->setWarnMessage(__('Please select a blog'));
        $this->redirect(array('controller'=>'Blogs', 'action'=>'index'));
      }
      return ;
    }

    // ログイン中でかつブログ選択中の場合ブログ情報を取得し時間設定を行う
    $blog = $this->getBlog($this->getBlogId());
    if(is_array($blog) && isset($blog['timezone'])) {
      date_default_timezone_set($blog['timezone']);
    }
  }

  /**
  * ログイン処理
  */
  protected function loginProcess($user, $blog=null)
  {
    \Fc2blog\Session::regenerate();

    \Fc2blog\Session::set('user_id',   $user['id']);
    \Fc2blog\Session::set('login_id',  $user['login_id']);
    \Fc2blog\Session::set('user_type', $user['type']);

    if (!empty($blog)) {
      \Fc2blog\Session::set('blog_id',  $blog['id']);
      \Fc2blog\Session::set('nickname', $blog['nickname']);
    }
  }

  /**
  * ログイン状況
  */
  protected function isLogin()
  {
    return !!\Fc2blog\Session::get('user_id');
  }

  /**
  * ログイン中のIDを取得する
  */
  protected function getUserId()
  {
    return \Fc2blog\Session::get('user_id');
  }

  /**
  * ログイン中の名前を取得する
  */
  protected function getNickname()
  {
    return \Fc2blog\Session::get('nickname');
  }

  /**
  * ブログIDが設定中かどうか
  */
  protected function isSelectedBlog()
  {
    return !!\Fc2blog\Session::get('blog_id');
  }

  /**
  * 管理人かどうか
  */
  protected function isAdmin()
  {
    return \Fc2blog\Session::get('user_type') === \Fc2blog\Config::get('USER.TYPE.ADMIN');
  }

  /**
  * ブログIDを取得する
  */
  protected function getBlogId()
  {
    return \Fc2blog\Session::get('blog_id');
  }

  /**
  * ブログIDを設定する
  */
  protected function setBlog($blog=null)
  {
    if ($blog) {
      \Fc2blog\Session::set('nickname', $blog['nickname']);
      \Fc2blog\Session::set('blog_id',  $blog['id']);
    }else{
      \Fc2blog\Session::set('nickname', null);
      \Fc2blog\Session::set('blog_id',  null);
    }
  }

  /**
  * 情報用メッセージを設定する
  */
  protected function setInfoMessage($message)
  {
    $this->setMessage($message, 'flash-message-info');
  }

  /**
  * 警告用メッセージを設定する
  */
  protected function setWarnMessage($message)
  {
    $this->setMessage($message, 'flash-message-warn');
  }

  /**
  * エラー用メッセージを設定する
  */
  protected function setErrorMessage($message)
  {
    $this->setMessage($message, 'flash-message-error');
  }

  /**
  * メッセージを設定する
  */
  protected function setMessage($message, $type)
  {
    $messages = \Fc2blog\Session::get($type, array());
    $messages[] = $message;
    \Fc2blog\Session::set($type, $messages);
  }

  /**
  * メッセージ情報を削除し取得する
  */
  protected function removeMessage()
  {
    $messages = array();
    $messages['info'] = \Fc2blog\Session::remove('flash-message-info');
    $messages['warn'] = \Fc2blog\Session::remove('flash-message-warn');
    $messages['error'] = \Fc2blog\Session::remove('flash-message-error');
    return $messages;
  }

}

