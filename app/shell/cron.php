<?php

error_reporting(E_ALL);

require(dirname(__FILE__) . '/../core/bootstrap.php');
\Fc2blog\Config::read('cron.php');    // cron用の環境設定読み込み

require_once(\Fc2blog\Config::get('CORE_DIR') . 'Request.php');
Request::getInstance()->setCronParams($argv);

list($classFile, $className, $methodName) = getRouting(\Fc2blog\Config::get('DEFAULT_CLASS_NAME'), \Fc2blog\Config::get('DEFAULT_METHOD_NAME'), \Fc2blog\Config::get('APP_PREFIX'));
require($classFile);
$controller = new $className();

Debug::log('Controller Action', false, 'system', __FILE__, __LINE__);
$controller->process($methodName);

