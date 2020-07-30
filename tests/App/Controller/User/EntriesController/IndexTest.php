<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\User\EntriesController;

use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;

class IndexTest extends TestCase
{
  use ClientTrait;

  public function testGet(): void
  {
    $res = static::execute('/testblog1/?no=1', true);

    $this->assertStringStartsWith("<!DOCTYPE html", $res);
    $this->assertStringContainsString("testblog1", $res);
  }
}
