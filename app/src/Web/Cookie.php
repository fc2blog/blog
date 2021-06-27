<?php
declare(strict_types=1);

namespace Fc2blog\Web;

use InvalidArgumentException;

class Cookie
{
    /**
     * クッキーから情報を取得する
     * @param Request $request
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(Request $request, string $key, $default = null)
    {
        return $request->cookie[$key] ?? $default;
    }

    /**
     * クッキーから情報を取得し破棄する
     * @param Request $request
     * @param string $key
     * @return void
     */
    public static function remove(Request $request, string $key): void
    {
        self::set($request, $key, "", time() - 3600);
    }

    /**
     * クッキーに情報を保存する
     * @param Request $request
     * @param string $key
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httponly
     * @param string $samesite
     *
     * NOTE: httpアクセスも許可する仕様がなければ、secureを$request->server['https']をみてtrueにすべきではないか
     */
    public static function set(
        Request $request,
        string $key,
        string $value,
        int $expires = 0,
        string $path = "/",
        string $domain = "",
        bool $secure = false,
        bool $httponly = true,
        string $samesite = "Lax"
    )
    {
        $params = [
            'expires' => $expires,
            'path' => $path,
            'domain' => $domain,
        ];

        // SSLターミネーションがある環境においては検討が必要
        if ($secure === true && !$request->isHttps()) {
            throw new InvalidArgumentException("secure=true needs https.");
        }
        $params['secure'] = $secure;

        // setcookieではデフォルトfalseだが、セキュリティのためにtrueをデフォルト化
        if ($httponly === true) {
            $params['httponly'] = $httponly;
        }

        if (false === in_array($samesite, static::cookieSamesiteAllowedList)) {
            throw new InvalidArgumentException("invalid samesite parameter");
        }
        $params['samesite'] = $samesite;

        // domainは特別に設定されないかぎり空
        if (strlen($domain) > 0) {
            $params['domain'] = $domain;
        }

        // 不可能な組み合わせを拒否
        if (
            $samesite === "None" &&
            (
                $secure !== true ||
                (!isset($request->server['HTTPS']) || $request->server['HTTPS'] !== "on")
            )
        ) {
            throw new InvalidArgumentException("samesite=None needs https.");
        }

        // 書き込み
        $request->cookie[$key] = $value;
        if (!defined("THIS_IS_TEST")) {
            // unit testでなければcookieを書き込む
            setcookie(
                $key,
                $value,
                $params
            );
        }
    }

    // Cookieのsamesiteパラメタに指定可能な値のバリエーション
    const cookieSamesiteAllowedList = ['None', 'Lax', 'Strict'];
}
