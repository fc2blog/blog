<?php
// the file will be load by composer autoloader.

// lcfirst関数補完
if (function_exists('lcfirst') === false) {
  function lcfirst($str)
  {
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
function h($text)
{
  return htmlentities($text, ENT_QUOTES, \Fc2blog\Config::get('INTERNAL_ENCODING'));
}


/**
 * マルチバイト対応のtruncate
 */
function t($text, $length = 10, $etc = '...')
{
  if (!$length) {
    return '';
  }
  if (mb_strlen($text, \Fc2blog\Config::get('INTERNAL_ENCODING')) > $length) {
    return mb_substr($text, 0, $length, \Fc2blog\Config::get('INTERNAL_ENCODING')) . $etc;
  }
  return $text;
}

/**
 * 対象の内容が空文字列の場合代替内容を返却する
 */
function d($text, $default)
{
  if ($text === null || $text === '') {
    return $default;
  }
  return $text;
}

/**
 * t,hのエイリアス
 */
function th($text, $length = 10, $etc = '...')
{
  return h(t($text, $length, $etc));
}

/**
 * URLのエンコードエイリアス
 */
function ue($text)
{
  return rawurlencode($text);
}

/**
 * 日付のフォーマット変更
 */
function df($date, $format = 'Y/m/d H:i:s')
{
  return date($format, strtotime($date));
}


/**
 * 多言語化 TODO:後で独自実装関数とすげ替える予定
 */
function __($msg)
{
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
 * 言語設定
 * @param \Fc2blog\Web\Request $request
 * @return string
 */
function setLanguage(\Fc2blog\Web\Request $request)
{
  $cookie_lang = \Fc2blog\Web\Cookie::get('lang');
  $cookie_language = \Fc2blog\Config::get('LANGUAGES.' . (string)$cookie_lang);
  if (!is_null($cookie_lang) && !is_null($cookie_language)) {
    $lang = $cookie_lang;
    $language = $cookie_language;
  }else{
    $lang = \Fc2blog\Config::get('LANG');
    $language = \Fc2blog\Config::get('LANGUAGE');
  }

  // 多言語化対応
  putenv('LANG=' . $language);
  putenv('LANGUAGE=' . $language);
  setlocale(LC_ALL, $language);
  bindtextdomain('messages', \Fc2blog\Config::get('LOCALE_DIR'));
  textdomain('messages');
  return $lang;
}

/**
 * 引数がcount関数で数えられる値かどうかを調べる
 * ※PHP7.2よりcount関数の引数に数えられない値が指定された場合、E_WARNINGが発生
 * ※PHP7.3より本関数は追加されている
 */
if (!function_exists('is_countable')) {
  function is_countable($var)
  {
    return (is_array($var) || $var instanceof Countable);
  }
}

function getServerUrl()
{
  $url = (empty($_SERVER["HTTPS"])) ? 'http://' : 'https://';
  $url .= \Fc2blog\Config::get('DOMAIN');

  return $url;
}