<?php
/**
 * Controllerの親クラス
 */

namespace Fc2blog\Web\Controller;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Debug;
use Fc2blog\Exception\PseudoExit;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;

abstract class Controller
{
  private $data = array();            // テンプレートへ渡す変数の保存領域
  protected $layout = 'default.php';  // 表示ページのレイアウトテンプレート
  protected $output = '';             // 出力タグ

  public function __construct(Request $request, $method)
  {
    $className = get_class($this);

    { // PSR-4 対応のためのTweak
      // namespace付きクラス名から、クラス名へ
      $classNamePathList = explode('\\', $className);
      $className = $classNamePathList[count($classNamePathList) - 1];
    }

    // コントローラー名の設定（後でアクセス許可判定などに使われる
    $controllerName = explode('Controller', $className);
    Config::set('ControllerName', $controllerName[0]);

    // メソッド名の設定（後でアクセス許可判定などに使われる
    Config::set('ActionName', $method);

    // デバイスタイプの設定（TODO ここでなくても良さそうだが
    Config::set('DeviceType', App::getDeviceType($request));

    // アプリプレフィックス、テンプレートファイル名決定に使われる
    $prefix = Config::get('APP_PREFIX'); // TODO Request に持たせられそう

    Debug::log('Prefix[' . $prefix . '] Controller[' . $className . '] Method[' . $method . '] Device[' . Config::get('DeviceType') . ']', false, 'system', __FILE__, __LINE__);

    $this->beforeFilter($request);

    // アクションの実行(返り値はテンプレートファイル名)
    $template = $this->$method($request);

    // 空の場合は、規約に則ってテンプレートファイルを決定する
    if (empty($template)) {
      $template = substr($className, 0, strlen($className) - strlen('Controller')) . '/' . $method . '.php';
    }

    // 後での置換のため、出力を一時変数へ
    ob_start();
    $this->layout($request, $template);
    $this->output = ob_get_clean();

    // SSI的なインクルード処理など
    $this->beforeRender();

    // 結果を出力
    echo $this->output;
  }

  protected function beforeFilter(Request $request)
  {
  }

  protected function beforeRender()
  {
  }

  public function set($key, $value)
  {
    $this->data[$key] = $value;
  }

  /**
   * リダイレクト
   * MEMO: Blog idが特定できないときの強制的なSchemaがさだまらない
   * @param Request $request
   * @param $url
   * @param string $hash
   * @param bool $full_url BlogIdが特定できるとき、http(s)://〜からのフルURLを出力する、HTTP<>HTTPS強制リダイレクト時に必要
   * @param string|null $blog_id
   */
  protected function redirect(Request $request, $url, $hash = '', bool $full_url = false, string $blog_id = null)
  {
    if (is_array($url)) {
      $url = Html::url($request, $url, false, $full_url);

    } else if ($full_url && is_string($blog_id) && strlen($blog_id) > 0) {
      $url = BlogsModel::getFullHostUrlByBlogId($blog_id) . $url;

    } else if ($full_url && preg_match("|\A/([^/]+)/|u", $url, $match)) {
      // Blog idをURLから抜き出して利用
      $url = BlogsModel::getFullHostUrlByBlogId($match[1]) . $url;
      $blog_id = $match[1];
    }
    $url .= $hash;

    // デバッグ時にSessionにログを保存
    Debug::log('Redirect[' . $url . ']', false, 'system', __FILE__, __LINE__);
    Debug::setSessionLogs();

    if (!is_null($blog_id) && $full_url) {
      $status_code = BlogsModel::getRedirectStatusCodeByBlogId($blog_id);
    } else {
      $status_code = 302;
    }
    if (!headers_sent()) {
      // full url指定時のリダイレクトは、Blogの設定がもつステータスコードを利用する
      header('Location: ' . $url, true, $status_code);
    }
    $escaped_url = h($url);
    echo "redirect to {$escaped_url} status code:{$status_code}";
    if (defined("THIS_IS_TEST")) {
      throw new PseudoExit(__FILE__ . ":" . __LINE__ . " redirect to {$escaped_url} status code:{$status_code}");
    } else {
      exit;
    }
  }

