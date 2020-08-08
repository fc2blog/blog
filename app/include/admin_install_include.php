<?php
require(__DIR__ . '/config_loading_include.php');

Config::read('admin.php'); // Admin用の環境設定読み込み

require(Config::get('CONTROLLER_DIR') . 'admin/common_controller.php');
$controller = new CommonController('install');
