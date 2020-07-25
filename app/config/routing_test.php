<?php
/**
 * テストページ用のルーティング
 */

// 管理用のパラメータを設定する
$request = Request::getInstance();
$path = $request->getPath(); // full path with out query args.
$paths = $request->getPaths(); // explode with `/`
$query = $request->getQuery(); // query args
$argsc = Config::get('ARGS_CONTROLLER');
$argsa = Config::get('ARGS_ACTION');

// argsa => method(action
// argsc => class

// アクションのクラスは当座固定
$request->set($argsc, "common");

// → /_for_unit_test_/phpinfo
//     ^- path[0]      ^- path[1]
if ($paths[1] === "phpinfo") {
  $request->set($argsa, "phpinfo");

}else if($paths[1] === "redirect_test_no_full_url"){
  $request->set($argsa, "redirect_test_no_full_url");

}else if($paths[1] === "redirect_test_full_url"){
  $request->set($argsa, "redirect_test_full_url");

}
