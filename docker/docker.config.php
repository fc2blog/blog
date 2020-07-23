<?php
// This configuration is for development purposes only. DO NOT USE it in a PRODUCTION or PUBLIC SITE.
// (この設定ファイルはDockerによる開発用のファイルです。 *公開サイトには使わないでください* ）

error_reporting(-1);
ini_set('error_log', 'php://stderr');
ini_set('log_errors', '1');
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('html_errors', '0');
ini_set('ignore_repeated_errors', '0');

// 直接呼び出された場合は終了
if (count(get_included_files())==1) {
  exit;
}

// DBの接続情報
if (getenv("MYSQL_HOST")) {
  define('DB_HOST', 'db'); // docker apache時
} else {
  define('DB_HOST', '127.0.0.1'); // phpunit時
}

define('DB_USER',     'docker');     // dbのユーザー名
define('DB_PASSWORD', 'docker');      // dbのパスワード
define('DB_DATABASE', 'dev_fc2blog'); // dbのデータベース名
define('DB_CHARSET',  'UTF8MB4');            // MySQL 5.5未満の場合はUTF8を指定してください

// サーバーの設定情報
define('DOMAIN',        'localhost:8080');           // ドメイン名
// https有効環境のテストをする際には上をコメントアウトし、以下を有効にしてください。URLは https://localhost:8480/ となります。
//define('DOMAIN',        'localhost:8480');           // ドメイン名
define('PASSWORD_SALT', '7efe3a5b4d111b9e2148e24993c1cfdb'); // 適当な英数字を入力してください

// 設定クラス読み込み
define('WWW_DIR', '/var/www/html/');
require(dirname(__FILE__) . '/../app/core/bootstrap.php');
