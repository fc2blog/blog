<?php
// `app`ディレクトリを$app_dir_pathに設定してください
// Please set actual `app` directory path to $app_dir_path.
$app_dir_path = __DIR__ . "/../app";

if (!file_exists($app_dir_path . "/src/include/index_include.php")) {
  die("please set correct `\$app_dir_path` in index.php ");
}

require($app_dir_path . "/src/include/index_include.php");
