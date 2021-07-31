<?php
// TODO クラスのConstであるべきものと設定が混在しているので分割する
// TODO bootstrap.phpと統合できると思われる

$config = [];

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
