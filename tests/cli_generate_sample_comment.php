#!/usr/bin/env php
<?php
declare(strict_types=1);

use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
use Fc2blog\Tests\LoaderHelper;

define("TEST_APP_DIR", __DIR__ . "/../app");
define("THIS_IS_TEST", true);

require_once __DIR__ . "/../app/vendor/autoload.php";

/** @noinspection PhpUnhandledExceptionInspection */
LoaderHelper::requireBootStrap();

$generator = new GenerateSampleComment();

# データ生成例
$generator->generateSampleComment("testblog2", 1);
