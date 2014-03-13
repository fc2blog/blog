<?php

require_once(Config::get('CONTROLLER_DIR') . 'admin/admin_controller.php');

class CommonController extends AdminController
{

  /**
  * 言語設定変更
  */
  public function lang()
  {
    $request = Request::getInstance();

    // 言語の設定
    $lang = $request->get('lang');
    if ($language=Config::get('LANGUAGES.' . $lang)) {
      Cookie::set('lang', $lang);
    }

    // TOPへ戻す
    $url = Config::get('BASE_DIRECTORY');
    $device_name = App::getArgsDevice();
    if (!empty($device_name)) {
      $url .= '?' . $device_name;
    }
    $this->redirectBack($url);
  }

  /**
  * デバイス変更
  */
  public function device_change()
  {
    $request = Request::getInstance();

    // デバイスの設定
    $device_type = 0;
    $device = $request->get('device');
    switch ($device) {
      case 'pc': $device_type = Config::get('DEVICE_PC'); break;
      case 'm':
      case 'mb':  $device_type = Config::get('DEVICE_MB'); break;
      case 'sp': $device_type = Config::get('DEVICE_SP'); break;
      case 'tb': $device_type = Config::get('DEVICE_TB'); break;
      default:
        Cookie::set('device', null);
        $this->redirectBack(array('controller'=>'entries', 'action'=>'index'));
    }

    Cookie::set('device', $device_type);
    $this->redirectBack(array('controller'=>'entries', 'action'=>'index'));
  }

  /**
  * 初期表示ページ(ブログの設定よりリダイレクト)
  */
  public function initial()
  {
    $setting = Model::load('BlogSettings')->findByBlogId($this->getBlogId());
    switch($setting['start_page']){
      default:
      case Config::get('BLOG.START_PAGE.NOTICE'):
        $this->redirect(array('controller'=>'Common', 'action'=>'notice'));
        break ;

      case Config::get('BLOG.START_PAGE.ENTRY'):
        $this->redirect(array('controller'=>'Entries', 'action'=>'create'));
        break ;
    }
  }

  public function index()
  {
    return $this->initial();
  }

  /**
  * お知らせ一覧画面
  */
  public function notice()
  {
    $blog_id = $this->getBlogId();

    $comments_model = Model::load('Comments');
    $this->set('unread_count', $comments_model->getUnreadCount($blog_id));
    $this->set('unapproved_count', $comments_model->getUnapprovedCount($blog_id));
  }

  /**
  * インストール画面
  */
  public function install()
  {
    $this->layout = 'default_nomenu.html';

    $request = Request::getInstance();

    $state = $request->get('state', 0);
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
          $this->redirect(Config::get('BASE_DIRECTORY') . 'install.php?state=2');
        }
        $sql_path = Config::get('CONFIG_DIR') . 'blog.sql';
        $sql = file_get_contents($sql_path);
        if (DB_CHARSET!='UTF8MB4') {
          $sql = str_replace('utf8mb4', strtolower(DB_CHARSET), $sql);
        }

        MSDB::getInstance()->multiExecute($sql);

        // 初期公式プラグインを追加
        Model::load('Plugins')->addInitialOfficialPlugin();

        $this->redirect(Config::get('BASE_DIRECTORY') . 'install.php?state=2');

      case 2:  // 管理者登録
        if (Model::load('Users')->isExistAdmin()) {
          // 既にユーザー登録完了
          $this->redirect(Config::get('BASE_DIRECTORY') . 'install.php?state=3');
        }

        break;

      case 3:
        // 完了画面
        return 'common/installed.html';
    }

    // 初期表示時
    if (!$request->get('user')) {
      return 'common/install_user.html';
    }

    $users_model = Model::load('Users');
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
        $this->redirect(Config::get('BASE_DIRECTORY') . 'install.php?state=3');
      } else {
        // ブログ作成失敗時には登録したユーザーを削除
        $users_model->deleteById($blog_data['user_id']);
      }
      $this->setErrorMessage(__('I failed to register'));
      return 'common/install_user.html';
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);

    return 'common/install_user.html';
  }

}

