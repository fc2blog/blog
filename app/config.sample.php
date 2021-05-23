<?php
// Sample Config file
// Please copy `config.sample.php` to `config.php` and edit them.

error_reporting(-1);

// DBの接続情報
define('DB_HOST', 'localhost'); // dbのホスト名
define('DB_PORT', '3306'); // dbのホスト名
define('DB_USER', 'your user name'); // dbのユーザー名
define('DB_PASSWORD', 'your password'); // dbのパスワード
define('DB_DATABASE', 'your database name'); // dbのデータベース名
define('DB_CHARSET', 'UTF8MB4'); // MySQL 5.5未満の場合はUTF8を指定してください

// サーバーの設定情報
define('DOMAIN', 'example.test'); // ドメイン名
define('HTTP_PORT', '80'); // HTTP時ポート
define('HTTPS_PORT', '443'); // HTTPS時ポート

// publicとappの位置関係を修正した場合には変更してください
// Please edit the path when change `app` and `public` relative path condition.
define('WWW_DIR', __DIR__ . '/../public/'); // this path need finish with slash.

define("ADMIN_MAIL_ADDRESS", "your_email_addr@example.jp");
// メールが送信出来ない環境で、パスワードリセットする場合にのみ1を設定してください。
// パスワードリセットが成功した後は必ず "1" 以外の "0" 等の値に変更してください。
define("EMERGENCY_PASSWORD_RESET_ENABLE", "0");

// If you want get error log on display.
// define('ERROR_ON_DISPLAY', "1");
// ini_set('display_errors', '1');

// 別のGitHub repoを追従する場合に設定してください
// define('GITHUB_REPO', '/uzulla/fc2blog');
