<?php
// 設定を環境変数から読み込むコンフィグです。
// ENV and config.php bridge.

if (strlen((string)getenv("FC2_ERROR_LOG_PATH")) > 0) {
  ini_set('error_log', (string)getenv("FC2_ERROR_LOG_PATH"));
}

if ((string)getenv("FC2_STRICT_ERROR_REPORT") === "1") {
  error_reporting(-1);
  ini_set('log_errors', '1');
  ini_set('ignore_repeated_errors', '0');
}

if ((string)getenv("FC2_ERROR_ON_DISPLAY") === "1") {
  ini_set('display_errors', '1');
  ini_set('display_startup_errors', '1');
  ini_set('html_errors', '1');
}

// Log settings
define('APP_LOG_PATH', (string)getenv('FC2_APP_LOG_PATH'));
define('APP_LOG_LEVEL', (int)getenv('FC2_APP_LOG_LEVEL'));
define('SQL_DEBUG', (bool)getenv('FC2_SQL_DEBUG'));
define('APP_DEBUG', (bool)getenv('FC2_APP_DEBUG'));

// DB settings
define('DB_HOST', (string)getenv("FC2_DB_HOST"));
define('DB_PORT', (string)getenv("FC2_DB_PORT"));
define('DB_USER', (string)getenv("FC2_DB_USER"));
define('DB_PASSWORD', (string)getenv("FC2_DB_PASSWORD"));
define('DB_DATABASE', (string)getenv("FC2_DB_DATABASE"));
define('DB_CHARSET', (string)getenv("FC2_DB_CHARSET"));

// http settings
define('DOMAIN', (string)getenv("FC2_DOMAIN")); // ex: localhost, local.test or your domain.
define('HTTP_PORT', (string)getenv("FC2_HTTP_PORT")); // ex: 80, 8080
define('HTTPS_PORT', (string)getenv("FC2_HTTPS_PORT")); // ex: 443, 8480

// publicとappの位置関係を修正した場合には変更してください
// Please edit the path when change `app` and `public` relative path condition.
define('WWW_DIR', (string)getenv("FC2_DOCUMENT_ROOT_PATH"));

//define('DEFAULT_BLOG_ID', (string)getenv("DEFAULT_BLOG_ID"));
define("DEFAULT_BLOG_ID", (string)getenv("FC2_DEFAULT_BLOG_ID"));

// 設定クラス読み込み
require(__DIR__ . '/core/bootstrap.php');
