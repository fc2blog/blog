<?php
/**
* 管理ページ用のルーティング
*/

// 管理用のパラメータを設定する
$request = Request::getInstance();
$paths = $request->getPaths();
$argsc = Config::get('ARGS_CONTROLLER');
$argsa = Config::get('ARGS_ACTION');

if (isset($paths[1])) {
  $request->set($argsc, $paths[1]);
}
if (isset($paths[2])) {
  $request->set($argsa, $paths[2]);
}

