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

// DB settings
define('DB_HOST', (string)getenv("FC2_DB_HOST"));
define('DB_USER', (string)getenv("FC2_DB_USER"));
define('DB_PASSWORD', (string)getenv("FC2_DB_PASSWORD"));
define('DB_DATABASE', (string)getenv("FC2_DB_DATABASE"));
define('DB_CHARSET', (string)getenv("FC2_DB_CHARSET"));

// http settings
define('DOMAIN', (string)getenv("FC2_DOMAIN")); // ex: localhost, local.test or your domain.
define('HTTP_PORT', (string)getenv("FC2_HTTP_PORT")); // ex: 80, 8080
define('HTTPS_PORT', (string)getenv("FC2_HTTPS_PORT")); // ex: 443, 8480

// PASSWORD_SALTは必ず十分にランダムなテキストへ変更してください。
// PASSWORD_SALT should be unique and random string.
// ex: $ php -r 'echo hash("sha256", random_bytes(1024));'
define('PASSWORD_SALT', (string)getenv("FC2_PASSWORD_SALT"));

// publicとappの位置関係を修正した場合には変更してください
// Please edit the path when change `app` and `public` relative path condition.
define('WWW_DIR', (string)getenv("FC2_DOCUMENT_ROOT_PATH"));

// 設定クラス読み込み
require(__DIR__ . '/core/bootstrap.php');
