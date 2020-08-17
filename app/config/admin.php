<?php

$config = array();

$config['APP_PREFIX'] = 'Admin';

$config['DEFAULT_CLASS_NAME'] = 'CommonController';
$config['DEFAULT_METHOD_NAME'] = 'index';
$config['CLASS_PREFIX'] = "\\Fc2blog\\Web\\Controller\\Admin\\";

//$config['DIRECTORY_INDEX'] = 'admin.php';

$config['URL_REWRITE'] = true;

$config['BASE_DIRECTORY'] = '/admin/';

return $config;
