#!/usr/bin/env php
<?php
declare(strict_types=1);

use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleTemplate;
use Fc2blog\Tests\LoaderHelper;

define("TEST_APP_DIR", __DIR__ . "/../App");
define("THIS_IS_TEST", true);

require_once __DIR__ . "/../app/vendor/autoload.php";

LoaderHelper::requireBootStrap();

$generator = new GenerateSampleTemplate();

# データ生成例
$generator->generateSampleTemplate('testblog2');
