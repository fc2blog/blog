<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Controller;

use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;

class RedirectTest extends TestCase
{
  use ClientTrait;

  public static $testBase = '/_for_unit_test_';

  public function testNotFullUriRedirect(): void
  {
    $res = $this->reqGetBeRedirect(static::$testBase . "/redirect_test_no_full_url");
    $this->assertStringNotContainsString('http', $res->redirectUrl);

    $res = $this->reqGetBeRedirect(static::$testBase . "/redirect_test_full_url?blog_id=testblog1");
    $this->assertStringContainsString('http', $res->redirectUrl);
  }
}
