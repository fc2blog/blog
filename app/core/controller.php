<?php
/**
* Controllerの親クラス
*/

require_once(\Fc2blog\Config::get('MODEL_DIR') . 'model.php');

abstract class Controller
{

  private $data = array();             // テンプレートへ渡す変数の保存領域
  protected $layout = 'default.html';  // 表示ページのレイアウトテンプレート
  protected $output = '';              // 出力タグ

  public function __construct($method)
  {
    $className = get_class($this);

    // コントローラー名の設定
    $controllerName = explode('Controller', $className);
    \Fc2blog\Config::set('ControllerName', $controllerName[0]);

    // メソッド名の設定
    \Fc2blog\Config::set('ActionName', $method);

    // デバイスタイプの設定
    \Fc2blog\Config::set('DeviceType', \Fc2blog\App::getDeviceType());

    // アプリプレフィックス
    $prefix = \Fc2blog\Config::get('APP_PREFIX');

    \Fc2blog\Debug::log('Prefix[' . $prefix . '] Controller[' . $className . '] Method[' . $method . '] Device[' . \Fc2blog\Config::get('DeviceType') . ']', false, 'system', __FILE__, __LINE__);

    $this->beforeFilter();

    $template = $this->$method();
    if (empty($template)) {
      $template = substr($className, 0, strlen($className) - strlen('Controller')) . '/' . $method . '.html';
    }

    $this->afterFilter();

    ob_start();
    $this->layout($template);
    $this->output = ob_get_clean();

    $this->beforeRender();

    // 結果を出力
    echo $this->output;
  }

  protected function beforeFilter(){}

  protected function afterFilter(){}

  protected function beforeRender(){}

  public function set($key, $value)
  {
    $this->data[$key] = $value;
  }

  /**
   * リダイレクト
   * MEMO: Blog idが特定できないときの強制的なSchemaがさだまらない
   * @param $url
   * @param string $hash
   * @param bool $full_url BlogIdが特定できるとき、http(s)://〜からのフルURLを出力する、HTTP<>HTTPS強制リダイレクト時に必要
   * @param string|null $blog_id
   * @throws \Fc2blog\Exception\PseudoExit
   */
  protected function redirect($url, $hash = '', bool $full_url = false, string $blog_id = null)
  {
    if (is_array($url)) {
      $url = \Fc2blog\Web\Html::url($url, false, $full_url);

    } else if ($full_url && is_string($blog_id) && strlen($blog_id) > 0) {
      $url = BlogsModel::getFullHostUrlByBlogId($blog_id) . $url;

    } else if ($full_url && preg_match("|\A/([^/]+)/|u", $url, $match)) {
      // Blog idをURLから抜き出して利用
      $url = BlogsModel::getFullHostUrlByBlogId($match[1]) . $url;
      $blog_id = $match[1];
    }
    $url .= $hash;

    // デバッグ時にSessionにログを保存
    \Fc2blog\Debug::log('Redirect[' . $url . ']', false, 'system', __FILE__, __LINE__);
    \Fc2blog\Debug::setSessionLogs();

    if(!is_null($blog_id) && $full_url) {
      $status_code = BlogsModel::getRedirectStatusCodeByBlogId($blog_id);
    }else{
      $status_code = 302;
    }
    if (!headers_sent()) {
      // full url指定時のリダイレクトは、Blogの設定がもつステータスコードを利用する
      header('Location: ' . $url, true, $status_code);
    }
    $escaped_url = h($url);
    echo "redirect to {$escaped_url} status code:{$status_code}";
    if(defined("THIS_IS_TEST")){
      throw new \Fc2blog\Exception\PseudoExit(__FILE__ . ":" . __LINE__ ." redirect to {$escaped_url} status code:{$status_code}");
    }else{
      exit;
    }
  }

  /**
  * 前のURLに戻す リファラーが取れなければ引数のURLに飛ばす
  */
  protected function redirectBack($url, $hash='')
  {
    // 元のURLに戻す
    if (!empty($_SERVER['HTTP_REFERER'])) {
      $this->redirect($_SERVER['HTTP_REFERER']);
    }
    // リファラーが取れなければメインへ飛ばす
    $this->redirect($url, $hash);
  }

