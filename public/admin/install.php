<?php
// `app`ディレクトリを$app_dir_pathに設定してください
// Please set actual app directory path to $app_dir_path.
$app_dir_path = __DIR__ . "/../../app";

if(!file_exists($app_dir_path)){
  die("please edit public/admin/install.php");
}

require($app_dir_path . "/include/admin_install_include.php");
