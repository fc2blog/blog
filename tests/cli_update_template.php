#!/usr/bin/env php
<?php

declare(strict_types=1);

define("TEST_APP_DIR", __DIR__ . "/../app");
define("THIS_IS_TEST", true);

use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Tests\LoaderHelper;

require_once __DIR__ . "/../app/vendor/autoload.php";

LoaderHelper::requireBootStrap();

Config::set('DEBUG', 0); // suppress debug message.

// CLI param check
if ($argc !== 2) {
  echo "usage: this_cli.php {blog_id}" . PHP_EOL;
  exit(1);
}
$blog_id = $argv[1];

$blogs = new BlogsModel();

$blog = $blogs->findById($blog_id);

if (empty($blog)) {
  echo "not found blog_id:{$blog_id}" . PHP_EOL;
  exit(1);
}

$blogs->resetToDefaultTemplateByBlogId($blog_id);

echo "done" . PHP_EOL;
exit(0);
