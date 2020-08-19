<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

require(__DIR__ . '/config_loading_include.php');

$request = new \Fc2blog\Web\Request();

\Fc2blog\Config::set('URL_REWRITE', true);
\Fc2blog\Config::set('BASE_DIRECTORY', '/admin/');
\Fc2blog\Config::set('APP_PREFIX', 'Admin');

$controller = new \Fc2blog\Web\Controller\Admin\CommonController($request,'install');
