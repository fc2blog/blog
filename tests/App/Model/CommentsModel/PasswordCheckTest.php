<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\CommentsModel;

use Fc2blog\Model\CommentsModel;
use Fc2blog\Model\Model;
use Fc2blog\Tests\DBHelper;
use PHPUnit\Framework\TestCase;
use TypeError;

class PasswordCheckTest extends TestCase
{
  public function setUp(): void
  {
    if (!class_exists(CommentsModel::class)) {
      Model::load('comments');
    }

    DBHelper::clearDbAndInsertFixture();

    parent::setUp();
  }

  public function testPasswordCheck(): void
  {
    $comments_model = new CommentsModel();

    $i = 10;
    while ($i-- > 0) { // ランダムで複数回チェック
      /** @noinspection PhpUnhandledExceptionInspection テストなので、失敗したら失敗してもらう */
      $random_password = base64_encode(random_bytes(random_int(1, 1024)));
      $passwd_hash = $comments_model::passwordHash($random_password);

      // 成功
      $is_match = $comments_model::password_check($random_password, ['password' => $passwd_hash]);
      $this->assertTrue($is_match);

      // 失敗（間違ったパスワード）
      $is_match = $comments_model::password_check($random_password, ['password' => "wrong pass"]);
      $this->assertNotTrue($is_match);
      $this->assertIsString($is_match);

      // 失敗（optionにpasswdがない）
      $is_match = $comments_model::password_check($random_password, []);
      $this->assertNotTrue($is_match);
      $this->assertIsString($is_match);
    }
  }

  /**
   * 空白パスワードの挙動チェック
   */
  public function testEmptyPasswordCheck(): void
  {
    $comments_model = new CommentsModel();

    $random_password = '';
    $passwd_hash = $comments_model::passwordHash($random_password);

    // 成功
    $is_match = $comments_model::password_check($random_password, ['password' => $passwd_hash]);
    $this->assertTrue($is_match);
  }

  /**
   * int入力時の挙動チェック
   */
  public function testDenyIntInputPasswordCheck(): void
  {
    $comments_model = new CommentsModel();

    $random_password = 123;
    try {
      /** @noinspection PhpStrictTypeCheckingInspection */
      $comments_model::passwordHash($random_password);
      $this->fail();
    } catch (TypeError $e) {
      //ok
      $this->assertInstanceOf(TypeError::class, $e);
    }
  }

  /**
   * bool入力時の挙動チェック
   */
  public function testDenyBoolInputPasswordCheck(): void
  {
    $comments_model = new CommentsModel();

    $random_password = true;
    try {
      /** @noinspection PhpStrictTypeCheckingInspection */
      $comments_model::passwordHash($random_password);
      $this->fail();
    } catch (TypeError $e) {
      //ok
      $this->assertInstanceOf(TypeError::class, $e);
    }
  }
}
