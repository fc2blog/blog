<?php

//error_reporting(-1);
error_reporting(0);

// 直接呼び出された場合は終了
if (count(get_included_files())==1) {
  exit;
}

// DBの接続情報
define('DB_HOST',     'localhost');          // dbのホスト名
define('DB_USER',     'your user name');     // dbのユーザー名
define('DB_PASSWORD', 'your password');      // dbのパスワード
define('DB_DATABASE', 'your database name'); // dbのデータベース名
define('DB_CHARSET',  'UTF8MB4');            // MySQL 5.5未満の場合はUTF8を指定してください

// サーバーの設定情報
define('DOMAIN', 'domain'); // ドメイン名
define('HTTP_PORT', '80'); // HTTP時ポート
define('HTTPS_PORT', '443'); // HTTPS時ポート
define('PASSWORD_SALT', '0123456789abcdef'); // 適当な英数字を入力してください

// 設定クラス読み込み
define('WWW_DIR', dirname(__FILE__) . '/');
require(dirname(__FILE__) . '/../app/core/bootstrap.php');

