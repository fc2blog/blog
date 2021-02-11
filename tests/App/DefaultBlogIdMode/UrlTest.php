<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\DefaultBlogIdMode;

use Fc2blog\Config;
use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;

// Configを直接操作するテストをおこなうため、この内部でFailすると他テストに影響する可能性あり
class UrlTest extends TestCase
{
  use ClientTrait;

  public function testHeaderLinkUrlWithOutDefaultBlogId(): void
  {
    // no default blog
    Config::set('DEFAULT_BLOG_ID', null);
    $c = $this->reqGet('/testblog2/');
    $this->assertStringContainsString('<h1><a href="/testblog2/"', $c->getOutput());
    Config::set('DEFAULT_BLOG_ID', null);
  }

  public function testHeaderLinkUrlWithDefaultBlogId(): void
  {
    // set default blog to "testblog2"
    Config::set('DEFAULT_BLOG_ID', 'testblog2');
    $c = $this->reqGet('/testblog2/');
    $this->assertStringNotContainsString('<h1><a href="/testblog2/', $c->getOutput());
    $this->assertStringContainsString('<h1><a href="/"', $c->getOutput());
    Config::set('DEFAULT_BLOG_ID', null);
  }
}
