<?php
declare(strict_types=1);

namespace Fc2blog\Web;

use Fc2blog\Config;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE || headers_sent()) {
            return;
        }

        $session_cookie_options = [
            "lifetime" => 0,
            "path" => "/",
            "httponly" => true,
            "samesite" => "Lax" // NOTE: 要件としてhttpサポートがあるため、None属性は指定ができない
            // NOTE: 要件としてhttpサポートがあるため secure属性は指定ができない
        ];

        session_set_cookie_params($session_cookie_options);
        session_name(Config::get('SESSION_NAME'));
        session_start();
    }

    /**
     * セッションから情報を取得する
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    public static function get(string $key, $default = null)
    {
        self::start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * セッションから情報を取得し破棄する
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public static function remove(string $key, $default = null)
    {
        self::start();
        $tmp = $_SESSION[$key] ?? $default;
        unset($_SESSION[$key]);
        return $tmp;
    }

    /**
     * セッションに情報を保存する
     * @param string $key
     * @param mixed $value
     */
    public static function set(string $key, $value): void
    {
        self::start();
        $_SESSION[$key] = $value;
    }

    /**
     * セッションID置き換え
     */
    public static function regenerate(): void
    {
        self::start();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    /**
     * セッションを破棄
     * @param Request $request
     */
    public static function destroy(Request $request): void
    {
        $_SESSION = [];
        $request->session = [];
        if (isset($request->cookie[Config::get('SESSION_NAME')])) {
            Cookie::remove($request, Config::get('SESSION_NAME'));
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}

