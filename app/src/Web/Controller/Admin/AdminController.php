<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Config;
use Fc2blog\Web\Controller\AppController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

abstract class AdminController extends AppController
{

  public function __construct(Request $request, $method)
  {
    parent::__construct($request, $method);
  }

  protected function beforeFilter(Request $request)
  {
    // 親のフィルター呼び出し
    parent::beforeFilter($request);

    if (!$this->isLogin()) {
      // 未ログイン時は新規登録とログイン以外させない
      $allows = array(
        'Users'  => array('login', 'register'),
        'Common' => array('lang', 'install', 'debug'),
      );
      $controller_name = Config::get('ControllerName');
      $action_name = Config::get('ActionName');
      if (!isset($allows[$controller_name]) || !in_array($action_name, $allows[$controller_name])) {
        $this->redirect($request, array('controller'=>'Users', 'action'=>'login'));
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
      $controller_name = Config::get('ControllerName');
      $action_name = Config::get('ActionName');
      if (!isset($allows[$controller_name]) || !in_array($action_name, $allows[$controller_name])) {
        $this->setWarnMessage(__('Please select a blog'));
        $this->redirect($request, array('controller'=>'Blogs', 'action'=>'index'));
      }
      return ;
    }

    // ログイン中でかつブログ選択中の場合ブログ情報を取得し時間設定を行う
    $blog = $this->getBlog($this->getBlogId($request));
    if(is_array($blog) && isset($blog['timezone'])) {
      date_default_timezone_set($blog['timezone']);
    }
  }

  /**
   * ログイン処理
   * @param $user
   * @param null $blog
   */
  protected function loginProcess($user, $blog=null)
  {
    Session::regenerate();

    Session::set('user_id',   $user['id']);
    Session::set('login_id',  $user['login_id']);
    Session::set('user_type', $user['type']);

    if (!empty($blog)) {
      Session::set('blog_id',  $blog['id']);
      Session::set('nickname', $blog['nickname']);
    }
  }

  /**
  * ログイン状況
  */
  protected function isLogin()
  {
    return !!Session::get('user_id');
  }

  /**
  * ログイン中のIDを取得する
  */
  protected function getUserId()
  {
    return Session::get('user_id');
  }

  /**
  * ログイン中の名前を取得する
  */
  protected function getNickname()
  {
    return Session::get('nickname');
  }

  /**
  * ブログIDが設定中かどうか
  */
  protected function isSelectedBlog()
  {
    return !!Session::get('blog_id');
  }

  /**
  * 管理人かどうか
  */
  protected function isAdmin()
  {
    return Session::get('user_type') === Config::get('USER.TYPE.ADMIN');
  }

  /**
   * ブログIDを取得する
   * @param Request $request
   * @return mixed|null
   */
  protected function getBlogId(Request $request)
  {
    return Session::get('blog_id');
  }

  /**
   * ブログIDを設定する
   * @param null $blog
   */
  protected function setBlog($blog=null)
  {
    if ($blog) {
      Session::set('nickname', $blog['nickname']);
      Session::set('blog_id',  $blog['id']);
    }else{
      Session::set('nickname', null);
      Session::set('blog_id',  null);
    }
  }

  /**
   * 情報用メッセージを設定する
   * @param $message
   */
  protected function setInfoMessage($message)
  {
    $this->setMessage($message, 'flash-message-info');
  }

  /**
   * 警告用メッセージを設定する
   * @param $message
   */
  protected function setWarnMessage($message)
  {
    $this->setMessage($message, 'flash-message-warn');
  }

  /**
   * エラー用メッセージを設定する
   * @param $message
   */
  protected function setErrorMessage($message)
  {
    $this->setMessage($message, 'flash-message-error');
  }

  /**
   * メッセージを設定する
   * @param $message
   * @param $type
   */
  protected function setMessage($message, $type)
  {
    $messages = Session::get($type, array());
    $messages[] = $message;
    Session::set($type, $messages);
  }

  /**
  * メッセージ情報を削除し取得する
  */
  protected function removeMessage()
  {
    $messages = array();
    $messages['info'] = Session::remove('flash-message-info');
    $messages['warn'] = Session::remove('flash-message-warn');
    $messages['error'] = Session::remove('flash-message-error');
    return $messages;
  }

}

