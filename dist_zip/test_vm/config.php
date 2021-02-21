<?php
error_reporting(-1);

// DBの接続情報
define('DB_HOST', '127.0.0.1'); // dbのホスト名
define('DB_PORT', '3306'); // dbのホスト名
define('DB_USER', 'dbuser'); // dbのユーザー名
define('DB_PASSWORD', 'd1B2p3a#s!s'); // dbのパスワード
define('DB_DATABASE', 'fc2'); // dbのデータベース名
define('DB_CHARSET', 'UTF8MB4'); // MySQL 5.5未満の場合はUTF8を指定してください

// サーバーの設定情報
define('DOMAIN', 'example.test'); // ドメイン名
define('HTTP_PORT', '80'); // HTTP時ポート
define('HTTPS_PORT', '443'); // HTTPS時ポート

define('WWW_DIR', __DIR__ . '/../html/');

require(__DIR__ . '/core/bootstrap.php');
