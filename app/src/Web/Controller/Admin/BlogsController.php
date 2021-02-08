<?php

namespace Fc2blog\Web\Controller\Admin;

use Exception;
use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\Model;
use Fc2blog\Web\Request;
use Tuupola\Base62Proxy;

class BlogsController extends AdminController
{

  /**
   * 一覧表示
   * @param Request $request
   * @return string
   */
  public function index(Request $request): string
  {
    $request->generateNewSig();

    // ブログの一覧取得
    $options = [
      'where' => 'user_id=?',
      'params' => [$this->getUserId()],
      'limit' => Config::get('BLOG.DEFAULT_LIMIT', 10),
      'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
      'order' => 'created_at DESC',
    ];
    if (ceil(PHP_INT_MAX / $options['limit']) <= $options['page']) {
      $options['page'] = 0;
    }
    $blogs_model = new BlogsModel();
    $blogs = $blogs_model->find('all', $options);
    if ($blogs === false) $blogs = [];
    $paging = $blogs_model->getPaging($options);

    $this->set('blogs', $blogs);
    $this->set('paging', $paging);
    return 'admin/blogs/index.twig';
  }

  /**
   * 新規作成
   * @param Request $request
   * @return string
   * @throws Exception
   */
  public function create(Request $request): string
  {
    // 初期表示時
    if (!$request->get('blog') || !$request->isValidSig()) {
      $request->generateNewSig();
      return 'admin/blogs/create.twig';
    }

    $blogs_model = new BlogsModel();

    // 新規登録処理
    $errors = [];
    $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, ['id', 'name', 'nickname']);
    if (empty($errors['blog'])) {
      $blog_data['user_id'] = $this->getUserId();
      $blog_data['trip_salt'] = Base62Proxy::encode(random_bytes(128));
      if ($id = $blogs_model->insert($blog_data)) {
        $this->setInfoMessage(__('I created a blog'));
        $this->redirect($request, ['action' => 'index']);
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
    return 'admin/blogs/create.twig';
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
    $errors['blog'] = $blogs_model->validate(
      // バリデーションのために、blog_idを引き回している。バリデーションを作り変えたい
      array_merge($request->get('blog'), ["_blog_id"=>$blog_id]),
      $blog_data,
      $white_list
    );
    if (empty($errors['blog'])) {
      // パスワード空欄なら、パスワードを更新しない
      if (strlen($blog_data['blog_password']) > 0) {
        $blog_data['blog_password'] = password_hash($blog_data['blog_password'], PASSWORD_DEFAULT);
      }else{
        $blog_data['blog_password'] = ($blogs_model->findById($blog_id))['blog_password'];
      }
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
    $this->redirect($request, $request->baseDirectory);   // トップページへリダイレクト
  }

  /**
   * 削除
   * @param Request $request
   * @return string
   */
  public function delete(Request $request): string
  {
    $this->set('tab', 'blog_delete');
    // 退会チェック
    if (!$request->get('blog.delete') || !$request->isValidSig()) {
      $request->generateNewSig();
      return 'admin/blogs/delete.twig';
    }

    $blog_id = $this->getBlogId($request);
    $user_id = $this->getUserId();

    // 削除するブログが存在するか？
    $blogs_model = Model::load('Blogs');
    if (!$blog = $blogs_model->findByIdAndUserId($blog_id, $user_id)) {
      $this->setErrorMessage(__('I failed to remove'));
      $this->redirect($request, ['action' => 'index']);
    }

    // 削除処理
    $blogs_model->deleteByIdAndUserId($blog_id, $user_id);
    $this->setBlog(null); // ログイン中のブログを削除したのでブログの選択中状態を外す
    $this->setInfoMessage(__('I removed the blog'));
    $this->redirect($request, ['action' => 'index']);
    return 'admin/blogs/delete.twig'; // 到達しないはずである
  }

}

