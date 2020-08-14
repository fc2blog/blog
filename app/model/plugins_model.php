<?php

class PluginsModel extends \Fc2blog\Model\Model
{

  public static $instance = null;

  public function __construct(){}

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new PluginsModel();
    }
    return self::$instance;
  }

  public function getTableName()
  {
    return 'plugins';
  }

  /**
  * バリデート処理
  */
  public function validate($data, &$valid_data, $white_list=array())
  {
    // バリデートを定義
    $this->validates = array(
      'title' => array(
        'required'  => true,
        'maxlength' => array('max' => 50),
      ),
      'body' => array(
        'required'  => true,
        'maxlength' => array('max' => 2000),
      ),
    );

    return parent::validate($data, $valid_data, $white_list);
  }

  /**
  * IDで検索(blog_pluginsのattribute属性付き
  */
  public function findById($id, $options=array())
  {
    $plugin = parent::findById($id, $options);
    if (!empty($plugin)) {
      $plugin['title_align'] = $plugin['contents_align'] = 'left';
      $plugin['title_color'] = $plugin['contents_color'] = '';
    }
    return $plugin;
  }

  /**
  * プラグインの登録
  */
  public function register($blog_plugin, $plugin_data, $user_id=0)
  {
    // 登録データ作成
    $values = array(
      'title'       => $plugin_data['title'],
      'body'        => $plugin_data['body'],
      'list'        => $blog_plugin['list'],
      'contents'    => $blog_plugin['contents'],
      'attribute'   => $blog_plugin['attribute'],
      'device_type' => $blog_plugin['device_type'],
    );
    if ($blog_plugin['plugin_id']!=0) {
      // プラグインIDがある場合は更新
      $values['updated_at'] = date('Y-m-d H:i:s');
      return $this->updateById($values, $blog_plugin['plugin_id']);
    }
    // プラグインIDが無い場合は新規登録
    $values['user_id'] = $user_id;
    $values['blog_id'] = $blog_plugin['blog_id'];
    $values['updated_at'] = $values['created_at'] = date('Y-m-d H:i:s');
    $id = $this->insert($values);
    if (empty($id)) {
      return $id;
    }
    // 作成したプラグインIDで更新
    return \Fc2blog\Model\Model::load('BlogPlugins')->updateByIdAndBlogId(array('plugin_id'=>$id), $blog_plugin['id'], $blog_plugin['blog_id']);
  }

  /**
  * idとuser_idをキーとした更新
  */
  public function deleteByIdAndUserId($id, $user_id, $options=array())
  {
    if (!parent::deleteByIdAndUserId($id, $user_id, $options)) {
      return false;
    }
    // 登録状態(id<>0)のプラグインを未登録(id=0)に戻す
    return \Fc2blog\Model\Model::load('BlogPlugins')->update(array('plugin_id'=>0), 'plugin_id=?', array($id));
  }

  /**
  * 初期公式プラグインの追加処理
  */
  public function addInitialOfficialPlugin()
  {
    $this->delete('user_id=0');

    \Fc2blog\Config::read('fc2_default_plugin.php');   // 初期公式プラグインデータ取得
    $plugins = \Fc2blog\Config::get('official_plugins');
    foreach ($plugins as $plugin) {
      $plugin['list'] = '';
      $plugin['attribute'] = '{}';
      $plugin['created_at'] = $plugin['updated_at'] = date('Y-m-d H:i:s');
      $this->insert($plugin);
    }
  }

}

