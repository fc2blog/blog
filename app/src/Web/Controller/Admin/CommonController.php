<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Exception;
use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogSettingsModel;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\CommentsModel;
use Fc2blog\Model\PDOConnection;
use Fc2blog\Model\PDOQuery;
use Fc2blog\Model\PluginsModel;
use Fc2blog\Model\UsersModel;
use Fc2blog\Web\Cookie;
use Fc2blog\Web\Request;
use PDO;

class CommonController extends AdminController
{
    /**
     * 言語設定変更
     * @param Request $request
     * @return string
     */
    public function lang(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // 言語の設定
        $lang = $request->get('lang');
        if (Config::get('LANGUAGES.' . $lang)) {
            Cookie::set($request, 'lang', $lang);
        }

        // TOPへ戻す
        $url = $request->baseDirectory;
        $device_name = App::getArgsDevice($request);
        if (!empty($device_name)) {
            $url .= '?' . $device_name;
        }
        $this->redirectBack($request, $url);
        return "";
    }

    /**
     * デバイス変更
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function device_change(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // デバイスの設定
        $device_type = 0;
        $device = $request->get('device');
        switch ($device) {
            case 'pc':
                $device_type = Config::get('DEVICE_PC');
                break;
            case 'sp':
                $device_type = Config::get('DEVICE_SP');
                break;
            default:
                Cookie::set($request, 'device', Config::get('DEVICE_PC'));
                $this->redirectBack($request, array('controller' => 'entries', 'action' => 'index'));
        }

        Cookie::set($request, 'device', $device_type);
        $this->redirectBack($request, array('controller' => 'entries', 'action' => 'index'));
        return "";
    }

    /**
     * /admin/ ブログの設定より初期表示ページを決定し、リダイレクト
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // 設定読み込みをしてリダイレクト
        if (is_string($blog_id = $this->getBlogIdFromSession())) {
            $blog_settings = new BlogSettingsModel();
            $setting = $blog_settings->findByBlogId($blog_id);
        } else {
            $setting = null;
        }
        if (is_array($setting) && isset($setting['start_page'])) { // 設定あり
            switch ($setting['start_page']) {
                case Config::get('BLOG.START_PAGE.ENTRY'):
                    $this->redirect($request, ['controller' => 'Entries', 'action' => 'create']);
                    return ""; // break;

                case Config::get('BLOG.START_PAGE.NOTICE'):
                default:
                    $this->redirect($request, ['controller' => 'Common', 'action' => 'notice']);
                    return ""; // break;
            }
        } else { // 設定なし
            $this->redirect($request, ['controller' => 'Common', 'action' => 'notice']);
            return "";
        }

    }

    /**
     * お知らせ一覧画面
     * @param Request $request
     * @return string
     */
    public function notice(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $this->getBlogIdFromSession();

        $comments_model = new CommentsModel();
        $this->set('unread_count', $comments_model->getUnreadCount($blog_id));
        $this->set('unapproved_count', $comments_model->getUnapprovedCount($blog_id));
        $this->set('reply_status_unread', Config::get('COMMENT.REPLY_STATUS.UNREAD'));
        $this->set('open_status_pending', Config::get('COMMENT.OPEN_STATUS.PENDING'));
        return "admin/common/notice.twig";
    }

