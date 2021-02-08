<?php
/**
 * リクエストクラス
 * POST,GETへのアクセスを便利にするクラス
 */

namespace Fc2blog\Web\Router;

use Fc2blog\Config;
use Fc2blog\Util\StringCaseConverter;
use Fc2blog\Web\Controller\Admin\AdminController;
use Fc2blog\Web\Controller\User\BlogsController;
use Fc2blog\Web\Controller\User\CommonController;
use Fc2blog\Web\Controller\User\EntriesController;
use Fc2blog\Web\Controller\User\UserController;
use Fc2blog\Web\Request;

class Router
{
  public $className = "";
  public $methodName = "";
  public $request;

  public function __construct(Request $request)
  {
    $this->request = $request;
    // TODO if文ベースのルーターから、なんらかのルーターに切り替えたい（Config全廃が前提）

    // favicon.ico アクセス時に404をレスポンスし、ブラウザにリトライさせない。
    // しない場合、404扱いからのブログページへリダイレクトが発生し、無駄な資源を消費する。
    // 可能なら、httpd側でハンドルしたほうが良いのだが、可搬性のため。
    if (preg_match('/\A\/favicon\.ico\z/u', $request->uri)) {
      $this->className = CommonController::class; // default controller.
      $this->methodName = 'error404';
      return;
    }

    if (preg_match('|\A/admin/|u', $request->uri)) { // Admin routing
      $request->urlRewrite = true;
      $request->baseDirectory = '/admin/';
      $this->className = \Fc2blog\Web\Controller\Admin\CommonController::class; // default controller.
      $this->methodName = 'index'; // default method.

      // 管理用のパラメータを設定する
      $paths = $request->getPaths();
      $args_controller = Config::get('ARGS_CONTROLLER');
      $args_action = Config::get('ARGS_ACTION');

      if ($request->isArgs($args_controller)) {
        $this->className = "Fc2blog\\Web\\Controller\\Admin\\" . StringCaseConverter::pascalCase($request->get($args_controller)) . "Controller";
      } elseif (isset($paths[1])) {
        $this->className = "Fc2blog\\Web\\Controller\\Admin\\" . StringCaseConverter::pascalCase($paths[1]) . "Controller";
      }

      if ($request->isArgs($args_action)) {
        $this->methodName = $request->get($args_action);
      } elseif (isset($paths[2])) {
        $this->methodName = $paths[2];
      }

    } else { // User Routing
      $request->urlRewrite = false;
      $request->baseDirectory = '/';
      $this->className = BlogsController::class; // default controller.

      // ユーザー用のパラメータを設定する
      $path = $request->getPath();
      $paths = $request->getPaths();
      $args_controller = "mode";
      $args_action = "process";

      if ($request->get($args_controller) == "common") {
        $this->className = CommonController::class;
      }

      // blog_idの設定
      if (isset($paths[0]) && preg_match('|\A[0-9a-zA-Z]+\z|u', $paths[0])) {
        $this->className = EntriesController::class;
        $request->set('blog_id', $paths[0]);
      }

      if ($request->isArgs('xml')) { // `/?xml`
        // RSS feed
        $this->className = BlogsController::class;
        $this->methodName = 'feed';

      } else if (isset($paths[0]) && !$request->isArgs($args_action)) {
        // トップページ
        $this->methodName = 'index';

        if ($request->rawHasGet('no') && $request->isGet()) {
          // 記事詳細
          $this->methodName = 'view';
          $request->set('id', $request->get('no'));

        } else if ($request->isArgs('no') && $request->isArgs('m2')) {
          $this->methodName = 'view';
          $request->set('id', $request->get('no'));

        } else if (isset($paths[1]) && strpos($paths[1], 'archives.html') === 0) {
          // アーカイブ
          $this->methodName = 'archive';

        } else if (isset($paths[1]) && preg_match('/^blog-entry-([0-9]+)\.html$/u', $paths[1], $matches)) {
          // 記事詳細
          $this->methodName = 'view';
          $request->set('id', $matches[1]);

        } else if ($request->rawHasGet('mp')) {
          // プラグイン単体
          $this->methodName = 'plugin';
          $request->set('id', $request->get('mp'));

        } else if ($request->rawHasGet('tag')) {
          // タグ
          $this->methodName = 'tag';

        } else if ($request->rawHasGet('cat')) {
          // カテゴリー
          $this->methodName = 'category';

        } else if ($request->rawHasGet('q')) {
          // 検索
          $this->methodName = 'search';
        }

      } else if (preg_match('{/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/([0-9a-zA-Z]+)/file/([0-9]+)_([wh]?)([0-9]+)\.(png|gif|jpe?g)$}', $path, $matches)) {
        // サムネイル画像
        $this->className = CommonController::class;
        $this->methodName = 'thumbnail';
        $request->set('blog_id', $matches[1]);
        $request->set('id', $matches[2]);
        $request->set('whs', $matches[3]);
        $request->set('size', $matches[4]);
        $request->set('ext', $matches[5]);

      } else if (preg_match('{/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/([0-9a-zA-Z]+)/file/([0-9]+)_(wh)([0-9]+)_([0-9]+)\.(png|gif|jpe?g)$}', $path, $matches)) {
        // サムネイル画像
        $this->className = CommonController::class;
        $this->methodName = 'thumbnail';
        $request->set('blog_id', $matches[1]);
        $request->set('id', $matches[2]);
        $request->set('whs', $matches[3]);
        $request->set('width', $matches[4]);
        $request->set('height', $matches[5]);
        $request->set('ext', $matches[6]);

      }else if ($this->methodName === "") {
        // 一つもかからなかった
        $this->methodName = $request->get($args_action);
      }
    }

    // アクセス拒否パターン（親クラス直呼び出し）
    // TODO パターンで表現されているが、Denyは構造で実現出来ているべきかと思われる
    if (
      (
        $this->className === UserController::class ||
        $this->className === AdminController::class
      ) ||
      (
        $this->methodName === 'process' ||
        $this->methodName === 'display' ||
        $this->methodName === 'fetch' ||
        $this->methodName === 'set'
      )
    ) {
      // 404に固定
      $this->className = CommonController::class; // default controller.
      $this->methodName = 'error404';
    }

    // 存在しないClassやMethodならFallbackさせる
    if (!class_exists($this->className) || !method_exists($this->className, $this->methodName)) {
      // 404に固定
      $this->className = CommonController::class; // default controller.
      $this->methodName = 'error404';
    }

    $request->className = $this->className;
    $request->methodName = $this->methodName;
  }

  public function resolve(): array
  {
    return [
      "request" => $this->request,
      "className" => $this->className,
      "methodName" => $this->methodName
    ];
  }
}
