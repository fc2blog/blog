<?php
/**
 * リクエストクラス
 * POST,GETへのアクセスを便利にするクラス
 */

namespace Fc2blog\Web;

use Fc2blog\App;
use Fc2blog\Util\I18n;
use Fc2blog\Util\Log;
use Fc2blog\Web\Controller\Test\CommonController;
use Fc2blog\Web\Router\Router;

class Request
{
  const VALID_NOT_EMPTY = 0;    // 空チェック
  const VALID_UNSIGNED_INT = 1;    // 0以上の数値チェック
  const VALID_POSITIVE_INT = 2;    // 1以上の数値チェック
  const VALID_IN_ARRAY = 3;    // 配列内の値のどれかチェック

  private $path;
  private $query = '';
  private $request;
  private $get;
  private $post;
  private $files;

  public $uri = "";
  public $method = "";
  public $session = [];
  public $server = [];
  public $env = [];
  public $cookie = [];

  public $className = CommonController::class;
  public $methodName = "index";
  public $shortControllerName = "Common";
  public $lang = "";
  public $deviceType="";
  public $urlRewrite = true;
  public $baseDirectory = "/";

  public function __construct(
    string $method = null,
    string $uri = null,
    array $session = null,
    array $post = null,
    array $get = null,
    array $files = null,
    array $server = null,
    array $env = null,
    array $cookie = null
  )
  {
    $this->method = $method ?? $_SERVER['REQUEST_METHOD'] ?? "GET";
    $this->uri = $uri ?? $_SERVER["REQUEST_URI"] ?? "GET";
    Session::start();
    if (isset($_SESSION)) {
      $this->session = $session ?? $_SESSION;
    }
    $this->post = $post ?? $_POST;
    $this->get = $get ?? $_GET;
    $this->files = $files ?? $_FILES;
    $this->server = $server ?? $_SERVER;
    if(!isset($this->server['HTTP_USER_AGENT'])){
      $this->server['HTTP_USER_AGENT'] = "";
    }
    $this->env = $env ?? $_ENV;
    $this->cookie = $cookie ?? $_COOKIE;

    $urls = parse_url($this->uri);
    $this->path = $urls['path'];
    if (isset($urls['query'])) {
      $this->query = $urls['query'];
      parse_str($urls['query'], $this->get);
    }
    $this->request = array_merge($this->get, $this->post);

    // リクエストからの言語規定
    $this->lang = I18n::setLanguage($this);
    // リクエストからのデバイス種類規定
    $this->deviceType = App::getDeviceType($this);

    // ルートの解決
    $router = new Router($this);
    $resolve = $router->resolve();

    $this->methodName = $resolve['methodName']; // ここまでRequestに持たせるのは少々責務範囲が広いか？
    $this->className = $resolve['className']; // ここまでRequestに持たせるのは少々責務範囲が広いか？
    { // Common など短いコントローラ名の生成
      $classNamePathList = explode('\\', $this->className);
      $className = $classNamePathList[count($classNamePathList) - 1];
      $this->shortControllerName = explode('Controller', $className)[0];
    }

    // デバッグ用アクセス（リゾルブ結果）ログ
    Log::debug_log(__FILE__ . ":" . __LINE__ . " Controller[{$this->className}] Method[{$this->methodName}] Device[{$this->deviceType}]");
  }

  /**
   * リファラーを返却 存在しない場合は空文字を返却
   */
  public static function getReferer()
  {
    // TODO
    if (!empty($_SERVER['HTTP_REFERER'])) {
      return $_SERVER['HTTP_REFERER'];
    }
    return '';
  }

  public function getPath()
  {
    return $this->path;
  }

  public function getPaths()
  {
    return explode('/', trim($this->path, '/'));
  }

  public function getQuery()
  {
    return $this->query;
  }

  public function getGet()
  {
    return $this->get;
  }

  /**
   * $_FILESの中身を加工して取得する
   * @param $key
   * @param null $default
   * @return array|mixed|null
   */
  public function file($key, $default = null)
  {
    if (!isset($this->files[$key])) {
      return $default;
    }
    $file = $this->files[$key];
    if (!is_array($file['tmp_name'])) {
      return $file;
    }
    $files = array();
    $keys = array_keys($file['tmp_name']);
    foreach ($keys as $key) {
      foreach ($file as $column => $value) {
        $files[$key][$column] = $value[$key];
      }
    }
    return $files;
  }

