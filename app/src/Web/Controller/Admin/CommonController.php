<?php

namespace Fc2blog\Web\Controller\Admin;

use Exception;
use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\Model;
use Fc2blog\Model\MSDB;
use Fc2blog\Model\UsersModel;
use Fc2blog\Web\Cookie;
use Fc2blog\Web\Request;

class CommonController extends AdminController
{

  /**
  * 言語設定変更
  */
  public function lang(Request $request)
  {
    // 言語の設定
    $lang = $request->get('lang');
    if ($language= Config::get('LANGUAGES.' . $lang)) {
      Cookie::set('lang', $lang);
    }

    // TOPへ戻す
    $url = Config::get('BASE_DIRECTORY');
    $device_name = App::getArgsDevice($request);
    if (!empty($device_name)) {
      $url .= '?' . $device_name;
    }
    $this->redirectBack($request, $url);
  }

  /**
  * デバイス変更
  */
  public function device_change(Request $request)
  {
    // デバイスの設定
    $device_type = 0;
    $device = $request->get('device');
    switch ($device) {
      case 'pc': $device_type = Config::get('DEVICE_PC'); break;
      case 'sp': $device_type = Config::get('DEVICE_SP'); break;
      default:
        Cookie::set('device', null);
        $this->redirectBack($request, array('controller'=>'entries', 'action'=>'index'));
    }

    Cookie::set('device', $device_type);
    $this->redirectBack($request, array('controller'=>'entries', 'action'=>'index'));
  }

  /**
   * 初期表示ページ(ブログの設定よりリダイレクト)
   * @param Request $request
   */
  public function initial(Request $request)
  {
    $setting = Model::load('BlogSettings')->findByBlogId($this->getBlogId($request));
    if (is_array($setting)) {
      switch ($setting['start_page']) {
        default:
        case Config::get('BLOG.START_PAGE.NOTICE'):
          $this->redirect($request, array('controller' => 'Common', 'action' => 'notice'));
          break;

        case Config::get('BLOG.START_PAGE.ENTRY'):
          $this->redirect($request, array('controller' => 'Entries', 'action' => 'create'));
          break;
      }
    } else {
      $this->redirect($request, array('controller' => 'Common', 'action' => 'notice'));
    }
  }

  public function index(Request $request)
  {
    return $this->initial($request);
  }

  /**
  * お知らせ一覧画面
  */
  public function notice(Request $request)
  {
    $blog_id = $this->getBlogId($request);

    $comments_model = Model::load('Comments');
    $this->set('unread_count', $comments_model->getUnreadCount($blog_id));
    $this->set('unapproved_count', $comments_model->getUnapprovedCount($blog_id));
  }

  /**
  * インストール画面
  */
  public function install(Request $request)
  {
    $this->layout = 'default_nomenu.php';

    $state = $request->get('state', 0);

    // インストール済みロックファイルをチェックする。ロックファイルがあればインストール済みと判定し、完了画面へ
    $installed_lock_file_path = Config::get('TEMP_DIR') . "installed.lock";
    if (file_exists($installed_lock_file_path)) {
      $state = 3;
    }

    switch ($state) {
      default: case 0:
        // 環境チェック確認
        return ;

      case 1:
        // フォルダの作成
        !file_exists(Config::get('TEMP_DIR') . 'blog_template') && mkdir(Config::get('TEMP_DIR') . 'blog_template', 0777, true);
        !file_exists(Config::get('TEMP_DIR') . 'debug_html') && mkdir(Config::get('TEMP_DIR') . 'debug_html', 0777, true);
        !file_exists(Config::get('TEMP_DIR') . 'log') && mkdir(Config::get('TEMP_DIR') . 'log', 0777, true);

        $msdb = MSDB::getInstance();
        try {
          // DB接続確認(DATABASEの存在判定含む)
          $msdb->connect();
        } catch (Exception $e) {
          // データベースの作成
          $msdb->close();
          $msdb->connect(false, false);
          $sql = 'CREATE DATABASE IF NOT EXISTS ' . DB_DATABASE . ' CHARACTER SET ' . DB_CHARSET;
          $table = $msdb->execute($sql);
          $msdb->close();
        }

        // テーブルの存在チェック
        $sql = "SHOW TABLES LIKE 'users'";
        $table = MSDB::getInstance()->find($sql);

        if (count($table)) {
          // 既にDB登録完了
          $this->redirect($request, Config::get('BASE_DIRECTORY') . 'install.php?state=2');
        }
        $sql_path = Config::get('APP_DIR') . 'db/0_initialize.sql';
        $sql = file_get_contents($sql_path);
        if (DB_CHARSET!='UTF8MB4') {
          $sql = str_replace('utf8mb4', strtolower(DB_CHARSET), $sql);
        }

        MSDB::getInstance()->multiExecute($sql);

        // 初期公式プラグインを追加
        Model::load('Plugins')->addInitialOfficialPlugin();

        $this->redirect($request, Config::get('BASE_DIRECTORY') . 'install.php?state=2');

      case 2:  // 管理者登録
        if (Model::load('Users')->isExistAdmin()) {
          // 既にユーザー登録完了
          $this->redirect($request, Config::get('BASE_DIRECTORY') . 'install.php?state=3');
        }

        break;

      case 3:
        // 完了画面

        // 完了画面表示と同時に、インストール済みロックファイルの生成
        file_put_contents($installed_lock_file_path, "This is installed check lockfile.\nThe blog already installed. if you want re-enable installer, please delete this file.");

        return 'common/installed.php';
    }

    // 初期表示時
    if (!$request->get('user')) {
      return 'common/install_user.php';
    }

    /** @var UsersModel $users_model */
    $users_model = Model::load('Users');
    /** @var BlogsModel $blogs_model */
    $blogs_model = Model::load('Blogs');

    // ユーザーとブログの新規登録処理
    $errors = array();
    $errors['user'] = $users_model->registerValidate($request->get('user'), $user_data, array('login_id', 'password'));
    $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, array('id', 'name', 'nickname'));
    if (empty($errors['user']) && empty($errors['blog'])) {
      $user_data['type'] = Config::get('USER.TYPE.ADMIN');
      $blog_data['user_id'] = $users_model->insert($user_data);
      if ($blog_data['user_id'] && $blog_id=$blogs_model->insert($blog_data)) {
        $this->setInfoMessage(__('User registration is completed'));
        $this->redirect($request, Config::get('BASE_DIRECTORY') . 'install.php?state=3');
      } else {
        // ブログ作成失敗時には登録したユーザーを削除
        $users_model->deleteById($blog_data['user_id']);
      }
      $this->setErrorMessage(__('I failed to register'));
      return 'common/install_user.php';
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);

    return 'common/install_user.php';
  }

}

