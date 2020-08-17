<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\App;

use Fc2blog\App;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Throwable;
use TypeError;

class GetRandomStringTest extends TestCase
{
  public function testGenerate()
  {
    $this->assertIsString(App::genRandomString());
    $this->assertEquals(16, strlen(App::genRandomString()));

    $this->assertIsString(App::genRandomString(0));
    $this->assertIsString(App::genRandomString(10));
    $this->assertEquals(0, strlen(App::genRandomString(0)));
    $this->assertEquals(1, strlen(App::genRandomString(1)));
    $this->assertEquals(10, strlen(App::genRandomString(10)));
    $this->assertEquals(100, strlen(App::genRandomString(100)));
  }

  public function testCharset()
  {
    $this->assertEquals("a", App::genRandomString(1, "a"));
    $this->assertEquals("aaaaa", App::genRandomString(5, "a"));
    $this->assertEquals("aaaaa", App::genRandomString(5, "aa")); // 重複は特に禁止されない（確率が偏るが）
    $this->assertEquals(0, preg_match("/[^abc]/u", App::genRandomString(100, "abc")));
    $this->assertEquals(0, preg_match("/[^abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ012345679_-]/u", App::genRandomString(1000)));
  }

  public function testInvalidLength()
  {
    try {
      App::genRandomString(-1);
      $this->fail();
    } catch (InvalidArgumentException $e) {
      $this->assertTrue(true);
    } catch (Throwable $e) {
      $this->fail();
    }

    try {
      /** @noinspection PhpStrictTypeCheckingInspection */
      App::genRandomString(false);
      $this->fail();
    } catch (TypeError $e) {
      $this->assertTrue(true);
    } catch (Throwable $e) {
      $this->fail();
    }

    try {
      /** @noinspection PhpStrictTypeCheckingInspection */
      App::genRandomString("abc");
      $this->fail();
    } catch (TypeError $e) {
      $this->assertTrue(true);
    } catch (Throwable $e) {
      $this->fail();
    }
  }

  public function testInvalidCharList()
  {
    try {
      $this->assertIsString(App::genRandomString(1, ""));
      $this->fail();
    } catch (InvalidArgumentException $e) {
      $this->assertTrue(true);
    } catch (Throwable $e) {
      $this->fail();
    }

    try {
      /** @noinspection PhpStrictTypeCheckingInspection */
      $this->assertIsString(App::genRandomString(1, true));
      $this->fail();
    } catch (TypeError $e) {
      $this->assertTrue(true);
    } catch (Throwable $e) {
      $this->fail();
    }

    try {
      /** @noinspection PhpStrictTypeCheckingInspection */
      $this->assertIsString(App::genRandomString(1, 1));
      $this->fail();
    } catch (TypeError $e) {
      $this->assertTrue(true);
    } catch (Throwable $e) {
      $this->fail();
    }
  }

  public function testMultiByteCharList()
  {
    $this->assertIsString(App::genRandomString(1, "あいう"));
    $this->assertEquals(1, mb_strlen(App::genRandomString(1, "あいう")));
    $this->assertEquals(1, preg_match("/\A[あいう]\z/u", App::genRandomString(1, "あいう")));

    $this->assertEquals(1, mb_strlen(App::genRandomString(1, "🤔☺️😀")));
    $this->assertEquals("🤔", App::genRandomString(1, "🤔"));
    // 合成文字（合成絵文字 例 👨‍👩‍👧‍👧👩‍👩‍👦👩‍👩‍👧等）は、現在の所サポートされない。preg_splitの都合
    // $this->assertEquals("👩‍👩‍👧‍👧", \Fc2blog\App::genRandomString(1, "👩‍👩‍👧‍👧"));
    $this->assertEquals(1, preg_match("/\A[🤔☺️😀]\z/u", App::genRandomString(1, "🤔☺️😀")));
  }
}
