<?php

$config = array();

$config['ROOT_DIR'] = realpath(dirname(__FILE__) . '/../../') . '/';

$config['UPLOAD_DIR_NAME'] = 'uploads';

$config['WWW_DIR']        = defined('WWW_DIR') ? WWW_DIR : $config['ROOT_DIR'] . 'public/';
$config['WWW_UPLOAD_DIR'] = $config['WWW_DIR'] . $config['UPLOAD_DIR_NAME'] . '/';

$config['APP_DIR']           = defined('APP_DIR') ? APP_DIR : $config['ROOT_DIR'] . 'app/';
$config['CORE_DIR']          = $config['APP_DIR']  . 'core/';
$config['CONFIG_DIR']        = $config['APP_DIR']  . 'config/';
$config['LOCALE_DIR']        = $config['APP_DIR']  . 'locale/';
$config['CONTROLLER_DIR']    = $config['APP_DIR']  . 'controller/';
$config['MODEL_DIR']         = $config['APP_DIR']  . 'model/';
$config['VIEW_DIR']          = $config['APP_DIR']  . 'view/';
$config['VIEW_COMMON_DIR']   = $config['VIEW_DIR'] . 'common/';
$config['LIB_DIR']           = $config['APP_DIR']  . 'lib/';
$config['TEMP_DIR']          = $config['APP_DIR']  . 'temp/';
$config['BLOG_TEMPLATE_DIR'] = $config['TEMP_DIR'] . 'blog_template/';

return $config;
