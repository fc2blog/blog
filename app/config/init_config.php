<?php

$config = [];

// -------------------- ディレクトリ構造関連 --------------------//

$config['ROOT_DIR'] = realpath(dirname(__FILE__) . '/../../') . '/';

$config['UPLOAD_DIR_NAME'] = 'uploads';

$config['WWW_DIR'] = defined('WWW_DIR') ? WWW_DIR : $config['ROOT_DIR'] . 'public/';
$config['WWW_UPLOAD_DIR'] = $config['WWW_DIR'] . $config['UPLOAD_DIR_NAME'] . '/';

$config['APP_DIR'] = defined('APP_DIR') ? APP_DIR : $config['ROOT_DIR'] . 'app/';
$config['CORE_DIR'] = $config['APP_DIR'] . 'core/';
$config['CONFIG_DIR'] = $config['APP_DIR'] . 'config/';
$config['LOCALE_DIR'] = $config['APP_DIR'] . 'locale/';
$config['CONTROLLER_DIR'] = $config['APP_DIR'] . 'controller/';
$config['MODEL_DIR'] = $config['APP_DIR'] . 'model/';
$config['VIEW_DIR'] = $config['APP_DIR'] . 'view/';
$config['VIEW_COMMON_DIR'] = $config['VIEW_DIR'] . 'common/';
$config['LIB_DIR'] = $config['APP_DIR'] . 'lib/';
$config['TEMP_DIR'] = $config['APP_DIR'] . 'temp/';
$config['BLOG_TEMPLATE_DIR'] = $config['TEMP_DIR'] . 'blog_template/';

// -------------------- DB関連 --------------------//
// Master/Slave機能のON/OFF
$config['IS_MASTER_SLAVE'] = false;
$config['DB_CHARSET'] = DB_CHARSET;

// DBの接続情報
$config['MASTER_DB'] = array(
  'HOST' => DB_HOST,
  'USER' => DB_USER,
  'PASSWORD' => DB_PASSWORD,
  'DATABASE' => DB_DATABASE,
);

$config['SLAVE_DB'] = array(
  'HOST' => 'localhost',
  'USER' => 'root',
  'PASSWORD' => '',
  'DATABASE' => 'blog_slave',
);

// -------------------- Debug関連 --------------------//
// Debug 0 = false 1 = echo 2 = html 3 = link
$config['DEBUG'] = (defined("THIS_IS_TEST")) ? 5 : 0;                  // Debugの表示可否
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
$config['DOMAIN'] = DOMAIN;
$config['DOMAIN_USER'] = $config['DOMAIN'];
$config['DOMAIN_ADMIN'] = $config['DOMAIN'];

// ポート
$config['HTTP_PORT_STR'] = (HTTP_PORT === "80") ? '' : ":" . HTTP_PORT; // http時、80は省略できる
$config['HTTPS_PORT_STR'] = (HTTP_PORT === "443") ? '' : ":" . HTTPS_PORT; // https時、443は省略できる

// Sessionのデフォルト有効ドメイン
$config['SESSION_DEFAULT_DOMAIN'] = ""; // 省略時、アクセスドメインとなります

// SESSIONのID名
$config['SESSION_NAME'] = 'dojima';

// Cookieのデフォルト有効ドメイン
$config['COOKIE_DEFAULT_DOMAIN'] = ""; // JS用Cookie名 省略時、アクセスドメインとなります
$config['COOKIE_EXPIRE'] = 180; // 有効期限 180日

// URLの形式を書き換えるフラグ
$config['URL_REWRITE'] = false;

// ベースディレクトリ
$config['BASE_DIRECTORY'] = '/';

// directory indexファイル名
$config['DIRECTORY_INDEX'] = 'index.php';

// Controller引数
$config['ARGS_CONTROLLER'] = 'mode'; // TODO mode以外の状態が存在しない

// Action引数
$config['ARGS_ACTION'] = 'process'; // TODO process以外の状態が存在しない

// アプリプレフィックス(Controller,Viewのプレフィックス)
$config['APP_PREFIX'] = null;

// -------------------- アプリの定数系設定ファイル --------------------//

