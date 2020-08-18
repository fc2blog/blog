<?php
/**
 * リクエストクラス
 * POST,GETへのアクセスを便利にするクラス
 */

namespace Fc2blog\Web\Router;

use Fc2blog\Config;
use Fc2blog\Web\Controller\User\BlogsController;
use Fc2blog\Web\Controller\User\CommonController;
use Fc2blog\Web\Controller\User\EntriesController;
use Fc2blog\Web\Request;

class Router
{
  public $className = "";
  public $methodName = "";

  public function __construct(Request $request)
  {
    // favicon.ico アクセス時に404をレスポンスし、ブラウザにリトライさせない。
    // しない場合、404扱いからのブログページへリダイレクトが発生し、無駄な資源を消費する。
    // 可能なら、httpd側でハンドルしたほうが良いのだが、可搬性のため。
    if (preg_match('/\Afavicon\.ico/u', $request->uri)) {
      return null;
    }

    if (preg_match('|\A/admin/|u', $request->uri)) {
      // 管理用のパラメータを設定する
      $paths = $request->getPaths();
      $args_controller = \Fc2blog\Config::get('ARGS_CONTROLLER');
      $args_action = \Fc2blog\Config::get('ARGS_ACTION');

      if ($request->isArgs($args_controller)) {
        $request->set($args_controller, $request->get($args_controller));
      } elseif (isset($paths[1])) {
        $request->set($args_controller, $paths[1]);
      }

      if ($request->isArgs($args_action)) {
        $request->set($args_action, $request->get($args_action));
      } elseif (isset($paths[2])) {
        $request->set($args_action, $paths[2]);
      }

    } elseif (preg_match('|\A/_for_unit_test_/|u', $request->uri)) {
      // 管理用のパラメータを設定する
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

    } else {
      //user
      Config::set('APP_PREFIX', 'User');
      $this->className = BlogsController::class; // default controller.

      // ユーザー用のパラメータを設定する
      $path = $request->getPath();
      $paths = $request->getPaths();
      $query = $request->getQuery();
      $args_controller = "mode";
      $args_action = "process";

      if($request->get($args_controller)=="common"){
        $this->className = CommonController::class;
      }


      // blog_idの設定
      if (isset($paths[0]) && preg_match('|\A[0-9a-zA-Z]+\z|u', $paths[0])) {
        $this->className = EntriesController::class;
        $request->set('blog_id', $paths[0]);
      }

      // トップページ
      if (isset($paths[0]) && !$request->isArgs($args_action)) {
        $this->methodName = 'index';
      }

      // 記事詳細
      if ($request->rawHasGet('no') && $request->isGet()) {
        $this->methodName =  'view';
        $request->set('id', $request->get('no'));
      }
      if ($request->isArgs('no') && $request->isArgs('m2')) {
        $this->methodName =  'view';
        $request->set('id', $request->get('no'));
        return;
      }

      // プラグイン単体
      if ($request->rawHasGet('mp')) {
        $this->methodName =  'plugin';
        $request->set('id', $request->get('mp'));
      }

      // タグ
      if ($request->rawHasGet('tag')) {
        $this->methodName = 'tag';
      }

      // カテゴリー
      if ($request->rawHasGet('cat')) {
        $this->methodName = 'category';
      }

      // 検索
      if ($request->rawHasGet('q')) {
        $this->methodName = 'search';
      }

      // アーカイブ
      if (isset($paths[1]) && strpos($paths[1], 'archives.html') === 0) {
        $this->methodName = 'archive';
      }

      // 記事詳細
      if (isset($paths[1]) && preg_match('/^blog-entry-([0-9]+)\.html$/u', $paths[1], $matches)) {
        $this->methodName =  'view';
        $request->set('id', $matches[1]);
      }

      // サムネイル画像
      if (preg_match('{/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/([0-9a-zA-Z]+)/file/([0-9]+)_([wh]?)([0-9]+)\.(png|gif|jpe?g)$}', $path, $matches)) {
        $this->className = CommonController::class;
        $this->methodName = 'thumbnail';
        $request->set('blog_id', $matches[1]);
        $request->set('id', $matches[2]);
        $request->set('whs', $matches[3]);
        $request->set('size', $matches[4]);
        $request->set('ext', $matches[5]);
      }
      if (preg_match('{/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/([0-9a-zA-Z]+)/file/([0-9]+)_(wh)([0-9]+)_([0-9]+)\.(png|gif|jpe?g)$}', $path, $matches)) {
        $this->className = CommonController::class;
        $this->methodName = 'thumbnail';
        $request->set('blog_id', $matches[1]);
        $request->set('id', $matches[2]);
        $request->set('whs', $matches[3]);
        $request->set('width', $matches[4]);
        $request->set('height', $matches[5]);
        $request->set('ext', $matches[6]);
      }

      if($this->methodName === ""){
        // 一つもかからなかったので
        $this->methodName = $request->get($args_action);
      }

    }

  }

  public function resolve():array
  {
    return [
      "className"=>$this->className,
      "methodName"=>$this->methodName
    ];
  }
}
