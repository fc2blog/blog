<?php

declare(strict_types=1);

require_once(__DIR__ . '/../vendor/autoload.php');

require(__DIR__ . '/config_loading_include.php');

$request = new \Fc2blog\Web\Request();

$c = new $request->className($request);
$c->execute($request->methodName);
$c->emit();
