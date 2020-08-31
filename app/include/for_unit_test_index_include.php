<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

require(__DIR__ . '/config_loading_include.php');

// test endpoint work in only TEST mode.
if ((string)getenv("FC2_ENABLE_UNIT_TEST_ENDPOINT") !== "1") {
  die("If you want use this, please set FC2_ENABLE_UNIT_TEST_ENDPOINT ENV.");
}
// test endpoint is only for testing. Ignore access without local ip address.
if (!preg_match("/\A(127.0.0.|172.24.)/u", $_SERVER['REMOTE_ADDR'])) {
  http_response_code(403);
  echo "forbidden";
  exit;
}

$request = new \Fc2blog\Web\Request();

$c = new $request->className($request);
$c->execute($request->methodName);
$c->emit();
