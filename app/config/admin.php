<?php

declare(strict_types=1);

$config = [];

$config['APP_PREFIX'] = 'Admin';

$config['DEFAULT_CLASS_NAME'] = 'CommonController';
$config['DEFAULT_METHOD_NAME'] = 'index';
$config['CLASS_PREFIX'] = "\\Fc2blog\\Web\\Controller\\Admin\\";

$config['URL_REWRITE'] = true;

$config['BASE_DIRECTORY'] = '/admin/';

return $config;
