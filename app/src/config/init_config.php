<?php
// TODO クラスのConstであるべきものと設定が混在しているので分割する
// TODO bootstrap.phpと統合できると思われる

$config = [];

// -------------------- ディレクトリ構造関連 --------------------//
# APPへ
$config['WWW_DIR'] = WWW_DIR;
$config['WWW_UPLOAD_DIR'] = $config['WWW_DIR'] . 'uploads/';

$config['APP_DIR'] = APP_DIR;
$config['CONFIG_DIR'] = $config['APP_DIR'] . 'src/config/';
$config['LOCALE_DIR'] = $config['APP_DIR'] . 'locale/';
$config['VIEW_DIR'] = $config['APP_DIR'] . 'view/';
$config['TEMP_DIR'] = $config['APP_DIR'] . 'temp/';
$config['BLOG_TEMPLATE_DIR'] = $config['TEMP_DIR'] . 'blog_template/';

// Enable DEBUG log.
$config['APP_DEBUG'] = defined("APP_DEBUG") ? APP_DEBUG : 0;
$config['SQL_DEBUG'] = defined("SQL_DEBUG") ? SQL_DEBUG : 0;

// -------------------- 色々 --------------------//
// 言語設定
# APPへ
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

# APPへ
// タイムゾーン
$config['TIMEZONE'] = 'Asia/Tokyo';
// 内部エンコード
$config['INTERNAL_ENCODING'] = 'UTF-8';

// ドメイン
$config['DOMAIN'] = DOMAIN;
$config['DOMAIN_USER'] = $config['DOMAIN'];
$config['DOMAIN_ADMIN'] = $config['DOMAIN'];

// ポート
# Requestへ？
$config['HTTP_PORT_STR'] = (HTTP_PORT === "80") ? '' : ":" . HTTP_PORT; // http時、80は省略できる
$config['HTTPS_PORT_STR'] = (HTTP_PORT === "443") ? '' : ":" . HTTPS_PORT; // https時、443は省略できる

# Controllerへ？
// SESSIONのID名
$config['SESSION_NAME'] = 'dojima';

// Cookieの有効期限、N日
$config['COOKIE_EXPIRE'] = 180;

// -------------------- アプリの定数系設定ファイル --------------------//
# APPへ
// デバイスタイプ
$config['DEVICE_PC'] = 1; // PC
$config['DEVICE_SP'] = 4; // スマフォ

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

// デバイス毎の名称(英名)
$config['DEVICE_NAME'] = array(
    1 => 'PC',
    4 => 'Smartphone',
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

// アプリ用定数 // TODO 存在意義がよくわからない…
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
    // 記事一覧の表示件数リスト
    'LIMIT_LIST' => array(
        10 => '10',
        20 => '20',
        40 => '40',
        60 => '60',
        80 => '80',
        100 => '100',
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
    'LIMIT_LIST' => array(
        10 => '10',
        20 => '20',
        40 => '40',
        60 => '60',
        80 => '80',
        100 => '100',
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
                5 => '5',
                10 => '10',
                20 => '20',
                40 => '40',
                60 => '60',
                80 => '80',
                100 => '100',
            ),
        ),
        'SP' => array(
            'LIMIT' => 15,
            'LIST' => array(
                15 => '15',
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

# APPへ
// テスト用のUserAgentではデフォルトブログ機能を強制オフにする
// TODO E2E testでシングルテナントモード対応ができたら外す
if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/THIS_IS_TEST/u", $_SERVER['HTTP_USER_AGENT'])) {
    $config['DEFAULT_BLOG_ID'] = null;
} else {
    $config['DEFAULT_BLOG_ID'] = defined("DEFAULT_BLOG_ID") ? DEFAULT_BLOG_ID : null;
}

$config['ADMIN_MAIL_ADDRESS'] = defined("ADMIN_MAIL_ADDRESS") ? ADMIN_MAIL_ADDRESS : "noreply@example.jp";
$config['MFA_EMAIL'] = defined("MFA_EMAIL") ? MFA_EMAIL : null;

return $config;