  public function get($key, $default = null, $valid = self::VALID_NOT_EMPTY, $options = null)
  {
    // .区切りのキーを解釈
    $data = $this->request;
    $keys = explode('.', $key);
    foreach ($keys as $k) {
      if (!isset($data[$k])) {
        return $default;
      }
      $data = $data[$k];
    }

    // 値のチェック
    switch ($valid) {
      default:
      case self::VALID_NOT_EMPTY:
        if ($data === "" || $data === null) {
          return $default;
        }
        break;

      // 0以上のint型のみ許可
      case self::VALID_UNSIGNED_INT:
        if (!$this->is_integer($data)) {
          return $default;
        }
        if ($data >= 0) {
          return intval($data);
        }
        return $default;

      // 1以上のint型のみ許可
      case self::VALID_POSITIVE_INT:
        if (!$this->is_integer($data)) {
          return $default;
        }
        if ($data >= 1) {
          return intval($data);
        }
        return $default;

      // 配列内の値判定
      case self::VALID_IN_ARRAY:
        if (in_array($data, $options)) {
          return $data;
        }
        return $default;

    }
    return $data;
  }


  /**
   * intデータかチェック
   * @param $int
   * @return bool
   */
  private function is_integer($int)
  {
    return ((string)intval($int) === (string)$int);
  }


  /**
   * 引数が存在するかチェック
   * @param $key
   * @return bool
   */
  public function isArgs($key)
  {
    // .区切りのキーを解釈
    $data = $this->request;
    $keys = explode('.', $key);
    $count = count($keys);
    for ($i = 0; $i < $count; $i++) {
      if (!isset($data[$keys[$i]])) {
        return false;
      }
      $data = $data[$keys[$i]];
    }
    return true;
  }

  /**
   * 値をリクエストデータに設定する
   * @param $key
   * @param $value
   */
  public function set($key, $value)
  {
    if (strpos($key, '.') === false) {
      $this->request[$key] = $value;
      return;
    }
    $keys = explode('.', $key);
    $this->_set($keys, $value, $this->request, count($keys));
  }

  private function _set($keys, $value, &$request, $count, $i = 0)
  {
    $key = $keys[$i];
    if ($count == 1) {
      $request[$key] = $value;
      return;
    }
    if (empty($request[$key]) || !is_array($request[$key])) {
      $request[$key] = array();
    }
    $this->_set($keys, $value, $request[$key], $count - 1, $i + 1);
  }

  public function delete($key)
  {
    if (strpos($key, '.') === false) {
      unset($this->request[$key]);
      return;
    }
    $keys = explode('.', $key);
    $this->_delete($keys, $this->request, count($keys));
  }

  private function _delete($keys, &$request, $count, $i = 0)
  {
    $key = $keys[$i];
    if ($count == 1) {
      unset($request[$key]);
      return;
    }
    if (empty($request[$key]) || !is_array($request[$key])) {
      return;
    }
    $this->_delete($keys, $request[$key], $count - 1, $i + 1);
  }

  /**
   * キーの入れ替えを行う
   * @param array $comb
   */
  public function combine($comb = array())
  {
    foreach ($comb as $key => $value) {
      $this->set($value, $this->get($key));
      $this->delete($key);
    }
  }

  /**
   * リクエストメソッドがGETか判定を行う
   * @return bool
   */
  public function isGet(): bool
  {
    return $this->method === 'GET';
  }

  /**
   * @param string $key
   * @param string|array|null $default
   * @return string|array|null
   */
  public function rawGet(string $key, $default = null)
  {
    return isset($this->get[$key]) ? $this->get[$key] : $default;
  }

  /**
   * @param string $key
   * @return bool
   */
  public function rawHasGet(string $key): bool
  {
    return isset($this->get[$key]);
  }

  /**
   * @param string $key
   * @param string|array|null $default
   * @return string|array|null
   */
  public function rawPost(string $key, $default = null)
  {
    return isset($this->post[$key]) ? $this->post[$key] : $default;
  }

  /**
   * @param string $key
   * @return bool
   */
  public function rawHasPost(string $key): bool
  {
    return isset($this->post[$key]);
  }

  /**
   * @param string $key
   * @param string|array|null $default
   * @return string|array|null
   */
  public function rawCookie(string $key, $default = null)
  {
    return isset($this->cookie[$key]) ? $this->cookie[$key] : $default;
  }

  /**
   * @param string $key
   * @return bool
   */
  public function rawHasCookie(string $key): bool
  {
    return isset($this->cookie[$key]);
  }

  /**
   * @return array
   */
  public function getData(): array
  {
    return $this->request;
  }

  /**
   * @param string $cookie_name
   * @param string|null $default
   * @return mixed|null
   */
  public function getCookie(string $cookie_name, string $default = null)
  {
    return $this->cookie[$cookie_name] ?? $default;
  }

  public function isValidSig():bool
  {
    if(
      !isset($this->session['sig']) ||
      strlen($this->session['sig'])===0
    ) {
      error_log("session did not have sig.");
      return false;
    }
    if(
      !is_string($this->get('sig', null)) ||
      strlen($this->get('sig', "")===0)
    ){
      error_log("request did not have sig.");
      return false;
    }

    return $this->session['sig'] === $this->get('sig');
  }

  public function generateNewSig():void
  {
    $sig = App::genRandomString();
    $this->session['sig'] = $sig;
    Session::set('sig', $sig);
  }
}
