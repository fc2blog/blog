<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Controller;

use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;

class HttpsHttpRedirectTest extends TestCase
{
    use ClientTrait;

    public static $https_blog_id_path = '/testblog1/';
    public static $http_blog_id_path = '/testblog2/';

    public static $statusCode301SettledBlogId = '/testblog2/';
    public static $statusCode302SettledBlogId = '/testblog1/';

    public function testRedirect(): void
    {
        $r = $this->reqGetBeRedirect('/');
        # ブログのIDがランダムに出てくる
        $this->assertStringContainsString("testblog", $r->redirectUrl);
    }

    public function testHttpToHttpsRedirect(): void
    {
        $r = $this->reqGetBeRedirect(static::$https_blog_id_path);
        $this->assertStringContainsString('https://', $r->redirectUrl);
    }

    public function testHttpsToHttpRedirect(): void
    {
        $res = $this->reqHttpsGetBeRedirect(static::$http_blog_id_path);
        $this->assertStringContainsString('http://', $res->redirectUrl);
    }

    public function testHttpsToHttpsNoRedirect(): void
    {
        $res = $this->reqHttpsGet(static::$https_blog_id_path);
        $this->assertStringStartsWith('<!DOCTYPE html', $res->getOutput());
    }

    public function testHttpToHttpNoRedirect(): void
    {
        $res = $this->reqget(static::$http_blog_id_path);
        $this->assertStringStartsWith('<!DOCTYPE html', $res->getOutput());
    }

    public function testRedirectWithStatusCode301(): void
    {
        $res = $this->reqGetBeRedirect(static::$statusCode302SettledBlogId);
        $this->assertEquals(302, $res->statusCode);
    }

    public function testRedirectWithStatusCode302(): void
    {
        $res = $this->reqHttpsGetBeRedirect(static::$statusCode301SettledBlogId);
        $this->assertEquals(301, $res->statusCode);
    }
}
