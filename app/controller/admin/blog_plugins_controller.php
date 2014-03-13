<?php

require_once(Config::get('CONTROLLER_DIR') . 'admin/admin_controller.php');

class BlogPluginsController extends AdminController
{

  /**
   * 一覧表示
   */
  public function index()
  {
    $request = Request::getInstance();

    Session::set('sig', App::genRandomString());

    $blog_id = $this->getBlogId();
    $device_type = $request->get('device_type', Config::get('DEVICE_PC'), Request::VALID_IN_ARRAY, Config::get('ALLOW_DEVICES'));
    $request->set('device_type', $device_type);

    // デバイス毎に分けられたテンプレート一覧を取得
    $category_blog_plugins = Model::load('BlogPlugins')->getCategoryPlugins($blog_id, $device_type);
    $this->set('category_blog_plugins', $category_blog_plugins);
  }

  /**
  * 公式プラグイン検索
  */
  public function official_search()
  {
    return $this->plugin_search(true);
  }

  /**
  * 共有プラグイン検索
  */
  public function share_search()
  {
    return $this->plugin_search(false);
  }

  /**
  * プラグイン検索
  */
  private function plugin_search($is_official=true)
  {
    $request = Request::getInstance();
    $plugins_model = Model::load('Plugins');

    // デバイスタイプの取得
    $device_type = $request->get('device_type', Config::get('DEVICE_PC'), Request::VALID_IN_ARRAY, Config::get('ALLOW_DEVICES'));
    $request->set('device_type', $device_type);

    // 検索条件作成
    $where = 'device_type=?';
    $params = array($device_type);

    if ($is_official) {
      $where .= ' AND user_id=0';
    } else {
      $where .= ' AND user_id<>0';
    }

    // 並び順
    $order = 'updated_at DESC, id DESC';

    $options = array(
      'where'  => $where,
      'params' => $params,
      'limit'  => $request->get('limit', Config::get('PAGE.PLUGIN.DEFAULT.LIMIT'), Request::VALID_POSITIVE_INT),
      'page'   => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
      'order'  => $order,
    );
    $plugins = $plugins_model->find('all', $options);
    $paging = $plugins_model->getPaging($options);

    $this->set('plugins', $plugins);
    $this->set('paging', $paging);

    return 'BlogPlugins/plugin_search.html';
  }

