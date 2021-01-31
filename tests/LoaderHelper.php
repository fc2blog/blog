<?php
declare(strict_types=1);

namespace Fc2blog\Tests;

use ErrorException;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

class LoaderHelper extends TestCase
{
  public static function requireBootStrap()
  {
    # 細かなエラーを見逃さないために、Noticeを含むすべてのエラーをキャッチしてErrorExceptionに変換する
    # TODO もっとふさわしい場所に移動
    set_error_handler(function (int $severity, string $message, string $file, int $line) {
      error_log("Error on {$file}:{$line} {$message}");
      /** @noinspection PhpUnhandledExceptionInspection */
      throw new ErrorException($message, 0, $severity, $file, $line);
    });

    //TODO dotenvで外部化
    putenv('FC2_CONFIG_FROM_ENV=1');
    putenv('FC2_ENABLE_UNIT_TEST_ENDPOINT=1');
    putenv('FC2_STRICT_ERROR_REPORT=1');
    putenv('FC2_ERROR_LOG_PATH=php://stderr');
    putenv('FC2_APP_LOG_PATH=php://stderr');
    putenv('FC2_APP_LOG_LEVEL='.Logger::WARNING);
    putenv('FC2_SQL_DEBUG=php://stderr');
    putenv('FC2_APP_DEBUG=php://stderr');
    putenv('FC2_ERROR_ON_DISPLAY=0');
    putenv('FC2_DB_HOST=db');
    putenv('FC2_DB_USER=docker');
    putenv('FC2_DB_PASSWORD=docker');
    putenv('FC2_DB_DATABASE=dev_fc2blog');
    putenv('FC2_DB_CHARSET=UTF8MB4');
    putenv('FC2_DOMAIN=localhost');
    putenv('FC2_HTTP_PORT=8080');
    putenv('FC2_HTTPS_PORT=8480');
    putenv('FC2_DOCUMENT_ROOT_PATH=' . __DIR__ . "/../public/");

    if ((string)getenv("FC2_CONFIG_FROM_ENV") === "1") {
      require(TEST_APP_DIR . '/config_read_from_env.php');
    } else {
      /** @noinspection PhpIncludeInspection このファイルはないことがあるので */
      require(TEST_APP_DIR . '/config.php');
    }
  }
}
