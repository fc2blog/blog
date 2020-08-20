#!/usr/bin/env php
<?php
declare(strict_types=1);

use Fc2blog\Config;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleCategory;
use Fc2blog\Tests\LoaderHelper;

define("TEST_APP_DIR", __DIR__ . "/../app");

require_once __DIR__ . "/../app/vendor/autoload.php";

LoaderHelper::requireBootStrap();

Config::set('DEBUG', 0); // suppress debug message.

$generator = new GenerateSampleCategory();

# データ生成例
$generator->generateSampleCategories('testblog2');

//# リスト取得例
//$list = $generator->getCategoryList('testblog2');
//var_dump($list);
//
//# 全削除例
//$generator->syncRemoveAllCategories('testblog2');
//
//# 全削除の確認
//$list = $generator->getCategoryList('testblog2');
//var_dump($list);
