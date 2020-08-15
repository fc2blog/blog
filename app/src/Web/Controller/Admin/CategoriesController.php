<?php

namespace Fc2blog\Web\Controller\Admin;

class CategoriesController extends AdminController
{

  /**
   * 新規作成
   */
  public function create()
  {
    $request = \Fc2blog\Web\Request::getInstance();
    $categories_model = \Fc2blog\Model\Model::load('Categories');

    $blog_id = $this->getBlogId();

    // 親カテゴリー一覧
    $options = $categories_model->getParentList($blog_id);
    $this->set('category_parents', array(0=>'') + $options);

    // カテゴリ登録数
    $create_limit = \Fc2blog\Config::get('CATEGORY.CREATE_LIMIT');
    $is_limit_create_category = ($create_limit > 0)? ($create_limit <= count($options)) : false;
    $this->set('is_limit_create_category', $is_limit_create_category);

    if ($is_limit_create_category) {
      $this->setErrorMessage(__('Exceeded the maximum number of registered category'));
      $request->set('category', null);
      return ;
    }

    // 初期表示時
    if (!$request->get('category') || !\Fc2blog\Web\Session::get('sig') || \Fc2blog\Web\Session::get('sig') !== $request->get('sig')) {
      \Fc2blog\Web\Session::set('sig', \Fc2blog\App::genRandomString());
      return ;
    }

    // 新規登録処理
    $category_request = $request->get('category');
    $category_request['blog_id'] = $blog_id;
    $errors = $categories_model->validate($category_request, $data, array('parent_id', 'name', 'category_order'));
    if (empty($errors)) {
      $data['blog_id'] = $blog_id;
      if ($id=$categories_model->addNode($data, 'blog_id=?', array($blog_id))) {
        $this->setInfoMessage(__('I added a category'));
        $this->redirect(array('action'=>'create'));
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
    $request = \Fc2blog\Web\Request::getInstance();
    $categories_model = \Fc2blog\Model\Model::load('Categories');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 親カテゴリー一覧
    $options = $categories_model->getParentList($blog_id, $id);
    $this->set('category_parents', array(0=>'') + $options);

    // 初期表示時に編集データの取得&設定
    if (!$request->get('category') || !\Fc2blog\Web\Session::get('sig') || \Fc2blog\Web\Session::get('sig') !== $request->get('sig')) {
      if (!$category=$categories_model->findByIdAndBlogId($id, $blog_id)) {
        $this->redirect(array('action'=>'create'));
      }
      $request->set('category', $category);
      return ;
    }

    // 更新処理
    $category_request = $request->get('category');
    $category_request['id'] = $id;            // 入力チェック用
    $category_request['blog_id'] = $blog_id;  // 入力チェック用
    $errors = $categories_model->validate($category_request, $data, array('parent_id', 'name', 'category_order'));
    if (empty($errors)){
      if ($categories_model->updateNodeById($data, $id, 'blog_id=?', array($blog_id))) {
        $this->setInfoMessage(__('I have updated the category'));
        $this->redirect(array('action'=>'create'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
   * 削除
   */
  public function delete()
  {
    $request = \Fc2blog\Web\Request::getInstance();
    $categories_model = \Fc2blog\Model\Model::load('Categories');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    if (!\Fc2blog\Web\Session::get('sig') || \Fc2blog\Web\Session::get('sig') !== $request->get('sig')) {
      $request->clear();
      $this->redirect(array('action'=>'create'));
      return;
    }
    
    // 削除データの取得(未分類であるid=1は削除させない)
    if ($id==1 || !$category=$categories_model->findByIdAndBlogId($id, $blog_id)) {
      $this->redirect(array('action'=>'create'));
    }

    // 削除処理
    $categories_model->deleteNodeByIdAndBlogId($id, $blog_id);
    $this->setInfoMessage(__('I removed the category'));
    $this->redirect(array('action'=>'create'));
  }

  /**
  * ajax用のカテゴリ追加
  */
  public function ajax_add()
  {
    \Fc2blog\Config::set('DEBUG', 0);    // デバッグなしに変更

    $request = \Fc2blog\Web\Request::getInstance();
    $categories_model = \Fc2blog\Model\Model::load('Categories');

    $blog_id = $this->getBlogId();

    $json = array('status' => 0);
    
    if (!\Fc2blog\Web\Session::get('sig') || \Fc2blog\Web\Session::get('sig') !== $request->get('sig')) {
      $request->clear();
      return;
    }
    
    $category_request = $request->get('category');
    $category_request['blog_id'] = $blog_id;
    $errors = $categories_model->validate($category_request, $data, array('parent_id', 'name'));
    if (empty($errors)) {
      $data['blog_id'] = $blog_id;
      if ($id=$categories_model->addNode($data, 'blog_id=?', array($blog_id))) {
        $json['status'] = 1;
        $json['category'] = array(
          'id'        => $id,
          'parent_id' => $data['parent_id'],
          'name'      => $data['name'],
        );
      }
    }

    $json['error'] = $errors;

    $this->layout = 'json.html';
    $this->set('json', $json);
  }

}

