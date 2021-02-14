<?php
/**
 * Cookieクラス
 */

namespace Fc2blog\Web;

use Fc2blog\Config;
use InvalidArgumentException;

class Cookie
{

  /**
   * クッキーから情報を取得する
   * @param Request $request
   * @param string $key
   * @param ?string $default
   * @return mixed
   */
  public static function get(Request $request, string $key, $default = null)
  {
    if (isset($request->cookie[$key])) {
      return $request->cookie[$key];
    }
    return $default;
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
    string $samesite = "Lax")
  {
    $params = [];
    $params['expires'] = $expires;
    $params['path'] = $path;
    $params['domain'] = $domain;

    // SSLターミネーションがある環境においては検討が必要
    if ($secure === true) {
      if (!isset($request->server['HTTPS']) || $request->server['HTTPS'] !== "on") {
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
    } else if (strlen(Config::get('COOKIE_DEFAULT_DOMAIN')) > 0) {
      $params['domain'] = $domain = Config::get('COOKIE_DEFAULT_DOMAIN');
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

    if(defined("THIS_IS_TEST")){
      $request->cookie[$key] = $value;
    }else{
      $request->cookie[$key] = $value;
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
