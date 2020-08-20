#!/usr/bin/env php
<?php

use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleCategory;

include __DIR__ . "/bootstrap.php";

$generator = new GenerateSampleCategory();

# データ生成例
$generator->generateSampleCategory('testblog2');

# リスト取得例
$list = $generator->getCategoryList('testblog2');
var_dump($list);

# 全削除例
$generator->syncRemoveAllCategories('testblog2');

# 全削除の確認
$list = $generator->getCategoryList('testblog2');
var_dump($list);
