<?php

declare(strict_types=1);

$config = [];

$config['APP_PREFIX'] = 'User';

$config['DEFAULT_CLASS_NAME'] = 'BlogsController';
// DELME $config['DEFAULT_METHOD_NAME'] = 'index';
$config['CLASS_PREFIX'] = "\\Fc2blog\\Web\\Controller\\User\\";

$config['URL_REWRITE'] = false;

// ルーティング設定
$config['ROUTING'] = 'routing_user.php';

return $config;
