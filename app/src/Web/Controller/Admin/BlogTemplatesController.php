<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Model\Model;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

class BlogTemplatesController extends AdminController
{

  /**
   * 一覧表示
   */
  public function index()
  {
    $request = Request::getInstance();

    Session::set('sig', App::genRandomString());

    $blog_id = $this->getBlogId();
    $device_type = $request->get('device_type', 0);

    $blog = $this->getBlog($blog_id);
    $this->set('template_ids', Model::load('Blogs')->getTemplateIds($blog));

    // デバイス毎に分けられたテンプレート一覧を取得
    $device_blog_templates = Model::load('BlogTemplates')->getTemplatesOfDevice($blog_id, $device_type);
    $this->set('device_blog_templates', $device_blog_templates);
  }

  /**
  * FC2のテンプレート一覧
  */
  public function fc2_index()
  {
    $request = Request::getInstance();

    // デバイスタイプの設定
    $device_type = $request->get('device_type', Config::get('DEVICE_PC'));
    $request->set('device_type', $device_type);

    // 条件設定
    $condition = array();
    $condition['page'] = $request->get('page', 0, Request::VALID_UNSIGNED_INT);
    $condition['device'] = Config::get('DEVICE_FC2_KEY.' . $device_type);

    // テンプレート一覧取得
    $fc2_templates = Model::load('Fc2Templates')->getListAndPaging($condition);
    $templates = $fc2_templates['templates'];
    $paging = $fc2_templates['pages'];

    $this->set('templates', $templates);
    $this->set('paging', $paging);
  }

  /**
  * FC2のテンプレート一覧
  */
  public function fc2_view()
  {
    $request = Request::getInstance();

    // 戻る用URLの設定
    $back_url = $request->getReferer();
    if (!empty($back_url)) {
      $request->set('back_url', $request->getReferer());
    }

    // デバイスタイプの設定
    $device_type = $request->get('device_type', Config::get('DEVICE_PC'));
    $request->set('device_type', $device_type);

    // テンプレート取得
    $device_key = Config::get('DEVICE_FC2_KEY.' . $device_type);
    $template = Model::load('Fc2Templates')->findByIdAndDevice($request->get('fc2_id'), $device_key);
    if (empty($template)) {
      return $this->error404();
    }
    $this->set('template', $template);
  }

