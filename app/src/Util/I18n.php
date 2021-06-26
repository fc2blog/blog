<?php

declare(strict_types=1);

namespace Fc2blog\Util;

use Fc2blog\App;
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
    static public function setLanguage(Request $request): string
    {
        $request_lang = null;

        // Note: 不正なlang指定時は一律でFallbackする。
        if (!is_null($request->get('lang')) && strlen($request->get('lang')) > 0) {
            // Requestにある場合、最優先する
            $request_lang = $request->get('lang'); // <== ex: ja
            // Cookieに書き込みする
            // https://github.com/fc2blog/blog/issues/162#issuecomment-733474145
            Cookie::set($request, 'lang', $request_lang);
        } elseif (!is_null($request->getCookie('lang'))) {
            // Requestにない場合、Cookieを確認する
            $request_lang = $request->getCookie('lang'); // <== ex: ja
        } elseif (isset($request->server["HTTP_ACCEPT_LANGUAGE"])) {
            // ReqもCookieもない場合、Header。
            // 最優先の言語を用いる（第二以降は無視される）
            // ex: Accept-Language: ja,en-US;q=0.9,en;q=0.8
            if (1 === preg_match('/\A([a-z]+)/', $request->server["HTTP_ACCEPT_LANGUAGE"], $match)) {
                $request_lang = $match[0];
            }
        }

        // 利用できる言語かチェックを兼ねている
        $request_language = App::$languages[$request_lang] ?? null ; // <== ex: ja_JP.UTF-8

        if (!is_null($request_lang) && !is_null($request_language)) {
            $lang = $request_lang;
            $language = $request_language;
        } else if (isset(App::$languages['en'])) {
            // languageが確定できないとき、enを優先する
            // https://github.com/fc2blog/blog/issues/162#issuecomment-733474145
            $lang = "en";
            $language = App::$languages[$lang];
        } else {
            // fallback to default language.
            $lang = App::$lang;
            $language = App::$language;
        }

        // 多言語化対応
        putenv('LANG=' . $language);
        putenv('LANGUAGE=' . $language);
        setlocale(LC_ALL, $language);
        bindtextdomain('messages', App::LOCALE_DIR);
        textdomain('messages');
        return $lang;
    }
}
