<?php

$config = array();

// -------------------- DB関連 --------------------//
// Master/Slave機能のON/OFF
$config['IS_MASTER_SLAVE'] = false;
$config['DB_CHARSET'] = DB_CHARSET;

// DBの接続情報
$config['MASTER_DB'] = array(
  'HOST'     => DB_HOST,
  'USER'     => DB_USER,
  'PASSWORD' => DB_PASSWORD,
  'DATABASE' => DB_DATABASE,
);

$config['SLAVE_DB'] = array(
  'HOST'     => 'localhost',
  'USER'     => 'root',
  'PASSWORD' => '',
  'DATABASE' => 'blog_slave',
);

// -------------------- Debug関連 --------------------//
// Debug 0 = false 1 = echo 2 = html 3 = link
$config['DEBUG'] = 0;                  // Debugの表示可否
$config['DEBUG_TEMPLATE_VARS'] = 0;    // テンプレートで使用可能な変数の一覧表示

// -------------------- 色々 --------------------//
// 言語設定
$config['LANG'] = 'ja';

// 国際化対応用
$config['LANGUAGE'] = 'ja_JP.UTF-8';

// 国際化対応用の対応言語一覧
$config['LANGUAGES'] = array(
  'ja' => 'ja_JP.UTF-8',
  'en' => 'en_US.UTF-8',
);

// エディタの言語切り替え互換用
$config['LANG_ELRTE'] = array(
  'ja' => 'jp',
  'en' => 'en',
);

// タイムゾーン
$config['TIMEZONE'] = 'Asia/Tokyo';

// 内部エンコード
$config['INTERNAL_ENCODING'] = 'UTF-8';

// cron実行
$config['CRON'] = false;

// パスワード用のsalt
$config['PASSWORD_SALT'] = PASSWORD_SALT;

// ドメイン
$config['DOMAIN']  = DOMAIN;
$config['DOMAIN_USER']  = $config['DOMAIN'];
$config['DOMAIN_ADMIN'] = $config['DOMAIN'];

// Sessionのデフォルト有効ドメイン
$config['SESSION_DEFAULT_DOMAIN'] = $config['DOMAIN'];

// SESSIONのID名
$config['SESSION_NAME'] = 'dojima';

// Cookieのデフォルト有効ドメイン
$config['COOKIE_DEFAULT_DOMAIN'] = $config['DOMAIN'];
$config['COOKIE_EXPIRE'] = 180;   // 有効期限 180日

// URLの形式を書き換えるフラグ
$config['URL_REWRITE'] = false;

// ベースディレクトリ
$config['BASE_DIRECTORY'] = '/';

// directory indexファイル名
$config['DIRECTORY_INDEX'] = 'index.php';

// Controller引数
$config['ARGS_CONTROLLER'] = 'mode';

// Action引数
$config['ARGS_ACTION'] = 'process';

// ルーティング設定
$config['ROUTING'] = 'routing.php';

// アプリプレフィックス(Controller,Viewのプレフィックス)
$config['APP_PREFIX'] = null;

return $config;
