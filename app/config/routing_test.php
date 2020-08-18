<?php
/**
 * テストページ用のルーティング
 */

// 管理用のパラメータを設定する
$request = \Fc2blog\Web\Request::getInstance();
$path = $request->getPath(); // full path with out query args.
$paths = $request->getPaths(); // explode with `/`
$query = $request->getQuery(); // query args
$args_controller = \Fc2blog\Config::get('ARGS_CONTROLLER');
$args_action = \Fc2blog\Config::get('ARGS_ACTION');

// argsa => method(action
// argsc => class

// アクションのクラスは当座固定
$request->set($args_controller, "common");

// → /_for_unit_test_/phpinfo
//     ^- path[0]      ^- path[1]
if ($paths[1] === "phpinfo") {
  $request->set($args_action, "phpinfo");

}else if($paths[1] === "redirect_test_no_full_url"){
  $request->set($args_action, "redirect_test_no_full_url");

}else if($paths[1] === "redirect_test_full_url"){
  $request->set($args_action, "redirect_test_full_url");

}
