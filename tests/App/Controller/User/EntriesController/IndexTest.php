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
        $c = $this->reqHttpsGet('/testblog1/?no=1');
        $this->assertStringStartsWith("<!DOCTYPE html", $c->getOutput());
        $this->assertStringContainsString("testblog1", $c->getOutput());
    }
}
