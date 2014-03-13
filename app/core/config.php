<?php
/**
* 設定クラス
*
*/

class Config{

  private static $config = array();
  private static $read_files = array();

  /**
   * 設定を取得する
   *
   * @param string $key 取得用のキー(MASTER.DB => $config["MASTER"]["DB"] .区切りで取得できる)
   * @param object $default 未設定の場合に返却する値
   */
  public static function get($key, $default=null){
    if (isset(self::$config[$key])) {
      return self::$config[$key];
    }
    $keys = explode('.', $key);
    if (count($keys) > 1 && isset(self::$config[$keys[0]])) {
      $value = self::$config[array_shift($keys)];
      foreach($keys as $v){
        if ($v==='' || !isset($value[$v])) {
          return $default;
        }
        $value = $value[$v];
      }
      return $value;
    }
    return $default;
  }

  public static function set($key, $value){
    self::_set($key, $value, self::$config);
  }

  private static function _set($key, $value, &$config){
    $keys = explode('.', $key);
    $count = count($keys);
    if ($count == 1) {
      $config[$key] = $value;
      return ;
    }
    $key = array_shift($keys);
    self::_set(implode('.', $keys), $value, $config[$key]);
  }

  /**
  * ファイルから環境情報を設定
  */
  public static function read($name){
    if (!empty(self::$read_files[$name])) {
      // 既に読み込み済みのファイルは読み込まない
      return ;
    }
    self::$read_files[$name] = true;
    $configs = include(Config::get('CONFIG_DIR') . $name);
    foreach($configs as $key => $value){
      self::$config[$key] = $value;
    }
  }

}

