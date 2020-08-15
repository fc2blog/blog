<?php
/**
* Sessionクラス
*/

namespace Fc2blog\Web;

use Fc2blog\Config;

class Session
{

  private static $isStart = false;

  private function __construct(){}

  public static function start()
  {
    if (self::$isStart) {
      return ;
    }
    if (headers_sent()) {
      return;
    }

    $session_cookie_options = [
      "lifetime" => 0,
      "path" => "/",
      "httponly" => true,
      "samesite" => "Lax" // NOTE: 要件としてhttpサポートがあるため、None属性は指定ができない
      // NOTE: 要件としてhttpサポートがあるため secure属性は指定ができない
    ];

    if (strlen(Config::get('SESSION_DEFAULT_DOMAIN')) > 0) {
      $session_cookie_options['domain'] = Config::get('SESSION_DEFAULT_DOMAIN');
    }

    session_set_cookie_params($session_cookie_options);
    session_name(Config::get('SESSION_NAME'));
    session_start();
    self::$isStart = true;
  }

  /**
  * セッションから情報を取得する
  */
  public static function get($key, $default=null)
  {
    self::start();
    if (isset($_SESSION[$key])){
      return $_SESSION[$key];
    }
    return $default;
  }

  /**
  * セッションから情報を取得し破棄する
  */
  public static function remove($key, $default=null)
  {
    self::start();
    if (isset($_SESSION[$key])){
      $default = $_SESSION[$key];
      unset($_SESSION[$key]);
    }
    return $default;
  }

  /**
  * セッションに情報を保存する
  */
  public static function set($key, $value)
  {
    self::start();
    $_SESSION[$key] = $value;
  }

  /**
  * セッションID置き換え
  */
  public static function regenerate()
  {
    self::start();
    if (version_compare(PHP_VERSION, '5.1.0')>=0) {
      session_regenerate_id(true);
    } else {
      $sess_id = session_id();
      session_regenerate_id();
      unlink(session_save_path() . DIRECTORY_SEPARATOR . 'sess_' . $sess_id);
    }
  }

  /**
  * セッションを破棄
  */
  public static function destroy()
  {
    $_SESSION = array();
    if (isset($_COOKIE[Config::get('SESSION_NAME')])) {
      \Fc2blog\Web\Cookie::remove(Config::get('SESSION_NAME'), '');
    }
    session_destroy();
  }

}

