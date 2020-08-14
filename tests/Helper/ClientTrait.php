<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper;

use Exception;
use InvalidArgumentException;
use \Fc2blog\Exception\PseudoExit;

trait ClientTrait
{
  /**
   * ルーティングを解決し、実行クラスやパラメタを準備する
   * @param string $uri GETパラメタもここに含める必要がある
   * @param bool $is_https
   * @param string $method
   * @param array $post
   * @param string $target user,admin,test
   * @return array
   */
  public static function resolve(string $uri = "/", bool $is_https = true, string $method = "GET", array $post = [], string $target = "user")
  {
    # 擬似的にアクセスURLをセットする
    $_SERVER['HTTP_USER_AGENT'] = "phpunit";
    $_SERVER['HTTPS'] = ($is_https) ? "on" : "";
    $_SERVER["REQUEST_METHOD"] = $method;
    $_SERVER['REQUEST_URI'] = $uri;
    $_POST = $post;

    # Requestはキャッシュされるので、都度消去する
    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    \Fc2blog\Request::resetInstanceForTesting();

    if ($target === "user") {
      /** @noinspection PhpFullyQualifiedNameUsageInspection */
      \Fc2blog\Config::read('user.php');
    } else if ($target === "admin") {
      \Fc2blog\Config::read('admin.php');
    } else if ($target === "test") {
      \Fc2blog\Config::read('test.php');
    } else {
      throw new InvalidArgumentException("target is wrong.");
    }

    [$className, $methodName] = getNeoRouting();


    return [$className, $methodName];
  }

  public static function execute(string $uri = "/", bool $is_https = true, string $method = "GET", array $post = [], string $target = "user"): string
  {
    [$className, $methodName] = static::resolve($uri, $is_https, $method, $post, $target);
    ob_start();
    try {
      new $className($methodName); // すべての実行が行われる
    } /** @noinspection PhpRedundantCatchClauseInspection */ catch (PseudoExit $e) {
      echo "\nUnexpected exit. {$e->getFile()}:{$e->getLine()} {$e->getMessage()}\n {$e->getTraceAsString()}";
    }
    static::resetClient();
    return ob_get_clean();
  }

  /**
   * 疑似終了を期待するリクエスト
   * @param string $uri
   * @param bool $is_https
   * @param string $method
   * @param array $post
   * @return false|string
   * @throws Exception
   */
  public static function executeWithShouldExit(string $uri = "/", bool $is_https = true, string $method = "GET", array $post = [], string $target = "user"): string
  {
    [$className, $methodName] = static::resolve($uri, $is_https, $method, $post, $target);
    try {
      ob_start();
      new $className($methodName);
      throw new Exception("Unexpected, no PseudoExit thrown.");

    } /** @noinspection PhpRedundantCatchClauseInspection */ catch (PseudoExit $e) {
      // \Fc2blog\Exception\PseudoExit は正常終了と同義
      $ob = ob_get_clean();
      echo $ob;
      return $ob;

    } finally {
      static::resetClient();
    }
  }

  /**
   * 都度、お掃除
   */
  public static function resetClient(): void
  {
    unset($_SERVER['REQUEST_URI'], $_SERVER['HTTP_USER_AGENT'], $_SERVER['HTTPS'], $_SERVER['REQUEST_URI']);
    $_POST = [];
    /** @noinspection PhpFullyQualifiedNameUsageInspection */
    \Fc2blog\Request::resetInstanceForTesting();
  }
}