  /**
   * 前のURLに戻す リファラーが取れなければ引数のURLに飛ばす
   * @param Request $request
   * @param $url
   * @param string $hash
   */
  protected function redirectBack(Request $request, $url, $hash = '')
  {
    // 元のURLに戻す
    if (!empty($_SERVER['HTTP_REFERER'])) {
      $this->redirect($request, $_SERVER['HTTP_REFERER']);
    }
    // リファラーが取れなければメインへ飛ばす
    $this->redirect($request, $url, $hash);
  }

  private function layout(Request $request, $fw_template)
  {
    // 定義済み変数に関しては展開させない
    unset($this->data['fw_template']);

    // 設定されているdataを展開
    extract($this->data);

    // アプリプレフィックス
    $prefix = strtolower(Config::get('APP_PREFIX'));

    Debug::log('Layout[' . $this->layout . ']', false, 'system', __FILE__, __LINE__);
    if ($this->layout == '') {
      // layoutが空の場合は表示処理を行わない
      return;
    }

    $fw_template_path = Config::get('VIEW_DIR') . ($prefix ? $prefix . '/' : '') . 'layouts/' . $this->layout;
    $fw_template_device_path = preg_replace('/^(.*?)\.([^\/\.]*?)$/', '$1' . Config::get('DEVICE_PREFIX.' . Config::get('DeviceType')) . '.$2', $fw_template_path);
    if (is_file($fw_template_device_path)) {
      // デバイス毎のファイルがあればデバイス毎のファイルを優先する
      include($fw_template_device_path);
    } elseif (is_file($fw_template_path)) {
      include($fw_template_path);
    } else {
      Debug::log('Not Found Layout[' . $fw_template_path . ']', false, 'error', __FILE__, __LINE__);
    }
  }

  /**
   * 画面表示処理
   * @param Request $request
   * @param $fw_template
   * @param array $fw_data
   * @param bool $fw_is_prefix
   */
  public function display(Request $request, $fw_template, $fw_data = array(), $fw_is_prefix = true)
  {
    $fw_template = snakeCase($fw_template);
    // データの設定
    if (count($fw_data)) {
      // 定義済み変数に関しては展開させない
      unset($fw_data['fw_template']);
      unset($fw_data['fw_is_prefix']);

      // fw_dataがある場合は渡された値のみ展開
      extract($fw_data);
    } else {
      // 定義済み変数に関しては展開させない
      unset($this->data['fw_template']);
      unset($this->data['fw_is_prefix']);

      // displayDataが無い場合はControllerのdataを展開
      extract($this->data);
    }
    // 展開完了後fw_dataは解除
    unset($fw_data);

    // Debug用にテンプレートで使用可能な変数一覧表示
    if (Config::get('DEBUG_TEMPLATE_VARS')) {
      include(Config::get('VIEW_DIR') . 'Common/variables.php');
    }

    // Template表示
    $fw_template_path = Config::get('VIEW_DIR') . ($fw_is_prefix ? strtolower(Config::get('APP_PREFIX')) . '/' : '') . $fw_template;
    $fw_template_device_path = preg_replace('/^(.*?)\.([^\/\.]*?)$/', '$1' . Config::get('DEVICE_PREFIX.' . Config::get('DeviceType')) . '.$2', $fw_template_path);
    if (is_file($fw_template_device_path)) {
      // デバイス毎のファイルがあればデバイス毎のファイルを優先する
      include($fw_template_device_path);
    } elseif (is_file($fw_template_path)) {
      Debug::log('Template[' . $fw_template_path . ']', false, 'system', __FILE__, __LINE__);
      include($fw_template_path);
    } else {
      Debug::log('Not Found Template[' . $fw_template_path . ']', false, 'error', __FILE__, __LINE__);
    }
  }

  /**
   * 表示処理データを取得
   * @param Request $request
   * @param $template
   * @param array $data
   * @param bool $isPrefix
   * @return false|string
   */
  public function fetch(Request $request, $template, $data = array(), $isPrefix = true)
  {
    ob_start();
    $this->display($request, $template, $data, $isPrefix);
    $html = ob_get_clean();
    return $html;
  }

  // 存在しないアクションは404へ
  public function __call($name, $arguments)
  {
    return $this->error404();
  }

  // 404 NotFound Action
  public function error404()
  {
    return 'Common/error404.php';
  }
}

