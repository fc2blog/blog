<?php /** @noinspection PhpFullyQualifiedNameUsageInspection */
// TODO init_config.phpと統合できるのではないか？
define('REQUEST_MICROTIME', microtime(true)); // 開始タイムスタンプ(ミリ秒含む)
define('APP_DIR', realpath(__DIR__ . '/../../') . '/'); // APPディレクトリのパス

// 環境設定読み込み
\Fc2blog\Config::read(__DIR__ . '/../config/init_config.php');

// タイムゾーン設定 TODO php.ini移譲でよいのではないか？
date_default_timezone_set(\Fc2blog\Config::get('TIMEZONE'));

// 内部文字コードを設定 TODO 非推奨なので消し込む
mb_internal_encoding('UTF-8');
