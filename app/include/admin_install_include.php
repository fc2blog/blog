<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

require(__DIR__ . '/config_loading_include.php');

$request = new \Fc2blog\Web\Request();

\Fc2blog\Config::read('admin.php'); // Admin用の環境設定読み込み

$controller = new \Fc2blog\Web\Controller\Admin\CommonController($request,'install');
