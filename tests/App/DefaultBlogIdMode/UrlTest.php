<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\DefaultBlogIdMode;

use Fc2blog\Config;
use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;

// Configを直接操作するテストをおこなうため、この内部でFailすると他テストに影響する可能性あり
class UrlTest extends TestCase
{
    use ClientTrait;

    public function setUp(): void
    {
        DBHelper::clearDbAndInsertFixture();
        parent::setUp();
    }

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
        $this->assertStringNotContainsString('<h1><a href="/testblog2/"', $c->getOutput());
        $this->assertStringContainsString('<h1><a href="/"', $c->getOutput());
        Config::set('DEFAULT_BLOG_ID', null);
    }

    public function testHeaderLinkUrlNotDefaultBlogWithDefaultBlogId(): void
    {
        // set default blog to "testblog2"
        Config::set('DEFAULT_BLOG_ID', 'testblog2');
        $c = $this->reqHttpsGet('/testblog1/');
        $this->assertStringContainsString('<h1><a href="/testblog1/"', $c->getOutput());
        Config::set('DEFAULT_BLOG_ID', null);
    }

    public function testHeaderLinkUrlNotDefaultBlogWithOutDefaultBlogId(): void
    {
        Config::set('DEFAULT_BLOG_ID', null);
        $c = $this->reqHttpsGet('/testblog1/');
        $this->assertStringContainsString('<h1><a href="/testblog1/"', $c->getOutput());
        Config::set('DEFAULT_BLOG_ID', null);
    }

    public function testEntryLinkUrl(): void
    {
        // Default blog なしのときのエントリリンク先にblog idが含まれていること
        Config::set('DEFAULT_BLOG_ID', null);
        $d = static::getFc2PreprocessedData("GET", "/testblog2/");
//    var_dump($d);
        $this->assertEquals('testblog2', $d['blog_id']);
        // HTML::urlのテストのため
        $this->assertEquals('/testblog2/blog-entry-3.html', $d['entries'][0]['link']);
        $this->assertStringContainsString("/testblog2/", $d['_calender_data'][3][5]);// テストデータに依存しているので、壊れやすい

        // Default blog ありの時のエントリリンク先にblog idが含まれていないこと
        Config::set('DEFAULT_BLOG_ID', 'testblog2');
        $d = static::getFc2PreprocessedData("GET", "/testblog2/");
//    var_dump($d);
        $this->assertEquals('testblog2', $d['blog_id']);
        $this->assertEquals('/blog-entry-3.html', $d['entries'][0]['link']);
        $this->assertStringNotContainsString("/testblog2/", $d['_calender_data'][3][5]);// テストデータに依存しているので、壊れやすい

        // 掃除
        Config::set('DEFAULT_BLOG_ID', null);
    }
}
