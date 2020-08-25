<?php
// the file will be load by composer autoloader.

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
