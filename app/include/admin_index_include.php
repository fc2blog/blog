<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

require(__DIR__ . '/config_loading_include.php');

\Fc2blog\Debug::log('Controller Action', false, 'system', __FILE__, __LINE__);

$request = getRouting('admin.php');
$controller = new $request->className($request, $request->methodName);

\Fc2blog\Debug::output($controller); // Debug用の出力
