<?php

declare(strict_types=1);

namespace Fc2blog\Util;

use Fc2blog\Config;
use Fc2blog\Web\Cookie;
use Fc2blog\Web\Request;

class I18n
{
  /**
   * 言語設定
   * @param Request $request
   * @return string
   */
  static public function setLanguage(Request $request)
  {
    $cookie_lang = Cookie::get('lang');
    $cookie_language = Config::get('LANGUAGES.' . (string)$cookie_lang);
    if (!is_null($cookie_lang) && !is_null($cookie_language)) {
      $lang = $cookie_lang;
      $language = $cookie_language;
    } else {
      $lang = Config::get('LANG');
      $language = Config::get('LANGUAGE');
    }

    // 多言語化対応
    putenv('LANG=' . $language);
    putenv('LANGUAGE=' . $language);
    setlocale(LC_ALL, $language);
    bindtextdomain('messages', Config::get('LOCALE_DIR'));
    textdomain('messages');
    return $lang;
  }

  static public function registerFunction(): bool
  {
    /**
     * 多言語化 TODO:後で独自実装関数とすげ替える予定
     */
    return true;
  }
}
