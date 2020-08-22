<?php

define('REQUEST_MICROTIME', microtime(true)); // 開始タイムスタンプ(ミリ秒含む)
define('APP_DIR', realpath(__DIR__ . '/../') . '/'); // APPディレクトリのパス

// DBの接続ライブラリ
if (class_exists('mysqli')) {
  define('DB_CONNECT_LIB', 'mysqli');
} else if (class_exists('PDO')) {
  define('DB_CONNECT_LIB', 'PDO');
}

// 環境設定読み込み
\Fc2blog\Config::read(__DIR__ . '/../config/init_config.php');


