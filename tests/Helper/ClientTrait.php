<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper;

use Fc2blog\Exception\RedirectExit;
use Fc2blog\Web\Controller\AppController;
use Fc2blog\Web\Request;
use Fc2blog\Web\Router\Router;
use RuntimeException;

/**
 * Trait ClientTrait2 なるだけ再利用できるようにのWrapper（ショートカットではない）
 * @package Fc2blog\Tests\Helper
 */
trait ClientTrait
{
  public $clientTraitSession = [];
  public $clientTraitCookie = [];

  public function resetSession()
  {
    $this->clientTraitSession = [];
  }

  public function resetCookie()
  {
    $this->clientTraitCookie = [];
  }

  public function setSession(array $session)
  {
    $this->clientTraitSession = $session;
  }

  public function mergeAdminSession()
  {
    $this->clientTraitSession = array_merge(
      [
        'user_id' => 1,
        'login_id' => 'testadmin',
        'user_type' => 1,
        'blog_id' => 'testblog2',
        'nickname' => 'testnick2',
      ],
      $this->clientTraitSession
    );
  }

  public function setPlainAdminSession()
  {
    $this->resetSession();
    $this->mergeAdminSession();
  }

  public function reqBase(
    bool $https,
    string $method,
    string $path,
    array $postParams = [],
    array $getParams = [],
    array $filesParams = []
  ): AppController
  {
    $_SESSION = $this->clientTraitSession;
    $_COOKIE = $this->clientTraitCookie;
    $_SERVER = [];
    if ($https) {
      $_SERVER['HTTPS'] = "on";
    } else {
      unset($_SERVER['HTTPS']);
    }
    $_SERVER['HTTP_USER_AGENT'] = "phpunit";

    $request = new Request(
      $method,
      $path,
      $_SESSION,
      $postParams,
      $getParams,
      $filesParams,
      $_SERVER,
      [],
      $_COOKIE
    );

    $router = new Router($request);
    $resolve = $router->resolve();

    $controller_instance = new $resolve['className']($resolve['request'], $resolve['methodName']);

    if(empty($_SESSION)){
      //おそらく、セッション全破棄がおこなわれたので、初期化
      $this->clientTraitSession = [];
    }else{
      $this->clientTraitSession = array_merge($this->clientTraitSession, $_SESSION);
    }
    // Cookieは設定場所が明確で制御できているので、$_COOKIEが劣位である。
    $this->clientTraitCookie = array_merge($_COOKIE, $request->cookie);

    return $controller_instance;
  }

  public function reqBaseBeRedirect(
    bool $https,
    string $method,
    string $path,
    array $postParams = [],
    array $getParams = [],
    array $filesParams = []
  ): RedirectExit
  {
    $_SESSION = $this->clientTraitSession;
    $_COOKIE = $this->clientTraitCookie;
    $_SERVER = [];
    if ($https) {
      $_SERVER['HTTPS'] = "on";
    } else {
      unset($_SERVER['HTTPS']);
    }
    $_SERVER['HTTP_USER_AGENT'] = "phpunit";

    $request = new Request(
      $method,
      $path,
      $_SESSION,
      $postParams,
      $getParams,
      $filesParams,
      $_SERVER,
      [],
      $_COOKIE
    );

    $router = new Router($request);
    $resolve = $router->resolve();

    $exception = null;
    try {
      new $resolve['className']($resolve['request'], $resolve['methodName']);
      throw new RuntimeException("It's not what I was expecting.");
    } catch (RedirectExit $e) {
      $exception = $e;
    }

    if(empty($_SESSION)){
      //おそらく、セッション全破棄がおこなわれたので、初期化
      $this->clientTraitSession = [];
    }else{
      $this->clientTraitSession = array_merge($this->clientTraitSession, $_SESSION);
    }
    // Cookieは設定場所が明確で制御できているので、$_COOKIEが劣位である。
    $this->clientTraitCookie = array_merge($_COOKIE, $request->cookie);

    return $exception;
  }

  public function reqGet(string $path = "/", array $params = []): AppController
  {
    return static::reqBase(false, "GET", $path, [], $params);
  }

  public function reqHttpsGet(string $path = "/", array $params = []): AppController
  {
    return static::reqBase(true, "GET", $path, [], $params);
  }

  public function reqGetBeRedirect(string $path = "/", array $params = []): RedirectExit
  {
    return static::reqBaseBeRedirect(false, "GET", $path, [], $params);
  }

  public function reqHttpsGetBeRedirect(string $path = "/", array $params = []): RedirectExit
  {
    return static::reqBaseBeRedirect(true, "GET", $path, [], $params);
  }

  public function reqPost(string $path = "/", array $params = []): AppController
  {
    return static::reqBase(false, "POST", $path, $params);
  }

  public function reqHttpsPost(string $path = "/", array $params = []): AppController
  {
    return static::reqBase(true, "POST", $path, $params);
  }

  public function reqPostWithExit(string $path = "/", array $params = []): RedirectExit
  {
    return static::reqBaseBeRedirect(false, "POST", $path, $params);
  }

  public function reqHttpsPostWithExit(string $path = "/", array $params = []): RedirectExit
  {
    return static::reqBaseBeRedirect(true, "POST", $path, $params);
  }

  public function reqPostFileBeRedirect(string $path = "/", array $params = [], array $files =[]): RedirectExit
  {
    return static::reqBaseBeRedirect(false, "POST", $path, $params, [], $files);
  }
}
