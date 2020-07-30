<?php

$config = array();

$config['APP_PREFIX'] = 'user';

$config['DEFAULT_CLASS_NAME'] = 'BlogsController';
$config['DEFAULT_METHOD_NAME'] = 'index';

$config['URL_REWRITE'] = false;

// ルーティング設定
$config['ROUTING'] = 'routing_user.php';

return $config;
