<?php

declare(strict_types=1);

require_once(__DIR__ . '/../../vendor/autoload.php');

// config.phpの存在チェック
if (!file_exists(__DIR__ . '/../config.php') && (string)getenv("FC2_CONFIG_FROM_ENV") !== "1") {
  header("Content-Type: text/html; charset=UTF-8");
  echo <<<HTML
<!DOCTYPE html>
<html lang="ja">
<head>
<title>Does not exists config.php</title>
</head>
<body>
  Does not exists config.php / config.phpが存在しておりません
  <p class="ng">
    Please copy app/config.sample.php to app/config.php and edit them.<br>
    app/config.sample.phpをapp/config.phpにコピーしファイル内に存在するDBの接続情報とサーバーの設定情報を入力してください。
  </p>
</body>
</html>
HTML;
  exit;
}

// 設定クラス読み込み
if ((string)getenv("FC2_CONFIG_FROM_ENV") === "1") {
  require(__DIR__ . '/../../config_read_from_env.php');
} else {
  require(__DIR__ . '/../../config.php');
}
require(__DIR__ . '/bootstrap.php');

$request = new \Fc2blog\Web\Request();

$c = new $request->className($request);
$c->execute($request->methodName);
$c->emit();
