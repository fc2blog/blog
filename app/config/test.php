<?php

declare(strict_types=1);

$config = [];

$config['APP_PREFIX'] = 'Test';

$config['DEFAULT_CLASS_NAME'] = 'CommonController';
$config['DEFAULT_METHOD_NAME'] = 'index';
$config['CLASS_PREFIX'] = "\\Fc2blog\\Web\\Controller\\Test\\";

$config['URL_REWRITE'] = true;

$config['BASE_DIRECTORY'] = '/_for_unit_test_/';

// ルーティング設定
$config['ROUTING'] = 'routing_test.php';

return $config;
