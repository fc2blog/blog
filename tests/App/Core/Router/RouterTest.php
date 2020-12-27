<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Router;

use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class RouterTest extends TestCase
{
  use ClientTrait;

  public function testResolveRoute(): void
  {
    $this->assertEquals("Fc2blog\Web\Controller\User\BlogsController", $this->getClass("GET", "/"));
    $this->assertEquals("index", $this->getMethod("GET", "/"));

    $this->assertEquals("Fc2blog\Web\Controller\User\CommonController", $this->getClass("GET", "/admin/missing"));
    $this->assertEquals("error404", $this->getMethod("GET", "/admin/install"));

    $this->assertEquals("Fc2blog\Web\Controller\Admin\CommonController", $this->getClass("GET", "/admin/common/install"));
    $this->assertEquals("install", $this->getMethod("GET", "/admin/common/install"));
  }

  private function getClass($method, $path): string
  {
    $request = new Request($method, $path);
    return $request->className;
  }

  private function getMethod($method, $path): string
  {
    $request = new Request($method, $path);
    return $request->methodName;
  }
}
