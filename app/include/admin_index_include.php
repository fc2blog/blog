<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

require(__DIR__ . '/config_loading_include.php');

\Fc2blog\Config::read('admin.php'); // Admin用の環境設定読み込み

Debug::log('Controller Action', false, 'system', __FILE__, __LINE__);

list($classFile, $className, $methodName) = getRouting();
require($classFile);
$controller = new $className($methodName);

Debug::output($controller); // Debug用の出力
