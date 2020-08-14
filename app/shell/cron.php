<?php
// TODO Cronは元来正しく動作しない？ fix/cron-wip ブランチで作業中
error_reporting(E_ALL);

require_once(__DIR__ . "/../vendor/autoload.php");

require(__DIR__ . '/../core/bootstrap.php');

\Fc2blog\Config::read('cron.php');    // cron用の環境設定読み込み

\Fc2blog\Request::getInstance()->setCronParams($argv);

list($className, $methodName) = getRouting(\Fc2blog\Config::get('DEFAULT_CLASS_NAME'), \Fc2blog\Config::get('DEFAULT_METHOD_NAME'), \Fc2blog\Config::get('APP_PREFIX'));
$controller = new $className();

\Fc2blog\Debug::log('Controller Action', false, 'system', __FILE__, __LINE__);
$controller->process($methodName);

