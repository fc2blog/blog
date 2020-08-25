#!/usr/bin/env php
<?php
declare(strict_types=1);

use Fc2blog\Config;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
use Fc2blog\Tests\LoaderHelper;

define("TEST_APP_DIR", __DIR__ . "/../App");

require_once __DIR__ . "/../app/vendor/autoload.php";

LoaderHelper::requireBootStrap();

Config::set('DEBUG', 0); // suppress debug message.

$generator = new GenerateSampleComment();

# データ生成例
$generator->generateSampleComment("testblog2", 1);
