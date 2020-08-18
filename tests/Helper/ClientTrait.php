<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper;

use Exception;
use Fc2blog\Exception\PseudoExit;
use Fc2blog\Web\Request;
use Fc2blog\Web\Router\Router;

trait ClientTrait
{
  /**
   * ルーティングを解決し、実行クラスやパラメタを準備する
   * @param string $uri GETパラメタもここに含める必要がある
   * @param bool $is_https
   * @param string $method
   * @param array $post
   * @return array
   */
  public static function resolve(string $uri = "/", bool $is_https = true, string $method = "GET", array $post = [])
  {
    # 擬似的にアクセスURLをセットする
    $_SERVER['HTTP_USER_AGENT'] = "phpunit";
    $_SERVER['HTTPS'] = ($is_https) ? "on" : "";
    $_SERVER["REQUEST_METHOD"] = $method;
    $_SERVER['REQUEST_URI'] = $uri;
    $_POST = $post;

    $request = new Request();

    $router = new Router($request);

    return $router->resolve();
  }

  public static function execute(string $uri = "/", bool $is_https = true, string $method = "GET", array $post = []): string
  {
    $resolve = static::resolve($uri, $is_https, $method, $post);
    ob_start();
    try {
      new $resolve['className']($resolve['request'], $resolve['methodName']); // すべての実行が行われる
    } catch (PseudoExit $e) {
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
  public static function executeWithShouldExit(string $uri = "/", bool $is_https = true, string $method = "GET", array $post = []): string
  {
    $resolve = static::resolve($uri, $is_https, $method, $post);
    try {
      ob_start();
      new $resolve['className']($resolve['request'], $resolve['methodName']); // すべての実行が行われる
      throw new Exception("Unexpected, no PseudoExit thrown.");

    } catch (PseudoExit $e) {
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
    $_GET = [];
  }
}
