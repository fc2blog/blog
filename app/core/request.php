<?php
/**
* リクエストクラス
* POST,GETへのアクセスを便利にするクラス
*/

class Request
{

  const VALID_NOT_EMPTY    = 0;    // 空チェック
  const VALID_UNSIGNED_INT = 1;    // 0以上の数値チェック
  const VALID_POSITIVE_INT = 2;    // 1以上の数値チェック
  const VALID_IN_ARRAY     = 3;    // 配列内の値のどれかチェック

  private $path    = '';
  private $query   = '';
  private $request = array();
  private $get     = array();
  private $post    = array();
  private $files   = null;

  private static $instance = null;

  private function __construct()
  {
    $request_uri = $_SERVER['REQUEST_URI'];
    $urls = parse_url($request_uri);
    $this->path = $urls['path'];
    if (isset($urls['query'])) {
      $this->query = $urls['query'];
      parse_str($urls['query'], $this->get);
    }
    $this->post = $_POST;
    $this->request = array_merge($this->get, $this->post);
    $this->files = $_FILES;
  }

  public static function getInstance()
  {
    if (self::$instance === null) {
      self::$instance = new Request();
    }
    return self::$instance;
  }

  /**
  * リファラーを返却 存在しない場合は空文字を返却
  */
  public static function getReferer(){
    if (!empty($_SERVER['HTTP_REFERER'])) {
      return $_SERVER['HTTP_REFERER'];
    }
    return '';
  }

  /**
  * コマンドラインの引数をリクエストに設定
  */
  public function setCronParams($argv=array())
  {
    if (count($argv) < 3) {
      // 1:ファイル名 2:コントローラー名 3:メソッド名 4...引数
      echo "コントローラー名、メソッド名が指定されておりません\n";
      echo "cron.php [ControllerName] [MethodName] [...key=value]\n";
      exit;
    }
    array_shift($argv);
    $className = array_shift($argv);
    $methodName = array_shift($argv);
    $data = array();
    foreach($argv as $a){
      list($key, $value) = explode('=', $a);
      $data[$key] = $value;
    }
    $data[Config::get('ARGS_CONTROLLER')] = $className;
    $data[Config::get('ARGS_ACTION')] = $methodName;
    $this->request = $data;
  }

  public function getRequest()
  {
    return $this->request;
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

  public function getPost()
  {
    return $this->post;
  }

  /**
  * $_FILESの中身を加工して取得する
  */
  public function file($key, $default=null)
  {
    if (!isset($this->files[$key])) {
      return $default;
    }
    $file = $this->files[$key];
    if (!is_array($file['tmp_name'])){
      return $file;
    }
    $files = array();
    $keys = array_keys($file['tmp_name']);
    foreach ($keys as $key) {
      foreach ($file as $colomn => $value) {
        $files[$key][$colomn] = $value[$key];
      }
    }
    return $files;
  }

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

  public function clear()
  {
    $this->get     = array();
    $this->post    = array();
    $this->request = array();
    $this->files   = array();
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

  /**
   * リクエストメソッドの判定を行う
   * @param string $method
   * @return bool
   */
  private function is(string $method): bool
  {
    return ($_SERVER["REQUEST_METHOD"] === $method);
  }

  /**
   * リクエストメソッドがGETか判定を行う
   * @return bool
   */
  public function isGet(): bool
  {
    return $this->is('GET');
  }

}
