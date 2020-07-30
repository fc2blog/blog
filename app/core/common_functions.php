<?php

// lcfirst関数補完
if(function_exists('lcfirst') === false) {
  function lcfirst($str) {
    $str[0] = strtolower($str[0]);
    return $str;
  }
}

/**
* 共通関数群
*/

/**
* HTMLエスケープの短縮形
*/
function h($text){
  return htmlentities($text, ENT_QUOTES, Config::get('INTERNAL_ENCODING'));
}


/**
* マルチバイト対応のtruncate
*/
function t($text, $length=10, $etc='...'){
  if (!$length) {
    return '';
  }
  if (mb_strlen($text, Config::get('INTERNAL_ENCODING')) > $length) {
    return mb_substr($text, 0, $length, Config::get('INTERNAL_ENCODING')) . $etc;
  }
  return $text;
}

/**
* 対象の内容が空文字列の場合代替内容を返却する
*/
function d($text, $default){
  if ($text===null || $text==='') {
    return $default;
  }
  return $text;
}

/**
* t,hのエイリアス
*/
function th($text, $length=10, $etc='...'){
  return h(t($text, $length, $etc));
}

/**
* URLのエンコードエイリアス
*/
function ue($text){
  return rawurlencode($text);
}

/**
* 日付のフォーマット変更
*/
function df($date, $format='Y/m/d H:i:s'){
  return date($format, strtotime($date));
}


/**
* 多言語化 TODO:後で独自実装関数とすげ替える予定
*/
function __($msg){
  return _($msg);
}

/**
* パスカルケースへの変換
*/
function pascalCase($snake_case)
{
  $snake_case = str_replace('_', ' ', $snake_case);
  $snake_case = ucwords($snake_case);
  $snake_case = str_replace(' ', '', $snake_case);
  return $snake_case;
}

/**
* キャメルケースへの変換
*/
function camelCase($snake_case)
{
  return lcfirst(pascalCase($snake_case));
}

/**
* スネークケースへの変換
*/
function snakeCase($camel_case)
{
  return strtolower(preg_replace("/([A-Z])/u", "_$0", lcfirst($camel_case)));
}

/**
* ルーティング用メソッド
*/
function getRouting(){
  require(Config::get('CONFIG_DIR') . Config::get('ROUTING'));  // ルーティング設定を読み込み

  $request = Request::getInstance();

  $defaultClass  = Config::get('DEFAULT_CLASS_NAME');
  $defaultMethod = Config::get('DEFAULT_METHOD_NAME');
  $prefix        = Config::get('APP_PREFIX');

  $denyClass   = $prefix ? $prefix . 'Controller' : 'AppController';
  $denyMethod  = array('process', 'display', 'fetch', 'set');
  $denyPattern = array('CommonController'=>array('install'));

  $argsc = Config::get('ARGS_CONTROLLER');
  $argsa = Config::get('ARGS_ACTION');

  $className = pascalCase(basename($request->get($argsc)));
  $className = $className ? $className . 'Controller' : $defaultClass;
  $classFile = Config::get('CONTROLLER_DIR') . ($prefix ? $prefix . '/' : '') . snakeCase($className) . '.php';
  $methodName = $request->get($argsa, $defaultMethod);
  $methodName = in_array($methodName, $denyMethod) ? $defaultMethod : $methodName;
  if ($className==$denyClass
    || !is_file($classFile)
    || (isset($denyPattern[$className]) && in_array($methodName, $denyPattern[$className]))
  ) {
    $className = $defaultClass;
    $classFile = Config::get('CONTROLLER_DIR') . ($prefix ? $prefix . '/' : '') . snakeCase($className) . '.php';
    $methodName = $defaultMethod;
  }

  return array($classFile, $className, $methodName);
}

/**
* 言語設定
*/
function setLanguage($lang=null, $file='messages'){
  if ($lang==null) {
    $lang = Cookie::get('lang');
  }
  if ($lang) {
    // 言語チェック
    if ($language=Config::get('LANGUAGES.' . $lang)) {
      Config::set('LANG', $lang);
      Config::set('LANGUAGE', $language);
    }
  }
  // 多言語化対応
  putenv('LANG=' . Config::get('LANGUAGE'));
  setlocale(LC_ALL, Config::get('LANGUAGE'));
  bindtextdomain($file, Config::get('LOCALE_DIR'));
  textdomain($file);
}

/**
 * 引数がcount関数で数えられる値かどうかを調べる
 * ※PHP7.2よりcount関数の引数に数えられない値が指定された場合、E_WARNINGが発生
 * ※PHP7.3より本関数は追加されている
 */
if (!function_exists('is_countable')) {
    function is_countable ($var)
    {
        return (is_array($var) || $var instanceof Countable);
    }
}