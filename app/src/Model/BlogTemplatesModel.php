<?php

namespace Fc2blog\Model;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Web\Session;

class BlogTemplatesModel extends \Fc2blog\Model\Model
{

  public static $instance = null;

  public function __construct(){}

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new BlogTemplatesModel();
    }
    return self::$instance;
  }

  public function getTableName()
  {
    return 'blog_templates';
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
      'html' => array(
        'required'  => true,
        'maxlength' => array('max' => 100000),
        'own'       => array('method' => 'fc2TemplateSyntax')
      ),
      'css' => array(
        'required'  => false,
        'maxlength' => array('max' => 100000),
      ),
      'device_type' => array(
        'default_value' => Config::get('DEVICE_PC'),
        'in_array'      => array('values'=>array_keys(Config::get('DEVICE_NAME'))),
      ),
    );

    return parent::validate($data, $valid_data, $white_list);
  }

  /**
  * FC2テンプレートの構文チェック
  */
  public static function fc2TemplateSyntax($value)
  {
    // フォルダが存在しない場合作成
    $templatePath = Config::get('BLOG_TEMPLATE_DIR') . App::getBlogLayer(Session::get('blog_id')) . '/syntax.php';
    $templateDir = dirname($templatePath);
    if (!file_exists($templateDir)) {
      mkdir($templateDir, 0777, true);
    }

    // HTMLをPHPテンプレートに変換してテンプレートファイルの作成
    $html = self::convertFC2Template($value);
    file_put_contents($templatePath, $html);
    chmod($templatePath, 0777);

    // PHPのシンタックスチェック
    $cmd = 'php -l ' . $templatePath;
    $ret = shell_exec($cmd);
    if (strpos($ret, 'No syntax errors detected')!==false) {
      return true;
    }
    return __('There may be a problem with the template or plug-in, installed in the blog.');
  }

  /**
  * テンプレートのパスを返却
  */
  public static function getTemplateFilePath($blog_id, $device_type=0, $isPreview=false)
  {
    return Config::get('BLOG_TEMPLATE_DIR') . App::getBlogLayer($blog_id) . '/' . $device_type . '/' . ($isPreview ? 'preview' : 'index') . '.php';
  }

  /**
  * CSSのパスを返却
  */
  public static function getCssFilePath($blog_id, $device_type, $isPreview=false)
  {
    return Config::get('WWW_UPLOAD_DIR') . App::getBlogLayer($blog_id) . '/' . $device_type . '/' . ($isPreview ? 'preview' : 'index') . '.css';
  }

  /**
  * CSSのURLを返却
  */
  public static function getCssUrl($blog_id, $device_type, $isPreview=false)
  {
    return '/' . Config::get('UPLOAD_DIR_NAME') . '/' . App::getBlogLayer($blog_id) . '/' . $device_type . '/' . ($isPreview ? 'preview' : 'index') . '.css';
  }

  /**
  * テンプレートを作成
  */
  public static function createTemplate($templateId, $blog_id, $device_type, $html=null, $css=null)
  {
    $isPreview = !empty($html);   // HTMLの情報が入っている場合プレビュー用として作成

    if (!$isPreview) {
      // DBからHTMLとCSSを取得
      $blog_templates_model = \Fc2blog\Model\Model::load('BlogTemplates');
      $blogTemplate = $blog_templates_model->findByIdAndBlogId($templateId, $blog_id);
      $html = $blogTemplate['html'];
      $css = $blogTemplate['css'];
    }

    // フォルダが存在しない場合作成
    $templatePath = self::getTemplateFilePath($blog_id, $device_type, $isPreview);
    $templateDir = dirname($templatePath);
    if (!file_exists($templateDir)) {
      mkdir($templateDir, 0777, true);
    }

    // HTMLをPHPテンプレートに変換してテンプレートファイルの作成
    $html = self::convertFC2Template($html);
    file_put_contents($templatePath, $html);
    chmod($templatePath, 0777);

    // フォルダが存在しない場合作成
    $cssPath = self::getCssFilePath($blog_id, $device_type, $isPreview);
    $cssDir = dirname($cssPath);
    if (!file_exists($cssDir)) {
      mkdir($cssDir, 0777, true);
    }

    // CSSファイルの作成
    file_put_contents($cssPath, $css);
    chmod($cssPath, 0777);
  }

  /**
  * デバイス毎のテンプレート一覧
  */
  public function getTemplatesOfDevice($blog_id, $device_type=0)
  {
    $options = array(
      'fields' => array('id', 'blog_id', 'template_id', 'title', 'device_type', 'created_at', 'updated_at'),
      'where'  => 'blog_id=?',
      'params' => array($blog_id),
      'order'  => 'device_type ASC, title ASC',
    );
    if ($device_type) {
      $options['where'] .= ' AND device_type=?';
      $options['params'][] = $device_type;
    } else {
      $options['where'] .= ' AND device_type IN (' . implode(',', Config::get('ALLOW_DEVICES')) . ')';
    }
    $blog_templates = $this->find('all', $options);

    $device_blog_templates = array();
    foreach ($blog_templates as $blog_template) {
      if (empty($device_blog_templates[$blog_template['device_type']])) {
        $device_blog_templates[$blog_template['device_type']] = array();
      }
      $device_blog_templates[$blog_template['device_type']][] = $blog_template;
    }
    return $device_blog_templates;
  }

  /**
  * テンプレートの作成
  */
  public function insert($values, $options=array())
  {
    $default_values = [
      'template_id' => 0,
    ];
    $values += $default_values;
    $values['updated_at'] = $values['created_at'] = date('Y-m-d H:i:s');
    $id = parent::insert($values, $options);

    return $id;
  }

  /**
  * テンプレートの更新
  */
  public function updateByIdAndBlogId($values, $id, $blog_id, $options=array())
  {
    $values['updated_at'] = date('Y-m-d H:i:s');
    if (!parent::updateByIdAndBlogId($values, $id, $blog_id, $options)) {
      return false;
    }

    // デバイスタイプを取得
    if (!$blogTemplate=$this->findByIdAndBlogId($id, $blog_id, array('fields'=>'device_type'))) {
      return false;
    }
    $device_type = $blogTemplate['device_type'];

    // 作成済みテンプレート,CSSの削除(HTMLとCSSが変更されている場合 又は 使用テンプレートの変更が行われた場合)
    $templatePath = self::getTemplateFilePath($blog_id, $device_type);
    is_file($templatePath) && unlink($templatePath);

    $cssFilePath = self::getCssFilePath($blog_id, $device_type);
    is_file($cssFilePath) && unlink($cssFilePath);

    // 適用中のテンプレート取得
    if (\Fc2blog\Model\Model::load('Blogs')->isAppliedTemplate($id, $blog_id, $device_type)) {
      // コメントの表示タイプをテンプレートから判断
      $reply_type = strstr($values['html'], '<%comment_reply_body>') ?
          Config::get('BLOG_TEMPLATE.COMMENT_TYPE.REPLY') : Config::get('BLOG_TEMPLATE.COMMENT_TYPE.AFTER');
      // コメントの表示タイプを更新
      \Fc2blog\Model\Model::load('BlogSettings')->updateReplyType($device_type, $reply_type, $blog_id);
    }

    return true;
  }

  /**
  * HTMLを解読しPHPテンプレートに変換する
  */
  public static function convertFC2Template($html)
  {
    // PHP文のエスケープ
    $delimit = "\xFF";
    $searchs = array('<?', '?>');
    $replaces = array($delimit . 'START_TAG_ESCAPE', $delimit . 'END_TAG_ESCAPE');
    $html = str_replace($searchs, $replaces, $html);

    $html = preg_replace('/(php)/i', "<?php echo '$1'; ?>", $html);

    $searchs = $replaces;
    $replaces = array('<?php echo \'<?\'; ?>', '<?php echo \'?>\'; ?>');
    $html = str_replace($searchs, $replaces, $html);

    // テンプレート置換用変数読み込み
    Config::read('fc2_template.php');
    $ambiguous = array(); // 既存FC2テンプレートの曖昧置換用

    // ループ文用の置き換え
    $loop = Config::get('fc2_template_foreach');
    foreach ($loop as $key => $value) {
      $ambiguous[] = $key;
      do{
        $html= preg_replace('/<!--' . $key . '-->(.*?)<!--\/' . $key . '-->/s', $value . '$1<?php } ?>', $html, -1, $count);
      }while($count);
    }

    // 条件判断文用の置き換え
    $cond = Config::get('fc2_template_if');
    foreach ($cond as $key => $value) {
      $ambiguous[] = $key;
      do{
        $html= preg_replace('/<!--' . $key . '-->(.*?)<!--\/' . $key . '-->/s', $value . '$1<?php } ?>', $html, -1, $count);
      }while($count);
    }

    // 既存FC2テンプレートの曖昧置換
    $ambiguous = implode('|', $ambiguous);
    // <!--topentry--><!--/edit_area--> 左記を許容する
    foreach ($loop as $key => $value) {
      do{
        $html= preg_replace('/<!--' . $key . '-->(.*?)<!--\/(' . $ambiguous . ')-->/s', $value . '$1<?php } ?>', $html, -1, $count);
      }while($count);
    }
    foreach ($cond as $key => $value) {
      do{
        $html= preg_replace('/<!--' . $key . '-->(.*?)<!--\/(' . $ambiguous . ')-->/s', $value . '$1<?php } ?>', $html, -1, $count);
      }while($count);
    }
    // <!--/topentry--><!--topentry--> 左記を許容する(テンプレートによってシンタックスエラーが発生する可能性あり)
    // 代わりに既存FC2で動作しているテンプレートでタグの相互がおかしいテンプレートも動作する
    foreach ($loop as $key => $value) {
      do{
        $html= preg_replace('/<!--\/(' . $ambiguous . ')-->(.*?)<!--' . $key . '-->/s', '<?php } ?>$2' . $value, $html, -1, $count);
      }while($count);
    }
    foreach ($cond as $key => $value) {
      do{
        $html= preg_replace('/<!--\/(' . $ambiguous . ')-->(.*?)<!--' . $key . '-->/s', '<?php } ?>$2' . $value, $html, -1, $count);
      }while($count);
    }

    // 変数の置き換え
    $html = str_replace(Config::get('fc2_template_var_search'), Config::get('fc2_template_var_replace'), $html);
    $html = preg_replace('/<%[0-9a-zA-Z_]+?>/', '', $html);
    return $html;
  }

}

