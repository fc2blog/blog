<?php
// test endpoint is only for testing. Ignore access without local ip address.
if(!preg_match("/\A(127.0.0.|172.24.)/u", $_SERVER['REMOTE_ADDR'])){
  http_response_code(403);
  echo "forbidden";
  exit;
}

require('../config.php');
Config::read('test.php'); // test用の環境設定読み込み

Debug::log('Controller Action', false, 'system', __FILE__, __LINE__);

list($classFile, $className, $methodName) = getRouting();
require($classFile);
$controller = new $className($methodName);

Debug::output($controller);

