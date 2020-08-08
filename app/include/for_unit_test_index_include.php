<?php
require(__DIR__ . '/config_loading_include.php');

// test endpoint work in only DEBUG mode.
if((string)getenv("FC2_ENABLE_UNIT_TEST_ENDPOINT") !== "1"){
  die("If you want use this, please set FC2_ENABLE_UNIT_TEST_ENDPOINT ENV.");
}
// test endpoint is only for testing. Ignore access without local ip address.
if(!preg_match("/\A(127.0.0.|172.24.)/u", $_SERVER['REMOTE_ADDR'])){
  http_response_code(403);
  echo "forbidden";
  exit;
}

Config::read('test.php'); // test用の環境設定読み込み

Debug::log('Controller Action', false, 'system', __FILE__, __LINE__);

list($classFile, $className, $methodName) = getRouting();
require($classFile);
$controller = new $className($methodName);

Debug::output($controller);
