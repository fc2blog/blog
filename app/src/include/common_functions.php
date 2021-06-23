<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
// the file will be load by composer autoloader.

/**
 * 共通関数群
 */

/**
 * HTMLエスケープの短縮形
 * @param string|null $text
 * @return string
 */
function h(?string $text): string
{
    return htmlentities($text, ENT_QUOTES, 'UTF-8');
}

/**
 * マルチバイト対応のtruncate
 * @param string|null $text
 * @param int $length
 * @param string $etc
 * @return string
 */
function t(?string $text, int $length = 10, string $etc = '...'): string
{
    if (!$length) {
        return '';
    }
    if (mb_strlen($text, 'UTF-8') > $length) {
        return mb_substr($text, 0, $length, 'UTF-8') . $etc;
    }
    return $text;
}

/**
 * 対象の内容が空文字列の場合代替内容を返却する
 * @param string|null $text
 * @param $default
 * @return string
 */
function d(?string $text, $default): string
{
    if ($text === null || $text === '') {
        return $default;
    }
    return $text;
}

/**
 * t,hのエイリアス
 * @param string|null $text
 * @param int $length
 * @param string $etc
 * @return string
 */
function th(?string $text, int $length = 10, string $etc = '...'): string
{
    return h(t($text, $length, $etc));
}

/**
 * URLのエンコードエイリアス
 * @param string|null $text
 * @return string
 */
function ue(?string $text): string
{
    return rawurlencode($text);
}

/**
 * 日付のフォーマット変更
 * @param $date
 * @param string $format
 * @return string
 * TODO 使われていない？
 */
function df($date, string $format = 'Y/m/d H:i:s'): string
{
    return (string)date($format, strtotime($date));
}

/**
 * GettextのWrapper
 * @param string|null $msg
 * @return string
 */
function __(?string $msg): string
{
    return _($msg);
}
