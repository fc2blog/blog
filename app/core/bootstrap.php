<?php

define('REQUEST_MICROTIME', microtime(true));                   // 開始タイムスタンプ(ミリ秒含む)
define('APP_DIR', realpath(dirname(__FILE__) . '/../') . '/');  // APPディレクトリのパス

// DBの接続ライブラリ
if (class_exists('mysqli')) {
  define('DB_CONNECT_LIB', 'mysqli');
} else if(class_exists('PDO')) {
  define('DB_CONNECT_LIB', 'PDO');
}

// 設定クラス読み込み
require(dirname(__FILE__) . '/config.php');

// ディレクトリ一覧読み込み
Config::read(dirname(__FILE__) . '/../config/dir.php');

// 環境設定読み込み
Config::read('env.php');

// 疑似Exitクラスの読み込み（テスト用）
require_once(Config::get('CORE_DIR') . 'PseudoExit.php');

// リクエストクラスの読み込み
require_once(Config::get('CORE_DIR') . 'request.php');

// タイムゾーン設定
date_default_timezone_set(Config::get('TIMEZONE'));

// Debugクラス
require(Config::get('CORE_DIR') . 'debug.php');

// 共通関数群読み込み
require(Config::get('CORE_DIR') . 'common_functions.php');

// Cookieクラス
require_once(Config::get('CORE_DIR') . 'cookie.php');

// Sessionクラス
require_once(Config::get('CORE_DIR') . 'session.php');

// 言語設定
setLanguage();

// アプリの定数系読み込み
Config::read('app.php');

// 内部文字コードを設定
mb_internal_encoding(Config::get('INTERNAL_ENCODING', 'UTF-8'));