// デバイスタイプ
$config['DEVICE_PC'] = 1;   // PC
$config['DEVICE_SP'] = 4;   // スマフォ

// デバイスの値一覧
$config['DEVICES'] = array(
  $config['DEVICE_PC'],
  $config['DEVICE_SP'],
);

// デバイス毎のファイル修飾子
$config['DEVICE_PREFIX'] = array(
  1 => '_pc',   // PC
  4 => '_sp',   // スマフォ
);

// デバイス毎のFC2APIキー
$config['DEVICE_FC2_KEY'] = array(
  1 => 'pc',   // PC
  4 => 'sp',   // スマフォ
);

// デバイス毎の名称
// TODO I18Nの都合で動的になっているので、静的にできないか
$config['DEVICE_NAME'] = array(
  1 => __('PC'),             // PC
  4 => __('Smartphone'),     // スマフォ
);

// ブログテンプレートのデバイス毎のカラム名
$config['BLOG_TEMPLATE_COLUMN'] = array(
  1 => 'template_pc_id',
  4 => 'template_sp_id',
);

// ブログテンプレートのデバイス毎のリプライタイプカラム名
$config['BLOG_TEMPLATE_REPLY_TYPE_COLUMN'] = array(
  1 => 'template_pc_reply_type',
  4 => 'template_sp_reply_type',
);

// 許可デバイス一覧
$config['ALLOW_DEVICES'] = array(
  $config['DEVICE_PC'],
  $config['DEVICE_SP'],
);

// アプリ用定数
$config['APP'] = array(
  'DISPLAY' => array(
    'SHOW' => 0,    // 表示
    'HIDE' => 1,    // 非表示
  ),
);

// ユーザー系
$config['USER'] = array(
  'TYPE' => array(
    'NORMAL' => 0,
    'ADMIN' => 1,
  ),
  'REGIST_SETTING' => array(
    'NONE' => 0,  // 登録は受け付けない
    'FREE' => 1,  // 誰でも登録可能
  ),
  'REGIST_STATUS' => 0,   // ユーザーの登録受付状態
);

// ブログ系
$config['BLOG'] = array(
  'START_PAGE' => array(
    'NOTICE' => 0,  // お知らせページ
    'ENTRY' => 1,  // 記事投稿ページ
  ),
  'OPEN_STATUS' => array(
    'PUBLIC' => 0,  // 公開
    'PRIVATE' => 1,  // プライベートモード(パスワード保護)
  ),
  'DEFAULT_LIMIT' => 10,
  'SSL_ENABLE' => array(
    'DISABLE' => 0,  // 無効
    'ENABLE' => 1,  // 有効
  ),
  'REDIRECT_STATUS_CODE' => array(
    'MOVED_PERMANENTLY' => 301,
    'FOUND' => 302,
  ),
);

// ブログテンプレート
$config['BLOG_TEMPLATE'] = array(
  'COMMENT_TYPE' => array(
    'AFTER' => 1,      // 一つ下にコメントを差し込むタイプ
    'REPLY' => 2,      // １対１でコメントを返信するタイプ
  ),
);

// ブログプラグイン
$config['BLOG_PLUGIN'] = array(
  'CATEGORY' => array(
    'FIRST' => 1,     // 1番目
    'SECOND' => 2,     // 2番目
    'THIRD' => 3,     // 3番目
  ),
);

// 記事系
$config['ENTRY'] = array(
  // 公開設定
  'OPEN_STATUS' => array(
    'OPEN' => 1,  // 公開
    'PASSWORD' => 2,  // パスワード保護
    'DRAFT' => 3,  // 下書き
    'LIMIT' => 4,  // 期間限定
    'RESERVATION' => 5,  // 予約投稿
  ),
  // コメント受付
  'COMMENT_ACCEPTED' => array(
    'ACCEPTED' => 1,  // 受け付ける
    'REJECT' => 0,  // 受け付けない
  ),
  // 自動改行
  'AUTO_LINEFEED' => array(
    'USE' => 1,  // 自動改行を行う
    'NONE' => 0,  // 行わない
  ),
  // 記事の表示順
  'ORDER' => array(
    'ASC' => 0,
    'DESC' => 1,
  ),
  // TODO I18Nの都合で動的になっているので、静的にできないか
  // 記事一覧の表示件数リスト
  'LIMIT_LIST' => array(
    10 => '10' . __(' results'),
    20 => '20' . __(' results'),
    40 => '40' . __(' results'),
    60 => '60' . __(' results'),
    80 => '80' . __(' results'),
    100 => '100' . __(' results'),
  ),
  'DEFAULT_LIMIT' => 20,
);