  private function layout($fw_template)
  {
    // 定義済み変数に関しては展開させない
    unset($this->data['fw_template']);

    // 設定されているdataを展開
    extract($this->data);

    // アプリプレフィックス
    $prefix = \Fc2blog\Config::get('APP_PREFIX');

    \Fc2blog\Debug::log('Layout[' . $this->layout . ']', false, 'system', __FILE__, __LINE__);
    if ($this->layout=='') {
      // layoutが空の場合は表示処理を行わない
      return ;
    }

    $fw_template_path = \Fc2blog\Config::get('VIEW_DIR') . ($prefix ? $prefix . '/' : '') . 'layouts/' . $this->layout;
    $fw_template_device_path = preg_replace('/^(.*?)\.([^\/\.]*?)$/', '$1' . \Fc2blog\Config::get('DEVICE_PREFIX.' . \Fc2blog\Config::get('DeviceType')) . '.$2', $fw_template_path);
    if (is_file($fw_template_device_path)) {
      // デバイス毎のファイルがあればデバイス毎のファイルを優先する
      include($fw_template_device_path);
    } elseif (is_file($fw_template_path)) {
      include($fw_template_path);
    } else {
      \Fc2blog\Debug::log('Not Found Layout[' . $fw_template_path . ']', false, 'error', __FILE__, __LINE__);
    }
  }

  /**
  * 画面表示処理
  */
  public function display($fw_template, $fw_data=array(), $fw_is_prefix=true)
  {
    $fw_template = snakeCase($fw_template);
    // データの設定
    if (count($fw_data)) {
      // 定義済み変数に関しては展開させない
      unset($fw_data['fw_template']);
      unset($fw_data['fw_is_prefix']);

      // fw_dataがある場合は渡された値のみ展開
      extract($fw_data);
    }else{
      // 定義済み変数に関しては展開させない
      unset($this->data['fw_template']);
      unset($this->data['fw_is_prefix']);

      // displayDataが無い場合はControllerのdataを展開
      extract($this->data);
    }
    // 展開完了後fw_dataは解除
    unset($fw_data);

    // リクエストデータ
    $request = \Fc2blog\Request::getInstance();

    // Debug用にテンプレートで使用可能な変数一覧表示
    if (\Fc2blog\Config::get('DEBUG_TEMPLATE_VARS')) {
      include(\Fc2blog\Config::get('VIEW_DIR') . 'Common/variables.html');
    }

    // Template表示
    $fw_template_path = \Fc2blog\Config::get('VIEW_DIR') . ($fw_is_prefix ? \Fc2blog\Config::get('APP_PREFIX') . '/' : '') . $fw_template;
    $fw_template_device_path = preg_replace('/^(.*?)\.([^\/\.]*?)$/', '$1' . \Fc2blog\Config::get('DEVICE_PREFIX.' . \Fc2blog\Config::get('DeviceType')) . '.$2', $fw_template_path);
    if (is_file($fw_template_device_path)) {
      // デバイス毎のファイルがあればデバイス毎のファイルを優先する
      include($fw_template_device_path);
    } elseif (is_file($fw_template_path)) {
      \Fc2blog\Debug::log('Template[' . $fw_template_path . ']', false, 'system', __FILE__, __LINE__);
      include($fw_template_path);
    }else{
      \Fc2blog\Debug::log('Not Found Template[' . $fw_template_path . ']', false, 'error', __FILE__, __LINE__);
    }
  }

  /**
  * 表示処理データを取得
  */
  public function fetch($template, $data=array(), $isPrefix=true)
  {
    ob_start();
    $this->display($template, $data, $isPrefix);
    $html = ob_get_clean();
    return $html;
  }

  public function __call($name, $arguments)
  {
    return $this->error404();
  }

  public function error404()
  {
    return 'Common/error404.html';
  }

}