    /**
     * インストール画面
     * @param Request $request
     * @return string
     */
    public function install(Request $request): string
    {
        $state = $request->get('state', 0);

        // インストール済みロックファイルをチェックする。ロックファイルがあればインストール済みと判定し、完了画面へ
        if ($this->isInstalled()) {
            $state = 3;
        }

        switch ($state) {
            default:
            case 0:
                if (!$request->isGet()) return $this->error400();
                // 環境チェック確認
                $this->set('temp_dir', Config::get('TEMP_DIR'));
                $this->set('www_upload_dir', Config::get('WWW_UPLOAD_DIR'));
                /** @noinspection PhpRedundantOptionalArgumentInspection */
                $this->set('random_string', App::genRandomStringAlphaNum(32));

                $this->set('DB_HOST', DB_HOST);
                $this->set('DB_PORT', DB_PORT);
                $this->set('DB_USER', DB_USER);
                $this->set('DB_PASSWORD', DB_PASSWORD);
                $this->set('DB_DATABASE', DB_DATABASE);

                // ディレクトリ書き込みパーミッション確認
                $is_write_temp = is_writable(Config::get('TEMP_DIR') . '.');
                $this->set('is_write_temp', $is_write_temp);
                $is_write_upload = is_writable(Config::get('WWW_UPLOAD_DIR') . '.');
                $this->set('is_write_upload', $is_write_upload);

                // DB疎通確認
                if (class_exists(PDO::class)) {
                    $is_connect = true;
                    $connect_message = '';
                    try {
                        PDOConnection::createConnection();
                    } catch (Exception $e) {
                        $is_connect = false;
                        $connect_message = $e->getMessage();
                    }
                } else {
                    $is_connect = false;
                    $connect_message = __("Please enable PDO");
                }
                $this->set('is_connect', $is_connect);
                $this->set('connect_message', $connect_message);

                // ドメイン確認
                $is_domain = DOMAIN != 'domain';
                $this->set('is_domain', $is_domain);
                $this->set('example_server_name', $request->server['SERVER_NAME'] ?? 'example.jp');

                // GDインストール済み確認
                $is_gd = function_exists('gd_info');
                $this->set('is_gd', $is_gd);

                $is_all_ok = $is_write_temp && $is_write_upload && $is_connect && $is_domain;
                $this->set('is_all_ok', $is_all_ok);

                return 'admin/common/install.twig';

            case 1:
                if (!$request->isGet()) return $this->error400();
                // 各種初期設定、DB テーブル作成、ディレクトリ作成

                // フォルダの作成
                !file_exists(Config::get('TEMP_DIR') . 'blog_template') && mkdir(Config::get('TEMP_DIR') . 'blog_template', 0777, true);
                !file_exists(Config::get('TEMP_DIR') . 'log') && mkdir(Config::get('TEMP_DIR') . 'log', 0777, true);

                // ディレクトリ製作成功チェック
                if (!file_exists(Config::get('TEMP_DIR') . 'log') || !file_exists(Config::get('TEMP_DIR') . 'blog_template')) {
                    $this->setErrorMessage(__('Create /app/temp/blog_template and log directory failed.'));
                    $this->redirect($request, $request->baseDirectory . 'common/install?state=0&error=mkdir');
                }

                // DB接続確認
                try {
                    // DB接続確認(DATABASEの存在判定含む)
                    $pdo = PDOConnection::createConnection();
                } catch (Exception $e) {
                    $this->setErrorMessage(__('Please set correct the DB connection settings.'));
                    $this->redirect($request, $request->baseDirectory . 'common/install?state=0&error=db_create');
                    return "";
                }

                // テーブルの存在チェック
                $sql = "SHOW TABLES LIKE 'users'";
                $table = PDOQuery::find($pdo, $sql);

                if (is_countable($table) && count($table)) {
                    // 既にDB登録完了
                    $this->redirect($request, $request->baseDirectory . 'common/install?state=2');
                }

                // DBセットアップ
                $sql_path = Config::get('APP_DIR') . 'db/0_initialize.sql';
                $sql = file_get_contents($sql_path);
                if (DB_CHARSET != 'UTF8MB4') {
                    $sql = str_replace('utf8mb4', strtolower(DB_CHARSET), $sql);
                }
                $res = PDOQuery::multiExecute($pdo, $sql);
                if ($res === false) {
                    $this->setErrorMessage(__('Create' . ' table failed.'));
                    $this->redirect($request, $request->baseDirectory . 'common/install?state=0&error=table_insert');
                }

                // DBセットアップ成功チェック
                $sql = "SHOW TABLES LIKE 'users'";
                $table = PDOQuery::find($pdo, $sql);
                if (!is_countable($table)) {
                    $this->setErrorMessage(__('Create' . ' table failed.'));
                    $this->redirect($request, $request->baseDirectory . 'common/install?state=0&error=table_insert');
                }

                // 初期公式プラグインを追加
                $plugins_model = new PluginsModel();
                $plugins_model->addInitialOfficialPlugin();

                $this->redirect($request, $request->baseDirectory . 'common/install?state=2');
                return 'admin/common/install_user.twig';

            case 2:  // 管理者登録
                // TODO いきなりstate=2を指定されたとき、state=1に戻す仕組みがない（とは言え無害？）

                $users = new UsersModel();
                if ($users->isExistAdmin()) {
                    // 既に管理者ユーザー登録完了済み
                    $this->redirect($request, $request->baseDirectory . 'common/install?state=3');
                }

                // ユーザー登録画面を表示
                if (!$request->get('user')) {
                    return 'admin/common/install_user.twig';
                }

                // 以下はユーザー登録実行
                if (!$request->isPost()) return $this->error400();
                $users_model = new UsersModel();
                $blogs_model = new BlogsModel();

                // ユーザーとブログの新規登録処理
                $errors = [];
                $errors['user'] = $users_model->registerValidate($request->get('user'), $user_data, array('login_id', 'password'));
                $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, array('id', 'name', 'nickname'));
                if (empty($errors['user']) && empty($errors['blog'])) {
                    $user_data['type'] = Config::get('USER.TYPE.ADMIN');
                    $user_id = $users_model->insert($user_data);
                    $blog_data['user_id'] = $user_id;
                    if ($blog_data['user_id'] && $blog_id = $blogs_model->insert($blog_data)) {
                        // userのlogin_blog_idを更新
                        $user_data['login_blog_id'] = $blog_id;
                        $users_model->updateById($user_data, $user_id);

                        // 成功したので完了画面へリダイレクト
                        $this->setInfoMessage(__('User registration is completed'));
                        $this->redirect($request, $request->baseDirectory . 'common/install?state=3'); // 成功終了

                    } else {
                        // ブログ作成失敗時には登録したユーザーを削除（ロールバックの代用）
                        $users_model->deleteById($blog_data['user_id']);

                    }
                    $this->setErrorMessage(__('I failed to register'));
                    return 'admin/common/install_user.twig';
                }

                // エラー情報の設定
                $this->setErrorMessage(__('Input error exists'));
                $this->set('errors', $errors);

                return 'admin/common/install_user.twig'; // 失敗描画

            case 3:
                // 完了画面
                if (!$request->isGet()) return $this->error400();

                // 完了画面表示と同時に、インストール済みロックファイルの生成
                file_put_contents($this->getInstalledLockFilePath(), "This is installed check lockfile.\nThe blog already installed. if you want re-enable installer, please delete this file.");

                return 'admin/common/installed.twig';
        }
    }
}
