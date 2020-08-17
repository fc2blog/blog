<?php

define('REQUEST_MICROTIME', microtime(true));                   // 開始タイムスタンプ(ミリ秒含む)
define('APP_DIR', realpath(__DIR__ . '/../') . '/');  // APPディレクトリのパス

// DBの接続ライブラリ
if (class_exists('mysqli')) {
  define('DB_CONNECT_LIB', 'mysqli');
} else if(class_exists('PDO')) {
  define('DB_CONNECT_LIB', 'PDO');
}

// ディレクトリ一覧読み込み
\Fc2blog\Config::read(__DIR__ . '/../config/dir.php');

// 環境設定読み込み
\Fc2blog\Config::read('env.php');

// タイムゾーン設定
date_default_timezone_set(\Fc2blog\Config::get('TIMEZONE'));

// 言語設定
setLanguage();

// アプリの定数系読み込み
\Fc2blog\Config::read('app.php');

// 内部文字コードを設定
mb_internal_encoding(\Fc2blog\Config::get('INTERNAL_ENCODING', 'UTF-8'));

