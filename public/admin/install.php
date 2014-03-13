<?php

// config.phpの存在チェック
if (!file_exists('../config.php')) {
  header("Content-Type: text/html; charset=UTF-8");
  echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<body>
  config.phpが存在しておりません
  <p class="ng">
    config.php.sampleをconfig.phpに変更し<br />
    ファイル内に存在するDBの接続情報とサーバーの設定情報を入力してください
  </p>
</body>
</html>
HTML;
  exit;
}

require('../config.php');
Config::read('admin.php');                           // Admin用の環境設定読み込み

require(Config::get('CONTROLLER_DIR') . 'admin/common_controller.php');
$controller = new CommonController('install');

