#!/usr/bin/env php
<?php
declare(strict_types=1);

use Fc2blog\Config;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleTag;
use Fc2blog\Tests\LoaderHelper;

define("TEST_APP_DIR", __DIR__ . "/../app");

require_once __DIR__ . "/../app/vendor/autoload.php";

LoaderHelper::requireBootStrap();

Config::set('DEBUG', 0); // suppress debug message.

$generator = new GenerateSampleTag();

$blog_id = 'testblog2';
$entry_id = 1;

# データ生成例
$tags = $generator->generateSampleTagsToSpecifyEntry($blog_id, $entry_id);
var_dump($tags);
