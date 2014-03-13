<?php
/**
* ユーザーページ用のルーティング処理
*/

// ユーザー用のパラメータを設定する
$request = Request::getInstance();
$path = $request->getPath();
$paths = $request->getPaths();
$query = $request->getQuery();
$argsc = Config::get('ARGS_CONTROLLER');
$argsa = Config::get('ARGS_ACTION');

// blog_idの設定
if (isset($paths[0]) && preg_match('/^[0-9a-zA-Z]+$/', $paths[0])) {
  $request->set('blog_id', $paths[0]);
  $request->set($argsc, 'entries');
}

// トップページ
if (isset($paths[0]) && !$request->isArgs($argsa)) {
  $request->set($argsa, 'index');
}

// 記事詳細
if (strpos($query, 'no=')===0) {
  $request->set($argsa, 'view');
  $request->set('id', $request->get('no'));
}
if ($request->isArgs('no') && $request->isArgs('m2')) {
  $request->set($argsa, 'view');
  $request->set('id', $request->get('no'));
}

// プラグイン単体
if (strpos($query, 'mp=')===0) {
  $request->set($argsa, 'plugin');
  $request->set('id', $request->get('mp'));
}

// タグ
if (strpos($query, 'tag=')===0) {
  $request->set($argsa, 'tag');
}

// カテゴリー
if (strpos($query, 'cat=')===0) {
  $request->set($argsa, 'category');
}

// 検索
if (strpos($query, 'q=')===0) {
  $request->set($argsa, 'search');
}

// アーカイブ
if (isset($paths[1]) && strpos($paths[1], 'archives.html')===0) {
  $request->set($argsa, 'archive');
}

// 記事詳細
if (isset($paths[1]) && preg_match('/^blog-entry-([0-9]+)\.html$/', $paths[1], $matches)) {
  $request->set($argsa, 'view');
  $request->set('id', $matches[1]);
}

// サムネイル画像
if (preg_match('{/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/([0-9a-zA-Z]+)/file/([0-9]+)_([wh]?)([0-9]+)\.(png|gif|jpe?g)$}', $path, $matches)) {
  $request->set($argsc, 'common');
  $request->set($argsa, 'thumbnail');
  $request->set('blog_id', $matches[1]);
  $request->set('id', $matches[2]);
  $request->set('whs', $matches[3]);
  $request->set('size', $matches[4]);
  $request->set('ext', $matches[5]);
}

