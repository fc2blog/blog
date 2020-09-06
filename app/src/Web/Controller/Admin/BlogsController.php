<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\Model;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

class BlogsController extends AdminController
{

  /**
   * 一覧表示
   * @param Request $request
   */
  public function index(Request $request)
  {
    Session::set('sig', App::genRandomString());

    // ブログの一覧取得
    $options = array(
      'where' => 'user_id=?',
      'params' => array($this->getUserId()),
      'limit' => Config::get('BLOG.DEFAULT_LIMIT', 10),
      'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
      'order' => 'created_at DESC',
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
   * @param Request $request
   */
  public function create(Request $request)
  {
    // 初期表示時
    if (!$request->get('blog') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      Session::set('sig', App::genRandomString());
      return;
    }

    /** @var BlogsModel $blogs_model */
    $blogs_model = Model::load('Blogs');

    // 新規登録処理
    $errors = array();
    $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, array('id', 'name', 'nickname'));
    if (empty($errors['blog'])) {
      $blog_data['user_id'] = $this->getUserId();
      if ($id = $blogs_model->insert($blog_data)) {
        $this->setInfoMessage(__('I created a blog'));
        $this->redirect($request, array('action' => 'index'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
   * 編集
   * @param Request $request
   * @return string
   */
  public function edit(Request $request): string
  {
    $blogs_model = new BlogsModel();

    $blog_id = $this->getBlogId($request);
    $this->set('open_status_list', BlogsModel::getOpenStatusList());
    $this->set('time_zone_list', BlogsModel::getTimezoneList());
    $this->set('ssl_enable_settings_list', BlogsModel::getSSLEnableSettingList());
    $this->set('redirect_status_code_settings_list', BlogsModel::getRedirectStatusCodeSettingList());
    $this->set('tab', 'blog_edit');

    // 初期表示時に編集データの設定
    if (!$request->get('blog') || !$request->isValidSig()) {
      $request->generateNewSig();
      if (!$blog = $blogs_model->findById($blog_id)) {
        $this->redirect($request, ['action' => 'index']);
      }
      $request->set('blog', $blog);
      return "admin/blogs/edit.twig";
    }

    // 更新処理
    $white_list = ['name', 'introduction', 'nickname', 'timezone', 'blog_password', 'open_status', 'ssl_enable', 'redirect_status_code'];
    $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, $white_list);
    if (empty($errors['blog'])) {
      if ($blogs_model->updateById($blog_data, $blog_id)) {
        $this->setBlog(['id' => $blog_id, 'nickname' => $blog_data['nickname']]); // ニックネームの更新
        $this->setInfoMessage(__('I updated a blog'));
        $this->redirect($request, ['action' => 'edit']);
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);

    return "admin/blogs/edit.twig";
  }

  /**
   * ブログの切り替え
   * @param Request $request
   */
  public function choice(Request $request)
  {
    $blog_id = $request->get('blog_id');

    // 切り替え先のブログの存在チェック
    $blog = Model::load('Blogs')->findByIdAndUserId($blog_id, $this->getUserId());
    if (!empty($blog)) {
      $this->setBlog($blog);
    }
    $this->redirect($request, Config::get('BASE_DIRECTORY'));   // トップページへリダイレクト
  }

  /**
   * 削除
   * @param Request $request
   */
  public function delete(Request $request)
  {
    // 退会チェック
    if (!$request->get('blog.delete') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      Session::set('sig', App::genRandomString());
      return;
    }

    $blog_id = $this->getBlogId($request);
    $user_id = $this->getUserId();

    // 削除データの取得
    $blogs_model = Model::load('Blogs');
    if (!$blog = $blogs_model->findByIdAndUserId($blog_id, $user_id)) {
      $this->redirect($request, array('action' => 'index'));
    }

    // 削除処理
    $blogs_model->deleteByIdAndUserId($blog_id, $user_id);
    $this->setBlog(null);   // ログイン中のブログを削除したのでブログの選択中状態を外す
    $this->setInfoMessage(__('I removed the blog'));
    $this->redirect($request, array('action' => 'index'));
  }

}

