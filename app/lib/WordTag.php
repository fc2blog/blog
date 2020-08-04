<?php


namespace App\Lib;


class WordTag
{
  public static function replace($wordTag=''): string
  {
    preg_match('/\{%(.+)\}/u', $wordTag, $m);
    $method = $m[1] ?? '';
    $replace = method_exists(__CLASS__, $method) ? self::$method() : $wordTag;

    return (string) $replace;
  }

  private static function displayNone(): string
  {
    return '';
  }

}