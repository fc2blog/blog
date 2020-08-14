<?php

$config = array();

$config['APP_PREFIX'] = 'Cron';

$config['DEFAULT_CLASS_NAME'] = 'CommonController';
$config['DEFAULT_METHOD_NAME'] = 'index';
$config['CLASS_PREFIX'] = "\\Fc2blog\\Cli\\Controller\\Cron\\";
$config['DEBUG'] =  4;                 // Debugの表示可否

return $config;
