<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Cookie;

use ErrorException;
use Fc2blog\Web\Cookie;
use Fc2blog\Web\Request;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use TypeError;

class SetTest extends TestCase
{
    public function testSetString(): void
    {
        // Cookieの正しいテストはUnitTestでは困難です
        // echo '<?php setcookie("1","b");' | php-cgi
        // 等でテストができますが、php-cgiがない環境でテストが実行できません。
        // headerが送信されている状態でCookieをセットするため、エラーが発生しますが、例外に変換してキャッチしています。
        $request = new Request();
        try {
            @Cookie::set($request, "k", "v");
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ErrorException $e) {
        }
        $this->assertTrue(true);
    }

    public function testSetInvalidType(): void
    {
        $request = new Request('GET', '/', null, null, null, null, ['HTTPS' => "on"]);
        try {
            /** @noinspection PhpStrictTypeCheckingInspection */
            Cookie::set($request, true, "v");
            $this->fail();
        } catch (TypeError $e) {
            $this->assertInstanceOf(TypeError::class, $e);
        }

        try {
            /** @noinspection PhpStrictTypeCheckingInspection */
            Cookie::set($request, 1, "v");
            $this->fail();
        } catch (TypeError $e) {
            $this->assertInstanceOf(TypeError::class, $e);
        }

        try {
            /** @noinspection PhpStrictTypeCheckingInspection */
            Cookie::set($request, "k", true);
            $this->fail();
        } catch (TypeError $e) {
            $this->assertInstanceOf(TypeError::class, $e);
        }

        try {
            /** @noinspection PhpStrictTypeCheckingInspection */
            Cookie::set($request, "k", 1);
            $this->fail();
        } catch (TypeError $e) {
            $this->assertInstanceOf(TypeError::class, $e);
        }
    }

    public function testDenyInvalidSamesite(): void
    {
        try {
            $request = new Request('GET', '/', null, null, null, null, ['HTTPS' => "on"]);
            @Cookie::set($request, "k", "v", time(), "", "", false, false, "Lax");
        } catch (InvalidArgumentException $e) {
            $this->fail($e->getMessage());
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ErrorException $e) {
            $this->assertTrue(true);
        }

        try {
            $request = new Request('GET', '/', null, null, null, null, ['HTTPS' => "on"]);
            @Cookie::set($request, "k", "v", time(), "", "", false, false, "Strict");
        } catch (InvalidArgumentException $e) {
            $this->fail($e->getMessage());
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ErrorException $e) {
            $this->assertTrue(true);
        }

        try {
            $request = new Request('GET', '/', null, null, null, null, ['HTTPS' => "on"]);
            @Cookie::set($request, "k", "v", time(), "", "", true, false, "None");
            $this->assertTrue(true);
        } catch (InvalidArgumentException $e) {
            $this->fail($e->getMessage());
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ErrorException $e) {
            $this->assertTrue(true);
        }

        try {
            $request = new Request();
            @Cookie::set($request, "k", "v", time(), "", "", false, false, "Wrong");
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testSamesiteNoneAndNotSecure(): void
    {
        try {
            $request = new Request('GET', '/', null, null, null, null, ['HTTPS' => ""]);
            @Cookie::set($request, "k", "v", time(), "", "", true, false, "None");
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ErrorException $e) {
            $this->assertTrue(true);
        }

        try {
            $request = new Request('GET', '/', null, null, null, null, ['HTTPS' => "on"]);
            @Cookie::set($request, "k", "v", time(), "", "", false, false, "None");
            $this->fail();
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        try {
            $request = new Request('GET', '/', null, null, null, null, ['HTTPS' => "on"]);
            @Cookie::set($request, "k", "v", time(), "", "", true, false, "None");
        } catch (InvalidArgumentException $e) {
            $this->fail($e->getMessage());
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ErrorException $e) {
            $this->assertTrue(true);
        }
    }
}
