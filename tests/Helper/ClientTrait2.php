<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper;

use Fc2blog\Exception\PseudoExit;
use Fc2blog\Web\Request;
use Fc2blog\Web\Router\Router;
use RuntimeException;

trait ClientTrait2
{
  public $clientTraitSession = [];

  public function resetSession()
  {
    $this->clientTraitSession = [];
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

  public function reqGet(string $path = "/", array $params = [])
  {
    $_SESSION = $this->clientTraitSession;

    $request = new Request(
      "GET",
      $path,
      $_SESSION,
      [],
      $params
    );

    $router = new Router($request);
    $resolve = $router->resolve();

    $controller_instance = new $resolve['className']($resolve['request'], $resolve['methodName']);

    $this->clientTraitSession = $_SESSION;

    return $controller_instance;
  }

  public function reqGetWithExit(string $path = "/", array $params = []): PseudoExit
  {
    $_SESSION = $this->clientTraitSession;

    $request = new Request(
      "GET",
      $path,
      $_SESSION,
      [],
      $params
    );

    $router = new Router($request);
    $resolve = $router->resolve();

    $exception = null;
    try {
      new $resolve['className']($resolve['request'], $resolve['methodName']);
      throw new RuntimeException("It's not what I was expecting.");
    } catch (PseudoExit $e) {
      $exception = $e;
    }

    $this->clientTraitSession = $_SESSION;

    return $exception;
  }

  public function reqPost(string $path = "/", array $params = [])
  {
    $_SESSION = $this->clientTraitSession;

    $request = new Request(
      "POST",
      $path,
      $_SESSION,
      $params
    );

    $router = new Router($request);
    $resolve = $router->resolve();

    $controller_instance = new $resolve['className']($resolve['request'], $resolve['methodName']);

    $this->clientTraitSession = $_SESSION;

    return $controller_instance;
  }

  public function reqPostWithExit(string $path = "/", array $params = []): PseudoExit
  {
    $_SESSION = $this->clientTraitSession;

    $request = new Request(
      "POST",
      $path,
      $_SESSION,
      $params
    );

    $router = new Router($request);
    $resolve = $router->resolve();

    $exception = null;
    try {
      new $resolve['className']($resolve['request'], $resolve['methodName']);
      throw new RuntimeException("It's not what I was expecting.");
    } catch (PseudoExit $e) {
      $exception = $e;
    }

    $this->clientTraitSession = $_SESSION;

    return $exception;
  }

}
