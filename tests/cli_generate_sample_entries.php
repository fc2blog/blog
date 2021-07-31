#!/usr/bin/env php
<?php
declare(strict_types=1);

# Noticeを含むすべてのエラーをキャッチしてExceptionに変換
set_error_handler(function (int $severity, string $message, string $file, int $line) {
    /** @noinspection PhpUnhandledExceptionInspection */
    throw new ErrorException($message, 0, $severity, $file, $line);
});

use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleEntry;
use Fc2blog\Tests\LoaderHelper;

define("TEST_APP_DIR", __DIR__ . "/../app");

require_once __DIR__ . "/../app/vendor/autoload.php";

/** @noinspection PhpUnhandledExceptionInspection */
LoaderHelper::bootStrap();

$generator = new GenerateSampleEntry();

# データ生成例
$generator->generateSampleEntry('testblog2');

