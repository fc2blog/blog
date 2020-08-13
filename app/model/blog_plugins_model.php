<?php

class BlogPluginsModel extends Model
{

  public static $instance = null;

  public function __construct(){}

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new BlogPluginsModel();
    }
    return self::$instance;
  }

  public function getTableName()
  {
    return 'blog_plugins';
  }

  public function getAutoIncrementCompositeKey()
  {
    return 'blog_id';
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
      'title_align' => array(
        'default_value' => 'left',
        'in_array'      => array('values'=>array_keys(self::getAttributeAlign())),
      ),
      'title_color' => array(
        'default_value' => '',
        'in_array'      => array('values'=>array_keys(self::getAttributeColor())),
      ),
      'contents' => array(
        'required'  => true,
        'maxlength' => array('max' => 100000),
        'own'       => array('method' => 'fc2PluginSyntax')
      ),
      'contents_align' => array(
        'default_value' => 'left',
        'in_array'      => array('values'=>array_keys(self::getAttributeAlign())),
      ),
      'contents_color' => array(
        'default_value' => '',
        'in_array'      => array('values'=>array_keys(self::getAttributeColor())),
      ),
      'device_type' => array(
        'default_value' => \Fc2blog\Config::get('DEVICE_PC'),
        'in_array'      => array('values'=>array_keys(\Fc2blog\Config::get('DEVICE_NAME'))),
      ),
      'category' => array(
        'default_value' => 1,
        'in_array'      => array('values'=>array(1, 2, 3)),
      ),
    );

    $errors = parent::validate($data, $valid_data, $white_list);
    if (!empty($errors)) {
      return $errors;
    }

    // title_align,title_color...をattributesに纏める
    if (in_array('title_align', $white_list) || in_array('title_color', $white_list)
        || in_array('contents_align', $white_list) || in_array('contents_color', $white_list)
    ) {
      $attribute = array(
        'title_align'    => $valid_data['title_align'],
        'title_color'    => $valid_data['title_color'],
        'contents_align' => $valid_data['contents_align'],
        'contents_color' => $valid_data['contents_color'],
      );
      $valid_data['attribute'] = json_encode($attribute);
      unset($valid_data['title_align'], $valid_data['title_color'], $valid_data['contents_align'], $valid_data['contents_color']);
    }
    return $errors;
  }

  /**
  * 文字方向の設定
  */
  public static function getAttributeAlign()
  {
    return array(
      'left'   => __('Flush left'),
      'center' => __('Center justification'),
      'right'  => __('Right justification'),
    );
  }

  /**
  * 文字色の設定
  */
  public static function getAttributeColor($text=false)
  {
    if ($text) {
      return array(
        ''       => __('Nothing'),
        'red'    => __('Red'),
        'green'  => __('Green'),
        'blue'   => __('Blue'),
        'purple' => __('Purple'),
        'pink'   => __('Pink'),
        'orange' => __('Orange'),
        'navy'   => __('Navy'),
        'gray'   => __('Gray'),
      );
    }
    return array(
      ''       => __('Nothing'),
      'red'    => '<span style="color:red">■</span>',
      'green'  => '<span style="color:green">■</span>',
      'blue'   => '<span style="color:blue">■</span>',
      'purple' => '<span style="color:purple">■</span>',
      'pink'   => '<span style="color:pink">■</span>',
      'orange' => '<span style="color:orange">■</span>',
      'navy'   => '<span style="color:navy">■</span>',
      'gray'   => '<span style="color:gray">■</span>',
    );
  }

  /**
  * FC2テンプレートの構文チェック
  */
  public static function fc2PluginSyntax($value)
  {
    // フォルダが存在しない場合作成
    $plugin_path = \Fc2blog\Config::get('BLOG_TEMPLATE_DIR') . App::getBlogLayer(\Fc2blog\Session::get('blog_id')) . '/plugins/syntax.php';
    $plugin_dir = dirname($plugin_path);
    if (!file_exists($plugin_dir)) {
      mkdir($plugin_dir, 0777, true);
    }

    // HTMLをPHPテンプレートに変換してテンプレートファイルの作成
    Model::load('BlogTemplates');
    $html = BlogTemplatesModel::convertFC2Template($value);
    file_put_contents($plugin_path, $html);
    chmod($plugin_path, 0777);

    // PHPのシンタックスチェック
    $cmd = 'php -l ' . $plugin_path;
    $ret = shell_exec($cmd);
    if (strpos($ret, 'No syntax errors detected')!==false) {
      return true;
    }
    return __('There may be a problem with the template or plug-in, installed in the blog.');
  }

  /**
  * カテゴリー毎のプラグイン一覧
  */
  public function getCategoryPlugins($blog_id, $device_type)
  {
    $options = array(
      'where'  => 'blog_id=? AND device_type=?',
      'params' => array($blog_id, $device_type),
      'order'  => 'category ASC, plugin_order ASC',
    );
    $blog_plugins = $this->find('all', $options);

    $category_blog_plugins = array(1=>array());
    if ($device_type==\Fc2blog\Config::get('DEVICE_PC')) {
      // PC版のみ3つまでカテゴリーが存在する
      $category_blog_plugins = array(1=>array(), 2=>array(), 3=>array());
    }
    foreach ($blog_plugins as $blog_plugin) {
      $category_blog_plugins[$blog_plugin['category']][] = $blog_plugin;
    }
    return $category_blog_plugins;
  }

  /**
  * idとblog_idの複合キーからデータを取得
  * attributeデータを振り分け
  */
  public function findByIdAndBlogId($id, $blog_id, $options=array())
  {
    $data = parent::findByIdAndBlogId($id, $blog_id, $options);
    if (empty($data)) {
      return $data;
    }

    $attribute = json_decode($data['attribute']);
    if (empty($attribute)) {
      $data['title_align'] = $data['contents_align'] = 'left';
      $data['title_color'] = $data['contents_color'] = '';
    } else {
      $data['title_align'] = $attribute->title_align;
      $data['title_color'] = $attribute->title_color;
      $data['contents_align'] = $attribute->contents_align;
      $data['contents_color'] = $attribute->contents_color;
    }
    return $data;
  }

  /**
  * デバイスタイプとカテゴリーの条件にマッチしたプラグインを返却
  */
  public function findByDeviceTypeAndCategory($device_type, $category, $blog_id)
  {
    $options = array(
      'where'  => 'blog_id=? AND device_type=? AND category=? AND display=' . \Fc2blog\Config::get('APP.DISPLAY.SHOW'),
      'params' => array($blog_id, $device_type, $category),
      'order'  => 'plugin_order ASC',
    );
    $plugins = $this->find('all', $options);
    foreach ($plugins as $key => $value) {
      $attribute = json_decode($value['attribute']);
      if (empty($attribute)) {
        $plugins[$key]['title_align'] = $plugins[$key]['contents_align'] = 'left';
        $plugins[$key]['title_color'] = $plugins[$key]['contents_color'] = '';
      } else {
        $plugins[$key]['title_align'] = $attribute->title_align;
        $plugins[$key]['title_color'] = $attribute->title_color;
        $plugins[$key]['contents_align'] = $attribute->contents_align;
        $plugins[$key]['contents_color'] = $attribute->contents_color;
      }
    }
    return $plugins;
  }

  /**
  * 最後の表示順を取得する
  */
  public function getNextPluginOrder($blog_id, $device_type, $category) {
    $plugin_order = $this->find('one', array(
      'fields' => 'plugin_order',
      'where'  => 'blog_id=? AND device_type=? AND category=?',
      'params' => array($blog_id, $device_type, $category),
      'order'  => 'plugin_order DESC',
      'limit'  => 1,
    ));
    if (empty($plugin_order)) {
      return 0;
    }
    return $plugin_order + 1;
  }

  /**
  * テンプレートの作成
  */
  public function insert($values, $options=array())
  {
    $default_values = [
        'list' => '',
        'attribute' => '',
    ];
    $values += $default_values;

    $values['updated_at'] = $values['created_at'] = date('Y-m-d H:i:s');
    $values['plugin_order'] = $this->getNextPluginOrder($values['blog_id'], $values['device_type'], $values['category']);
    $id = parent::insert($values, $options);
    if ($id) {
      // プラグインのPHPファイル作成
      self::createPlugin($values['contents'], $values['blog_id'], $id);
    }
    return $id;
  }

  /**
  * テンプレートの更新
  */
  public function updateByIdAndBlogId($values, $id, $blog_id, $options=array())
  {
    $values['updated_at'] = date('Y-m-d H:i:s');
    if (!parent::updateByIdAndBlogId($values, $id, $blog_id, $options)){
      return false;
    }
    // プラグインのPHPファイル作成
    if (isset($values['contents'])) {
      self::createPlugin($values['contents'], $blog_id, $id);
    }
    return true;
  }

  /**
  * 表示方法の変更を行う
  *  params => array([id=>display],...)の形式
  */
  public function updateDisplay($params, $blog_id)
  {
    if (!count($params)) {
      return false;
    }

    $displays = array();
    $displays[\Fc2blog\Config::get('APP.DISPLAY.SHOW')] = array();
    $displays[\Fc2blog\Config::get('APP.DISPLAY.HIDE')] = array();
    foreach ($params as $id => $display) {
      // show,hide以外のdisplayは更新対象としない
      if (isset($displays[$display])) {
        $displays[$display][] = $id;
      }
    }

    $ret = true;
    foreach ($displays as $display => $values) {
      if (!count($values)) {
        continue ;
      }
      $where = 'blog_id=? AND id IN (' . implode(',', array_fill(0, count($values), '?')) . ')';
      $ret = $ret && $this->update(array('display'=>$display), $where, array_merge(array($blog_id), $values));
    }

    return $ret;
  }

  /**
  * idとblog_idをキーとした削除 + ファイル削除も行う
  */
  public function deleteByIdAndBlogId($id, $blog_id, $options=array())
  {
    // プラグインファイルの削除
    $plugin_file = App::getPluginFilePath($blog_id, $id);
    is_file($plugin_file) && unlink($plugin_file);

    // 本体削除
    return parent::deleteByIdAndBlogId($id, $blog_id, $options);
  }

  /**
  * 並べ替え
  *  [id] => array(order=>x, category=>x)の形
  */
  public function sort($sort_values, $device_type, $blog_id) {
    $blog_plugins = $this->find('all', array(
      'fields' => array('id, plugin_order, category'),
      'where'  => 'blog_id=? AND device_type=?',
      'params' => array($blog_id, $device_type),
    ));
    $ids = array_keys($sort_values);
    foreach ($blog_plugins as $blog_plugin) {
      if (!in_array($blog_plugin['id'], $ids)) {
        $this->deleteByIdAndBlogId($blog_plugin['id'], $blog_id);
        continue ;
      }
      $values = $sort_values[$blog_plugin['id']];
      if ($blog_plugin['plugin_order']==$values['order'] && $blog_plugin['category']==$values['category']) {
        // 順序、カテゴリーに変更なし
        continue;
      }
      // 並べ替えの順序で更新
      $params = array(
        'plugin_order' => intval($values['order']),
        'category'     => intval($values['category']),
      );
      $this->updateByIdAndBlogId($params, $blog_plugin['id'], $blog_id);
    }
  }

  /**
  * テンプレートを作成
  */
  public static function createPlugin($html, $blog_id, $id='preview')
  {
    // フォルダが存在しない場合作成
    $plugin_path = App::getPluginFilePath($blog_id, $id);
    $plugin_dir = dirname($plugin_path);
    if (!file_exists($plugin_dir)) {
      mkdir($plugin_dir, 0777, true);
    }

    // HTMLをPHPテンプレートに変換してテンプレートファイルの作成
    Model::load('BlogTemplates');
    $html = BlogTemplatesModel::convertFC2Template($html);
    file_put_contents($plugin_path, $html);
    chmod($plugin_path, 0777);
  }

}

