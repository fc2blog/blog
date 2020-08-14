<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

require(__DIR__ . '/config_loading_include.php');

\Fc2blog\Config::read('admin.php'); // Admin用の環境設定読み込み

require(\Fc2blog\Config::get('CONTROLLER_DIR') . 'admin/common_controller.php');
$controller = new CommonController('install');
