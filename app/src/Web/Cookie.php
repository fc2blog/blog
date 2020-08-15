<?php
/**
 * Cookieクラス
 */

namespace Fc2blog\Web;

use InvalidArgumentException;

class Cookie
{

  /**
   * クッキーから情報を取得する
   */
  public static function get($key, $default = null)
  {
    if (isset($_COOKIE[$key])) {
      return $_COOKIE[$key];
    }
    return $default;
  }

  /**
   * クッキーから情報を取得し破棄する
   * @param string $key
   * @return void
   */
  public static function remove(string $key):void
  {
    self::set($key, "", time() - 3600);
  }

  /**
   * クッキーに情報を保存する
   * @param string $key
   * @param string $value
   * @param int $expires
   * @param string $path
   * @param string $domain
   * @param bool $secure
   * @param bool $httponly
   * @param string $samesite
   *
   * NOTE: httpアクセスも許可する仕様がなければ、secureを$_SERVER['https']をみてtrueにすべきではないか
   */
  public static function set(string $key,
                             string $value,
                             int $expires = 0,
                             string $path = "/",
                             string $domain = "",
                             bool $secure = false,
                             bool $httponly = true,
                             string $samesite = "Lax")
  {
    $params = [];
    $params['expires'] = $expires;
    $params['path'] = $path;
    $params['domain'] = $domain;

    // SSLターミネーションがある環境においては検討が必要
    if ($secure === true) {
      if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on") {
        throw new InvalidArgumentException("secure=true needs https.");
      }
      $params['secure'] = true;
    }

    // setcookieではデフォルトfalseだが、セキュリティのためにtrueをデフォルト化
    if ($httponly === true) {
      $params['httponly'] = $httponly;
    }

    // samesiteはhttp対応のために今後通常となるLaxをデフォルト
    if (false === in_array($samesite, static::cookieSamesiteAllowedList)) {
      throw new InvalidArgumentException("invalid samesite parameter");
    }
    $params['samesite'] = $samesite;

    // domainは特別にコンフィグで設定されないかぎり空で、アクセスドメインに発行
    if (strlen($domain) > 0) {
      $params['domain'] = $domain;
    } else if (strlen(\Fc2blog\Config::get('COOKIE_DEFAULT_DOMAIN')) > 0) {
      $params['domain'] = $domain = \Fc2blog\Config::get('COOKIE_DEFAULT_DOMAIN');
    }

    // 不可能な組み合わせを拒否
    if (
      $samesite === "None" &&
      (
        $secure !== true ||
        (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== "on")
      )
    ) {
      throw new InvalidArgumentException("samesite=None needs https.");
    }

    setcookie(
      $key,
      $value,
      $params
    );
  }

  // Cookieのsamesiteパラメタに指定可能な値のバリエーション
  const cookieSamesiteAllowedList = ['None', 'Lax', 'Strict'];
}