  /**
   * 新規作成
   */
  public function create()
  {
    $request = Request::getInstance();
    /** @var BlogTemplatesModel $blog_templates_model */
    $blog_templates_model = Model::load('BlogTemplates');

    // 初期表示時
    if (!$request->get('blog_template') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      // FC2テンプレートダウンロード
      if ($request->get('fc2_id')) {
        $device_type = $request->get('device_type');
        $device_key = Config::get('DEVICE_FC2_KEY.' . $device_type);
        $template = Model::load('Fc2Templates')->findByIdAndDevice($request->get('fc2_id'), $device_key);
        $request->set('blog_template', array(
          'title'       => $template['name'],
          'html'        => $template['html'],
          'css'         => $template['css'],
          'device_type' => $device_type,
        ));
      } else {
        $request->set('blog_template.device_type', $request->get('device_type'));
      }
      Session::set('sig', App::genRandomString());
      return ;
    }

    // 新規登録処理
    $errors = array();
    $white_list = array('title', 'html', 'css', 'device_type');
    $errors['blog_template'] = $blog_templates_model->validate($request->get('blog_template'), $blog_template_data, $white_list);
    if (empty($errors['blog_template'])) {
      $blog_template_data['blog_id'] = $this->getBlogId();
      if ($id=$blog_templates_model->insert($blog_template_data)) {
        $this->setInfoMessage(__('I created a template'));
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
    /** @var BlogTemplatesModel $blog_templates_model */
    $blog_templates_model = Model::load('BlogTemplates');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 使用中のテンプレート判定
    $blog = $this->getBlog($blog_id);

    // 初期表示時に編集データの取得&設定
    if (!$request->get('blog_template') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      if (!$blog_template=$blog_templates_model->findByIdAndBlogId($id, $blog_id)) {
        $this->redirect(array('action'=>'index'));
      }
      $request->set('blog_template', $blog_template);
      Session::set('sig', App::genRandomString());
      return ;
    }

    // 更新処理
    $errors = array();
    $white_list = array('title', 'html', 'css');
    $errors['blog_template'] = $blog_templates_model->validate($request->get('blog_template'), $blog_template_data, $white_list);
    if (empty($errors['blog_template'])) {
      if ($blog_templates_model->updateByIdAndBlogId($blog_template_data, $id, $blog_id)) {
        $this->setInfoMessage(__('I have updated the template'));
        $this->redirect(array('action'=>'index'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
  * 対象のテンプレートをブログのテンプレートとして設定する
  */
  public function apply()
  {
    $request = Request::getInstance();
    $blog_templates_model = Model::load('BlogTemplates');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    $blog_template = $blog_templates_model->findByIdAndBlogId($id, $blog_id);
    if (empty($blog_template)) {
      $this->setErrorMessage(__('Template to be used can not be found'));
      $this->redirectBack(array('action'=>'index'));
    }

    if (Session::get('sig') && Session::get('sig') === $request->get('sig')) {
      // テンプレートの切り替え作業
      Model::load('Blogs')->switchTemplate($blog_template, $blog_id);
      $this->setInfoMessage(__('I switch the template'));
    }
    $this->redirectBack(array('action'=>'index'));
  }

  /**
   * テンプレートダウンロード
   */
  public function download()
  {
    $request = Request::getInstance();
    /** @var BlogTemplatesModel $blog_templates_model */
    $blog_templates_model = Model::load('BlogTemplates');

    $id = $request->get('fc2_id');
    $device_type = $request->get('device_type');
    if (empty($id) || empty($device_type)) {
      return $this->error404();
    }

    $device_key = Config::get('DEVICE_FC2_KEY.' . $device_type);
    $template = Model::load('Fc2Templates')->findByIdAndDevice($id, $device_key);
    if (empty($template)) {
      $this->setErrorMessage(__('Template does not exist'));
      $this->redirectBack(array('controller'=>'blog_templates', 'action'=>'fc2_index', 'device_type'=>$device_type));
    }

    // 追加用のデータを取得データから作成
    $blog_template = array(
      'title'       => $template['name'],
      'html'        => $template['html'],
      'css'         => $template['css'],
      'device_type' => $device_type,
    );

    // 新規登録処理
    $errors = array();
    $white_list = array('title', 'html', 'css', 'device_type');
    $errors['blog_template'] = $blog_templates_model->validate($blog_template, $blog_template_data, $white_list);
    if (empty($errors['blog_template'])) {
      $blog_template_data['blog_id'] = $this->getBlogId();
      if ($id=$blog_templates_model->insert($blog_template_data)) {
        $this->setInfoMessage('「' . h($blog_template['title']) . '」' . __('I downloaded the template'));
        $this->redirect(array('action'=>'index', 'device_type'=>$device_type));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('There is a flaw in the template to be downloaded'));
    $this->redirectBack(array('controller'=>'blog_templates', 'action'=>'fc2_index', 'device_type'=>$device_type));
  }

  /**
   * 削除
   */
  public function delete()
  {
    $request = Request::getInstance();
    $blog_templates_model = Model::load('BlogTemplates');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 使用中のテンプレート判定
    $blog = $this->getBlog($blog_id);
    $template_ids = BlogsModel::getTemplateIds($blog);
    if (in_array($id, $template_ids)) {
      $this->setErrorMessage(__('You can not delete a template in use'));
      $this->redirect(array('action'=>'index'));
    }

    // 削除データの取得
    if (!$blog_template=$blog_templates_model->findByIdAndBlogId($id, $blog_id)) {
      $this->redirect(array('action'=>'index'));
    }

    if (Session::get('sig') && Session::get('sig') === $request->get('sig')) {
      // 削除処理
      $blog_templates_model->deleteByIdAndBlogId($id, $blog_id);
      $this->setInfoMessage(__('I removed the template'));
    }
    $this->redirectBack(array('action'=>'index'));
  }

}