  /**
   * 新規作成
   */
  public function create()
  {
    $request = Request::getInstance();
    $blog_plugins_model = Model::load('BlogPlugins');

    // 初期表示時
    if (!$request->get('blog_plugin') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      Session::set('sig', App::genRandomString());
      $request->set('blog_plugin', array(
        'device_type' => $request->get('device_type', Config::get('DEVICE_PC'), Request::VALID_IN_ARRAY, Config::get('ALLOW_DEVICES')),
        'category'    => $request->get('category', 1),
      ));
      return ;
    }

    // 新規登録処理
    $errors = array();
    $white_list = array('title', 'title_align', 'title_color', 'contents', 'contents_align', 'contents_color', 'device_type', 'category');
    $errors['blog_plugin'] = $blog_plugins_model->validate($request->get('blog_plugin'), $blog_plugin_data, $white_list);
    if (empty($errors['blog_plugin'])) {
      $blog_plugin_data['blog_id'] = $this->getBlogId();
      if ($id=$blog_plugins_model->insert($blog_plugin_data)) {
        $this->setInfoMessage(__('I created a plugin'));
        $this->redirect(array('action'=>'index', 'device_type'=>$blog_plugin_data['device_type']));
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
    $blog_plugins_model = Model::load('BlogPlugins');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 編集対象のデータ取得
    if (!$blog_plugin=$blog_plugins_model->findByIdAndBlogId($id, $blog_id)) {
      $this->redirect(array('action'=>'index'));
    }

    // 初期表示時に編集データの設定
    if (!$request->get('blog_plugin') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      $request->set('blog_plugin', $blog_plugin);
      Session::set('sig', App::genRandomString());
      return ;
    }

    // 更新処理
    $errors = array();
    $white_list = array('title', 'title_align', 'title_color', 'contents', 'contents_align', 'contents_color');
    $errors['blog_plugin'] = $blog_plugins_model->validate($request->get('blog_plugin'), $blog_plugin_data, $white_list);
    if (empty($errors['blog_plugin'])) {
      if ($blog_plugins_model->updateByIdAndBlogId($blog_plugin_data, $id, $blog_id)) {
        $this->setInfoMessage(__('I have updated the plugin'));
        $this->redirect(array('action'=>'index', 'device_type'=>$blog_plugin['device_type']));
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
    $request = Request::getInstance();
    $blog_plugins_model = Model::load('BlogPlugins');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 削除データの取得
    $blog_plugin = $blog_plugins_model->findByIdAndBlogId($id, $blog_id);
    if (!$blog_plugin) {
      $this->redirect(array('action'=>'index'));
    }

    if (Session::get('sig') && Session::get('sig') === $request->get('sig')) {
      // 削除処理
      $blog_plugins_model->deleteByIdAndBlogId($id, $blog_id);
      $this->setInfoMessage(__('I removed the plugin'));
    }
    $this->redirect(array('action'=>'index', 'device_type'=>$blog_plugin['device_type']));
  }

  /**
   * 登録
   */
  public function register()
  {
    $request = Request::getInstance();
    $plugins_model = Model::load('Plugins');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 登録データの取得
    $blog_plugin = Model::load('BlogPlugins')->findByIdAndBlogId($id, $blog_id);
    if (!$blog_plugin) {
      $this->redirect(array('action'=>'index'));
    }
    $this->set('blog_plugin', $blog_plugin);

    if (!$request->get('plugin') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      // 初期値入力
      if ($blog_plugin['plugin_id']) {
        // 既に登録済み
        $plugin = $plugins_model->findByIdAndUserId($blog_plugin['plugin_id'], $this->getUserId());
        $request->set('plugin.title', $plugin['title']);
        $request->set('plugin.body', $plugin['body']);
      } else {
        // 未登録
        $request->set('plugin.title', $blog_plugin['title']);
      }
      Session::set('sig', App::genRandomString());
      return ;
    }

    // 新規登録処理
    $errors = array();
    $white_list = array('title', 'body');
    $errors['plugin'] = $plugins_model->validate($request->get('plugin'), $plugin_data, $white_list);
    if (empty($errors['plugin'])) {
      // 登録処理
      if (Model::load('Plugins')->register($blog_plugin, $plugin_data, $this->getUserId())) {
        $this->setInfoMessage(__('I have registered the plug-in'));
      } else {
        $this->setErrorMessage(__('I failed to register the plug-in'));
      }
      $this->redirect(array('action'=>'index'));
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
   * 登録済みのプラグイン削除
   */
  public function plugin_delete()
  {
    $request = Request::getInstance();
    $plugins_model = Model::load('Plugins');

    $id = $request->get('id');
    $user_id = $this->getUserId();

    // 削除データの取得
    if (!$plugin=$plugins_model->findByIdAndUserId($id, $user_id)) {
      $this->redirect(array('action'=>'search'));
    }

    if (Session::get('sig') && Session::get('sig') === $request->get('sig')) {
      // 削除処理
      $plugins_model->deleteByIdAndUserId($id, $user_id);
      $this->setInfoMessage(__('I removed the plugin'));
    }
    $this->redirectBack(array('action'=>'search'));
  }

  /**
   * プラグインのダウンロード
   */
  public function download()
  {
    $request = Request::getInstance();
    $blog_plugins_model = Model::load('BlogPlugins');

    $id = $request->get('id');
    $plugin = Model::load('Plugins')->findById($id);
    if (empty($plugin)) {
      $this->setErrorMessage(__('Plugin does not exist'));
      $this->redirectBack(array('controller'=>'blog_plugins', 'action'=>'index'));
    }

    if (Session::get('sig') && Session::get('sig') === $request->get('sig')) {
      // 追加用のデータを取得データから作成
      $blog_plugin_data = array(
        'title'       => $plugin['title'],
        'contents'    => $plugin['contents'],
        'device_type' => $plugin['device_type'],
        'category'    => $request->get('category'),
      );

      // 新規登録処理
      $blog_plugin_data['blog_id'] = $this->getBlogId();
      if ($id=Model::load('BlogPlugins')->insert($blog_plugin_data)) {
        $this->setInfoMessage(__('I created a plugin'));
        $this->redirect(array('action'=>'index', 'device_type'=>$plugin['device_type']));
      }

      $this->setErrorMessage(__('I failed to download the plug-in'));
    }
    $this->redirectBack(array('controller'=>'blog_plugins', 'action'=>'index'));
  }

  /**
   * 並べ替え
   */
  public function sort()
  {
    $request = Request::getInstance();
    $blog_plugins_model = Model::load('BlogPlugins');

    $blog_id = $this->getBlogId();
    $device_type = $request->get('device_type', Config::get('DEVICE_PC'), Request::VALID_IN_ARRAY, Config::get('ALLOW_DEVICES'));

    // 並べ替え処理
    $blog_plugins_model->sort($request->get('blog_plugins', array()), $device_type, $blog_id);

    $this->setInfoMessage(__('I have completed the sorting'));
    if (App::isSP()) {
      $this->redirect(array('action'=>'index', 'device_type'=>$device_type, 'state'=>'sort'));
    }
    $this->redirect(array('action'=>'index', 'device_type'=>$device_type));
  }

  /**
   * プラグインの表示設定
   */
  public function display_changes()
  {
    $request = Request::getInstance();
    $blog_plugins_model = Model::load('BlogPlugins');

    $blog_id = $this->getBlogId();
    $device_type = $request->get('device_type', Config::get('DEVICE_PC'), Request::VALID_IN_ARRAY, Config::get('ALLOW_DEVICES'));

    if (Session::get('sig') && Session::get('sig') === $request->get('sig')) {
      // プラグインの表示可否の一括変更
      $blog_plugins_model->updateDisplay($request->get('blog_plugins'), $blog_id);
      $this->setInfoMessage(__('I changed the display settings'));
    }

    if (App::isSP()) {
      $this->redirect(array('action'=>'index', 'device_type'=>$device_type, 'state'=>'display'));
    }
    $this->redirect(array('action'=>'index', 'device_type'=>$device_type));
  }

  /**
   * プラグインの表示設定
   */
  public function display_change()
  {
    $request = Request::getInstance();
    $blog_plugins_model = Model::load('BlogPlugins');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();
    $display = $request->get('display') ? Config::get('APP.DISPLAY.SHOW') : Config::get('APP.DISPLAY.HIDE');  // 表示可否

    // 編集対象のデータ取得
    if (!$blog_plugin=$blog_plugins_model->findByIdAndBlogId($id, $blog_id) || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      $this->redirect(array('action'=>'index'));
    }

    // 表示・非表示設定
    $blog_plugins_model->updateByIdAndBlogId(array('display'=>$display), $id, $blog_id);
//    $blog_plugins_model->updateDisplay(array($id=>$request->get('display')), $blog_id);   // TODO:後でこちらに置き換え

    Config::set('DEBUG', 0);    // デバッグ設定を変更
    $this->layout = 'ajax.html';
  }

  /**
   * テンプレートエクスポート
   */
/*
  public function export()
  {
    $request = Request::getInstance();

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 登録データの取得
    if (!$blog_plugin=Model::load('BlogPlugins')->findByIdAndBlogId($id, $blog_id)) {
      $this->redirect(array('action'=>'index'));
    }

    $json = array(
      'title'       => $blog_plugin['title'],
      'list'        => $blog_plugin['list'],
      'contents'    => $blog_plugin['contents'],
      'attribute'   => $blog_plugin['attribute'],
      'device_type' => $blog_plugin['device_type'],
    );
    $json = json_encode($json);

    $this->set('file_name', time() . '.json');
    $this->set('data', $json);
    $this->layout = 'download.html';
  }
*/
}

