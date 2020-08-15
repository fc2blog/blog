<?php

namespace Fc2blog\Web\Controller\Admin;

class UsersController extends AdminController
{

  /**
   * 一覧表示(デバッグ用)
   */
  public function index()
  {
    if (!$this->isAdmin()) {
      return $this->error404();
    }

    $request = \Fc2blog\Request::getInstance();

    $options = array(
      'limit' => \Fc2blog\Config::get('PAGE.USER.LIMIT', 10),
      'page'  => $request->get('page', 0, \Fc2blog\Request::VALID_UNSIGNED_INT),
      'order' => 'id DESC',
    );
    $users_model = \Fc2blog\Model\Model::load('Users');
    $users = $users_model->find('all', $options);
    $paging = $users_model->getPaging($options);

    $this->set('users', $users);
    $this->set('paging', $paging);
  }

  /**
  * 新規作成
  */
  public function register()
  {
    if (\Fc2blog\Config::get('USER.REGIST_SETTING.FREE') != \Fc2blog\Config::get('USER.REGIST_STATUS')) {
      return $this->error404();
    }

    $request = \Fc2blog\Request::getInstance();

    // 初期表示時
    if (!$request->get('user')) {
      return ;
    }

    $users_model = \Fc2blog\Model\Model::load('Users');
    $blogs_model = \Fc2blog\Model\Model::load('Blogs');

    // ユーザーとブログの新規登録処理
    $errors = array();
    $errors['user'] = $users_model->registerValidate($request->get('user'), $user_data, array('login_id', 'password'));
    $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, array('id', 'name', 'nickname'));
    if (empty($errors['user']) && empty($errors['blog'])) {
      $blog_data['user_id'] = $users_model->insert($user_data);
      if ($blog_data['user_id'] && $blog_id=$blogs_model->insert($blog_data)) {
        $this->setInfoMessage(__('User registration is completed'));
        $this->redirect(array('action'=>'login'));
      } else {
        // ブログ作成失敗時には登録したユーザーを削除
        $users_model->deleteById($blog_data['user_id']);
      }
      $this->setErrorMessage(__('I failed to register'));
      return ;
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
  * ユーザー情報変更処理
  */
  public function edit()
  {
    $request = \Fc2blog\Request::getInstance();
    $users_model = \Fc2blog\Model\Model::load('Users');

    $user_id = $this->getUserId();

    // 初期表示時に編集データの取得&設定
    if (!$request->get('user')) {
      $user = $users_model->findById($user_id);
      unset($user['password']);
      $request->set('user', $user);
      return ;
    }

    // 更新処理
    $errors = array();
    $white_list = array('password', 'login_blog_id');
    $errors['user'] = $users_model->updateValidate($request->get('user'), $data_user, $white_list);
    if (empty($errors['user'])) {
      if ($users_model->updateById($data_user, $user_id)) {
        $this->setInfoMessage(__('Update User Information'));
        $this->redirect(array('action'=>'edit'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
   * 退会
   */
  public function withdrawal()
  {
    $request = \Fc2blog\Request::getInstance();

    // 退会チェック
    if (!$request->get('user.delete')) {
      return ;
    }

    // 削除処理
    \Fc2blog\Model\Model::load('Users')->deleteById($this->getUserId());
    $this->setInfoMessage(__('Was completed withdrawal'));
    $this->logout();
  }

  /**
  * ログイン
  */
  public function login()
  {
    if ($this->isLogin()) {
      $this->redirect(\Fc2blog\Config::get('BASE_DIRECTORY'));   // トップページへリダイレクト
    }

    $request = \Fc2blog\Request::getInstance();

    // 初期表示時
    if (!$request->get('user')) {
      return ;
    }

    $users_model = \Fc2blog\Model\Model::load('Users');

    // ログインフォームのバリデート
    $errors = $users_model->loginValidate($request->get('user'), $data, array('login_id', 'password'));
    if (empty($errors)) {
      $user = $users_model->findByLoginIdAndPassword($data['login_id'], $data['password']);
      if ($user) {
        // ログイン処理
        $blog = \Fc2blog\Model\Model::load('Blogs')->getLoginBlog($user);
        $this->loginProcess($user, $blog);
        $users_model->updateById(array('logged_at'=>date('Y-m-d H:i:s')), $user['id']);
        if (!$this->isSelectedBlog()) {
          $this->redirect(array('controller'=>'Blogs', 'action'=>'create'));
        }
        $this->redirect(\Fc2blog\Config::get('BASE_DIRECTORY'));   // トップページへリダイレクト
      }
      $errors = array('login_id' => __('Login ID or password is incorrect'));
    }
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
  * ログアウト
  */
  public function logout()
  {
    if ($this->isLogin()) {
      \Fc2blog\Web\Session::destroy();
    }
    $this->redirect(array('action'=>'login'));
  }

}

