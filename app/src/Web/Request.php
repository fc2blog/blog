<?php
/**
* リクエストクラス
* POST,GETへのアクセスを便利にするクラス
*/

namespace Fc2blog\Web;

use Fc2blog\Web\Controller\Test\CommonController;

class Request
{
  const VALID_NOT_EMPTY    = 0;    // 空チェック
  const VALID_UNSIGNED_INT = 1;    // 0以上の数値チェック
  const VALID_POSITIVE_INT = 2;    // 1以上の数値チェック
  const VALID_IN_ARRAY     = 3;    // 配列内の値のどれかチェック

  private $path    = '';
  private $query   = '';
  private $request = [];
  private $get     = [];
  private $post    = [];
  private $files   = [];

  public $uri   = "";
  public $method   = "";
  public $session   = [];
  public $server   = [];
  public $env   = [];
  public $cookie   = [];

  public $className = CommonController::class;
  public $methodName = "index";

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
    $this->method = $method ?? $_SERVER['REQUEST_METHOD'];
    $this->uri = $uri ?? $_SERVER["REQUEST_URI"];
    if(isset($_SESSION)) {
      $this->session = $session ?? $_SESSION;
    }
    $this->post = $post ?? $_POST;
    $this->get = $get ?? $_GET;
    $this->files = $files ?? $_FILES;
    $this->server = $server ?? $_SERVER;
    $this->env = $env ?? $_ENV;
    $this->cookie = $cookie ?? $_COOKIE;

    $urls = parse_url($this->uri);
    $this->path = $urls['path'];
    if (isset($urls['query'])) {
      $this->query = $urls['query'];
      parse_str($urls['query'], $this->get);
    }
    $this->request = array_merge($this->get, $this->post);
  }

  // DELME  private static $instance = null;
  // DELME
//  public static function getInstance()
//  {
//    if (self::$instance === null) {
//      self::$instance = new static();
//    }
//    return self::$instance;
//  }
// DELME
//  public static function resetInstanceForTesting()
//  {
//    self::$instance = null;
//  }

  /**
  * リファラーを返却 存在しない場合は空文字を返却
  */
  public static function getReferer(){
    // TODO
    if (!empty($_SERVER->server['HTTP_REFERER'])) {
      return $_SERVER['HTTP_REFERER'];
    }
    return '';
  }

  /**
  * コマンドラインの引数をリクエストに設定
  */
  public function setCronParams($argv=array())
  {
    // DELME
throw new \LogicException("deprecated");
    //    if (count($argv) < 3) {
//      // 1:ファイル名 2:コントローラー名 3:メソッド名 4...引数
//      echo "コントローラー名、メソッド名が指定されておりません\n";
//      echo "cron.php [ControllerName] [MethodName] [...key=value]\n";
//      if(defined("THIS_IS_TEST")){
//        throw new PseudoExit(__FILE__ . ":" . __LINE__ ." ");
//      }else{
//        exit;
//      }
//    }
//    array_shift($argv);
//    $className = array_shift($argv);
//    $methodName = array_shift($argv);
//    $data = array();
//    foreach($argv as $a){
//      list($key, $value) = explode('=', $a);
//      $data[$key] = $value;
//    }
//    $data[Config::get('ARGS_CONTROLLER')] = $className;
//    $data[Config::get('ARGS_ACTION')] = $methodName;
//    $this->request = $data;
  }

//  public function getRequest()
//  {
//    return $this->request;
//  }

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

//  public function getGet()
//  {
//    return $this->get;
//  }

//  public function getPost()
//  {
//    return $this->post;
//  }

//  /**
//  * $_FILESの中身を加工して取得する
//  */
//  public function file($key, $default=null)
//  {
//    if (!isset($this->files[$key])) {
//      return $default;
//    }
//    $file = $this->files[$key];
//    if (!is_array($file['tmp_name'])){
//      return $file;
//    }
//    $files = array();
//    $keys = array_keys($file['tmp_name']);
//    foreach ($keys as $key) {
//      foreach ($file as $colomn => $value) {
//        $files[$key][$colomn] = $value[$key];
//      }
//    }
//    return $files;
//  }

  public function get($key, $default=null, $valid=self::VALID_NOT_EMPTY, $options=null)
  {
    // .区切りのキーを解釈
    $data = $this->request;
    $keys = explode('.', $key);
    foreach($keys as $k){
      if(!isset($data[$k])){
        return $default;
      }
      $data = $data[$k];
    }

    // 値のチェック
    switch($valid){
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
  */
  private function is_integer($int)
  {
    return ((string)intval($int) === (string)$int);
  }


  /**
  * 引数が存在するかチェック
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
  */
  public function set($key, $value)
  {
    if (strpos($key, '.')===false) {
      $this->request[$key] = $value;
      return ;
    }
    $keys = explode('.', $key);
    $this->_set($keys, $value, $this->request, count($keys));
  }

  private function _set($keys, $value, &$request, $count, $i=0)
  {
    $key = $keys[$i];
    if ($count == 1) {
      $request[$key] = $value;
      return ;
    }
    if (empty($request[$key]) || !is_array($request[$key])) {
      $request[$key] = array();
    }
    $this->_set($keys, $value, $request[$key], $count-1, $i+1);
  }

  public function delete($key)
  {
    if (strpos($key, '.')===false) {
      unset($this->request[$key]);
      return ;
    }
    $keys = explode('.', $key);
    $this->_delete($keys, $this->request, count($keys));
  }

  private function _delete($keys, &$request, $count, $i=0)
  {
    $key = $keys[$i];
    if ($count == 1) {
      unset($request[$key]);
      return ;
    }
    if (empty($request[$key]) || !is_array($request[$key])) {
      return ;
    }
    $this->_delete($keys, $request[$key], $count-1, $i+1);
  }

  /**
   * @deprecated
   */
  public function clear()
  {
    throw new \LogicException("deprecated");
//    $this->get     = array();
//    $this->post    = array();
//    $this->request = array();
//    $this->files   = array();
  }

  /**
  * キーの入れ替えを行う
  */
  public function combine($comb=array())
  {
    foreach($comb as $key => $value){
      $this->set($value, $this->get($key));
      $this->delete($key);
    }
  }

  // DELME
//  /**
//   * リクエストメソッドの判定を行う
//   * @param string $method
//   * @return bool
//   */
//  private function is(string $method): bool
//  {
//    return ($_SERVER["REQUEST_METHOD"] === $method);
//  }

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
  public function rawGet(string $key, $default=null)
  {
    return isset($this->get[$key]) ? $this->get[$key] : $default;
  }

  /**
   * @param string $key
   * @return bool
   */
  public function rawHasGet(string $key):bool
  {
    return isset($this->get[$key]);
  }

  /**
   * @param string $key
   * @param string|array|null $default
   * @return string|array|null
   */
  public function rawPost(string $key, $default=null)
  {
    return isset($this->post[$key]) ? $this->post[$key] : $default;
  }

  /**
   * @param string $key
   * @return bool
   */
  public function rawHasPost(string $key):bool
  {
    return isset($this->post[$key]);
  }

  /**
   * @param string $key
   * @param string|array|null $default
   * @return string|array|null
   */
  public function rawCookie(string $key, $default=null)
  {
    return isset($this->cookie[$key]) ? $this->cookie[$key] : $default;
  }

  /**
   * @param string $key
   * @return bool
   */
  public function rawHasCookie(string $key):bool
  {
    return isset($this->cookie[$key]);
  }

}