// コメント系
$config['COMMENT'] = array(
  'OPEN_STATUS' => array(
    'PUBLIC' => 0,  // 全体公開
    'PENDING' => 2,  // 承認待ち
    'PRIVATE' => 1,  // 管理者のみ公開
  ),
  // コメントの確認設定
  'COMMENT_CONFIRM' => array(
    'THROUGH' => 0,  // 確認せずにそのまま表示
    'CONFIRM' => 1,  // コメントを確認する
  ),
  // 承認中、非公開コメントの代替コメント表示可否
  'COMMENT_DISPLAY' => array(
    'SHOW' => 0,    // 表示
    'HIDE' => 1,    // 非表示
  ),
  // コメント投稿時の名前、メールアドレス、URLを保存するかどうか
  'COMMENT_COOKIE_SAVE' => array(
    'NOT_SAVE' => 0,
    'SAVE' => 1,
  ),
  // コメント投稿時のCAPTCHA有無
  'COMMENT_CAPTCHA' => array(
    'NOT_USE' => 0,
    'USE' => 1,
  ),
  // コメントの表示順
  'ORDER' => array(
    'ASC' => 0,
    'DESC' => 1,
  ),
  // コメントの返信状態
  'REPLY_STATUS' => array(
    'UNREAD' => 1,  // 未読
    'READ' => 2,  // 既読
    'REPLY' => 3,  // 返信済み
  ),
  // コメントの引用を行うかどうか
  'QUOTE' => array(
    'USE' => 0,  // 引用を行う
    'NONE' => 1,  // 行わない
  ),
);

// カテゴリー系
$config['CATEGORY'] = array(
  // カテゴリーの表示順
  'ORDER' => array(
    'ASC' => 1,
    'DESC' => 0,
  ),
  'CREATE_LIMIT' => 100,
);

// タグ系
$config['TAG'] = array(
  // 記事一覧の表示件数リスト
  // TODO I18Nの都合で動的になっているので、静的にできないか
  'LIMIT_LIST' => array(
    10 => '10' . __(' results'),
    20 => '20' . __(' results'),
    40 => '40' . __(' results'),
    60 => '60' . __(' results'),
    80 => '80' . __(' results'),
    100 => '100' . __(' results'),
  ),
  'DEFAULT_LIMIT' => 20,
);

// ファイル系
$config['FILE'] = array(
  // ファイルの最大サイズ
  'MAX_SIZE' => 5242880,  // 5MB
);

// ページ毎の制限設定(上にあるLIMIT_LIST系は下記の配列に順次書き換えていく)
$config['PAGE'] = array(
  // ファイルの一覧表示系
  'FILE' => array(
    'DEFAULT' => array(
      'LIMIT' => 5,
      'LIST' => array(
        // TODO I18Nの都合で動的になっているので、静的にできないか
        5 => '5' . __(' results'),
        10 => '10' . __(' results'),
        20 => '20' . __(' results'),
        40 => '40' . __(' results'),
        60 => '60' . __(' results'),
        80 => '80' . __(' results'),
        100 => '100' . __(' results'),
      ),
    ),
    'SP' => array(
      'LIMIT' => 15,
      'LIST' => array(
        // TODO I18Nの都合で動的になっているので、静的にできないか
        15 => '15' . __(' results'),
      ),
    ),
  ),
  // メディアロード用
  'FILE_AJAX' => array(
    'DEFAULT' => array('LIMIT' => 18),
    'SP' => array('LIMIT' => 15),
  ),
  // プラグイン検索一覧
  'PLUGIN' => array(
    'DEFAULT' => array(
      'LIMIT' => 20,
    ),
  ),
);

return $config;
