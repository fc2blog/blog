<?php

require_once(\Fc2blog\Config::get('CONTROLLER_DIR') . 'admin/admin_controller.php');

class BlogsController extends AdminController
{

  /**
   * 一覧表示
   */
  public function index()
  {
    $request = Request::getInstance();
    \Fc2blog\Session::set('sig', App::genRandomString());

    // ブログの一覧取得
    $options = array(
      'where'  => 'user_id=?',
      'params' => array($this->getUserId()),
      'limit'  => \Fc2blog\Config::get('BLOG.DEFAULT_LIMIT', 10),
      'page'   => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
      'order'  => 'created_at DESC',
    );
    if (ceil(PHP_INT_MAX / $options['limit']) <= $options['page']) {
      $options['page'] = 0;
    }
    $blogs_model = Model::load('Blogs');
    $blogs = $blogs_model->find('all', $options);
    if ($blogs === false) $blogs = array();
    $paging = $blogs_model->getPaging($options);

    $this->set('blogs', $blogs);
    $this->set('paging', $paging);
  }

  /**
   * 新規作成
   */
  public function create()
  {
    $request = Request::getInstance();

    // 初期表示時
    if (!$request->get('blog') || !\Fc2blog\Session::get('sig') || \Fc2blog\Session::get('sig') !== $request->get('sig')) {
      \Fc2blog\Session::set('sig', App::genRandomString());
      return ;
    }

    $blogs_model = Model::load('Blogs');

    // 新規登録処理
    $errors = array();
    $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, array('id', 'name', 'nickname'));
    if (empty($errors['blog'])) {
      $blog_data['user_id'] = $this->getUserId();
      if ($id=$blogs_model->insert($blog_data)) {
        $this->setInfoMessage(__('I created a blog'));
        $this->redirect(array('action'=>'index'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
   * 編集
   */
  public function edit()
  {
    $request = Request::getInstance();
    /** @var BlogsModel $blogs_model */
    $blogs_model = Model::load('Blogs');

    $blog_id = $this->getBlogId();

    // 初期表示時に編集データの設定
    if (!$request->get('blog') || !\Fc2blog\Session::get('sig') || \Fc2blog\Session::get('sig') !== $request->get('sig')) {
      \Fc2blog\Session::set('sig', App::genRandomString());
      if (!$blog=$blogs_model->findById($blog_id)) {
        $this->redirect(array('action'=>'index'));
      }
      $request->set('blog', $blog);
      return ;
    }

    // 更新処理
    $white_list = array('name', 'introduction', 'nickname', 'timezone', 'blog_password', 'open_status', 'ssl_enable', 'redirect_status_code');
    $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, $white_list);
    if (empty($errors['blog'])){
      if ($blogs_model->updateById($blog_data, $blog_id)) {
        $this->setBlog(array('id'=>$blog_id, 'nickname'=>$blog_data['nickname']));    // ニックネームの更新
        $this->setInfoMessage(__('I updated a blog'));
        $this->redirect(array('action'=>'edit'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
  * ブログの切り替え
  */
  public function choice()
  {
    $request = Request::getInstance();

    $blog_id = $request->get('blog_id');

    // 切り替え先のブログの存在チェック
    $blog = Model::load('Blogs')->findByIdAndUserId($blog_id, $this->getUserId());
    if (!empty($blog)) {
      $this->setBlog($blog);
    }
    $this->redirect(\Fc2blog\Config::get('BASE_DIRECTORY'));   // トップページへリダイレクト
  }

  /**
   * 削除
   */
  public function delete()
  {
    $request = Request::getInstance();

    // 退会チェック
    if (!$request->get('blog.delete') || !\Fc2blog\Session::get('sig') || \Fc2blog\Session::get('sig') !== $request->get('sig')) {
      \Fc2blog\Session::set('sig', App::genRandomString());
      return ;
    }

    $blog_id = $this->getBlogId();
    $user_id = $this->getUserId();

    // 削除データの取得
    $blogs_model = Model::load('Blogs');
    if (!$blog=$blogs_model->findByIdAndUserId($blog_id, $user_id)) {
      $this->redirect(array('action'=>'index'));
    }

    // 削除処理
    $blogs_model->deleteByIdAndUserId($blog_id, $user_id);
    $this->setBlog(null);   // ログイン中のブログを削除したのでブログの選択中状態を外す
    $this->setInfoMessage(__('I removed the blog'));
    $this->redirect(array('action'=>'index'));
  }

}

