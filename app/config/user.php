<?php

$config = array();

$config['APP_PREFIX'] = 'User';

$config['DEFAULT_CLASS_NAME'] = 'BlogsController';
$config['DEFAULT_METHOD_NAME'] = 'index';
$config['CLASS_PREFIX'] = "\\Fc2blog\\Web\\Controller\\User\\";

$config['URL_REWRITE'] = false;

// ルーティング設定
$config['ROUTING'] = 'routing_user.php';

return $config;
