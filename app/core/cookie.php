<?php
/**
* Cookieクラス
*/

class Cookie{

  /**
  * クッキーから情報を取得する
  */
  public static function get($key, $default=null){
    if (isset($_COOKIE[$key])) {
      return $_COOKIE[$key];
    }
    return $default;
  }

  /**
  * クッキーから情報を取得し破棄する
  */
  public static function remove($key, $default=null){
    $value = self::get($key, $default);
    self::set($key, null);
    return $value;
  }

  /**
  * クッキーに情報を保存する
  */
  public static function set($key, $value, $timeout=0, $path='/', $domain=null){
    if ($domain===null) {
      $domain = Config::get('COOKIE_DEFAULT_DOMAIN');
    }
    setcookie($key, $value, $timeout, $path, $domain);
  }

}
