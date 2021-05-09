<?php

namespace Fc2blog\Web\Router;

use Fc2blog\Model\BlogsModel;
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
        // TODO if文ベースのルーターから、なんらかのルーターに切り替えたい

        // favicon.ico アクセス時に404をレスポンスし、ブラウザにリトライさせない。
        // しない場合、404扱いからのブログページへリダイレクトが発生し、無駄な資源を消費する。
        // 可能なら、httpd側でハンドルしたほうが良いのだが、可搬性のため。
        if (preg_match('/\A\/favicon\.ico\z/u', $request->uri)) {
            $this->className = CommonController::class; // default controller.
            $this->methodName = 'error404';
            return;
        }

        // /assets, /admin/assets へのアクセス（つまりファイルが無い）時、不備に気づきやすくする為に404をレスポンス。
        if (preg_match('!\A(/assets|/admin/assets)!u', $request->uri)) {
            $this->className = CommonController::class; // default controller.
            $this->methodName = 'error404';
            return;
        }

        // ユーザー用のパラメータを設定する
        $path = $request->getPath();
        $paths = $request->getPaths();
        $args_controller = "mode";
        $args_action = "process";

        if (preg_match('!\A(/admin\z|/admin/)!u', $request->uri)) { // Admin routing
            // http://example.jp/admin/* が対応
            $request->urlRewrite = true;
            $request->baseDirectory = '/admin/';
            $this->className = \Fc2blog\Web\Controller\Admin\CommonController::class; // default controller.
            $this->methodName = 'index'; // default method.

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

            // 対象となるblogを決定する
            if (!is_null($request->getBlogId())) {
                // URLからDefault Blog IDが取得できる場合
                $blog_id = $request->getBlogId();
                $request->set('blog_id', $blog_id);
                if (isset($paths[1])) {
                    $sub_path = $paths[1];
                }
            } else if (!is_null($request->get('blog_id'))) {
                // パラメタからBlog idが取得できる場合
                $blog_id = $request->get('blog_id');
                $request->set('blog_id', $blog_id);
                if (isset($paths[0])) {
                    $sub_path = $paths[0];
                }
            } else {
                // blog_idがリクエストから特定できない場合、デフォルトblog_idを利用する
                $blog_id = BlogsModel::getDefaultBlogId();
                $request->set('blog_id', $blog_id);
                if (isset($paths[0])) {
                    $sub_path = $paths[0];
                }
            }

            if (isset($blog_id) && $request->isArgs('xml')) { // `/?xml`
                // http://example.jp/blogid/*?xml が対応
                // RSS feed
                $this->className = BlogsController::class;
                $this->methodName = 'feed';

            } else if (preg_match('!\A/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/([0-9a-zA-Z]+)/file/([0-9]+)_([wh]?)([0-9]{1,4})\.(png|gif|jpeg|jpg)\z!u', $path, $matches)) {
                // サムネイル画像 (入力値制限あり
                // http://localhost:8080/uploads/t/e/s/testblog2/file/2_72.png?
                // http://localhost:8080/uploads/t/e/s/testblog2/file/2_w300.png?
                // http://localhost:8080/uploads/t/e/s/testblog2/file/2_w400.png?
                // http://localhost:8080/uploads/t/e/s/testblog2/file/2_w600.png?
                $this->className = CommonController::class;
                $this->methodName = 'thumbnail';
                $request->set('blog_id', $matches[1]);
                $request->set('id', $matches[2]);
                $request->set('whs', $matches[3]);
                $request->set('size', $matches[4]);
                $request->set('ext', $matches[5]);

            } else if (preg_match('!\A/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/([0-9a-zA-Z]+)/file/([0-9]+)_(wh)([0-9]+)_([0-9]+)\.(png|gif|jpe?g)\z!u', $path, $matches)) {
                // サムネイル画像 (入力値制限あり
                // http://localhost:8080/uploads/t/e/s/testblog2/file/2_wh760_420.png
                $this->className = CommonController::class;
                $this->methodName = 'thumbnail';
                $request->set('blog_id', $matches[1]);
                $request->set('id', $matches[2]);
                $request->set('whs', $matches[3]);
                $request->set('width', $matches[4]);
                $request->set('height', $matches[5]);
                $request->set('ext', $matches[6]);

            } else if (isset($blog_id) && !$request->isArgs($args_action)) {
                $this->className = EntriesController::class;

                if (isset($sub_path) && strpos($sub_path, 'archives.html') === 0) {
                    // http://example.jp(/blogid)?/archives.html が対応
                    // アーカイブ
                    $this->methodName = 'archive';

                } else if ($request->rawHasGet('no') && $request->isGet()) {
                    // http://example.jp(/blogid)?/*?no=XX が対応
                    // 記事詳細
                    $this->methodName = 'view';
                    $request->set('id', $request->get('no'));

                } else if ($request->isArgs('no') && $request->isArgs('m2')) {
                    // http://example.jp(/blogid)?/*?m2=XX&no=XX が対応
                    // モバイル用 記事詳細（フォーム表示など
                    $this->methodName = 'view';
                    $request->set('id', $request->get('no'));

                } else if (isset($sub_path) && preg_match('/^blog-entry-([0-9]+)\.html$/u', $sub_path, $matches)) {
                    // http://example.jp(/blogid)?/blog-entry-123.html(.*) が対応
                    // 記事詳細
                    $this->methodName = 'view';
                    $request->set('id', $matches[1]);

                } else if ($request->rawHasGet('mp')) {
                    // http://example.jp(/blogid)?/*?mp=XX が対応
                    // プラグイン単体
                    $this->methodName = 'plugin';
                    $request->set('id', $request->get('mp'));

                } else if ($request->rawHasGet('tag')) {
                    // http://example.jp(/blogid)?/*?tag=XX が対応
                    // タグ
                    $this->methodName = 'tag';

                } else if ($request->rawHasGet('cat')) {
                    // http://example.jp(/blogid)?/*?cat=XX が対応
                    // カテゴリー
                    $this->methodName = 'category';

                } else if ($request->rawHasGet('q')) {
                    // http://example.jp(/blogid)?/*?q=XX が対応
                    // 検索
                    $this->methodName = 'search';

                } else {
                    // ここまでの条件にかからないものが対応
                    // トップページ
                    $this->methodName = 'index';

                }

            } else if ($request->get($args_controller) === "common" && $request->get($args_action) === "captcha") {
                // http://example.jp/(index.php)?mode=common&process=captcha が対応
                // captcha画像生成
                $this->className = CommonController::class;
                $this->methodName = 'captcha';

            } else if ($request->get($args_controller) === "common" && $request->get($args_action) === "device_change") {
                // http://example.jp/(index.php)?mode=common&process=device_change が対応
                // 機種切り替え、つかわれているケースがあるか不明
                $this->className = CommonController::class;
                $this->methodName = 'device_change';

            } else if ($request->get($args_controller) === "common" && $request->get($args_action) === "lang") {
                // http://example.jp/(index.php)?mode=common&process=lang が対応
                // 言語切り替え
                $this->className = CommonController::class;
                $this->methodName = 'lang';

            } else if ($request->get($args_controller) === "blogs" && $request->get($args_action) === "index") {
                // http://example.jp/(index.php)?mode=blogs&process=index が対応
                $this->className = BlogsController::class;
                $this->methodName = 'index';

            } else if (!isset($blog_id)) {
                // ここまでかからず、なおかつblog_idが判定できなかった場合
                $this->className = BlogsController::class;
                $this->methodName = 'index';

            } else if ($this->className === "") {
                // Class(mode)がargsで指定がなく、ここまでに確定しなければEntriesController
                // このrouteに明記はないが、EntriesController::* を指定するルートが存在する
                $this->className = EntriesController::class;
            }

            // ここまでかからずmethodが不定の場合、process引数から決定
            // このrouteに明記はないが、EntriesController::* を指定するルートが存在する
            if ($this->methodName === "") {
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
