<?php

namespace Fc2blog\Web\Controller\Admin;

class CommonController extends AdminController
{

  /**
  * 言語設定変更
  */
  public function lang()
  {
    $request = \Fc2blog\Request::getInstance();

    // 言語の設定
    $lang = $request->get('lang');
    if ($language=\Fc2blog\Config::get('LANGUAGES.' . $lang)) {
      \Fc2blog\Web\Cookie::set('lang', $lang);
    }

    // TOPへ戻す
    $url = \Fc2blog\Config::get('BASE_DIRECTORY');
    $device_name = \Fc2blog\App::getArgsDevice();
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
    $request = \Fc2blog\Request::getInstance();

    // デバイスの設定
    $device_type = 0;
    $device = $request->get('device');
    switch ($device) {
      case 'pc': $device_type = \Fc2blog\Config::get('DEVICE_PC'); break;
      case 'm':
      case 'mb':  $device_type = \Fc2blog\Config::get('DEVICE_MB'); break;
      case 'sp': $device_type = \Fc2blog\Config::get('DEVICE_SP'); break;
      case 'tb': $device_type = \Fc2blog\Config::get('DEVICE_TB'); break;
      default:
        \Fc2blog\Web\Cookie::set('device', null);
        $this->redirectBack(array('controller'=>'entries', 'action'=>'index'));
    }

    \Fc2blog\Web\Cookie::set('device', $device_type);
    $this->redirectBack(array('controller'=>'entries', 'action'=>'index'));
  }

  /**
  * 初期表示ページ(ブログの設定よりリダイレクト)
  */
  public function initial()
  {
    $setting = \Fc2blog\Model\Model::load('BlogSettings')->findByBlogId($this->getBlogId());
    if (is_array($setting)) {
      switch ($setting['start_page']) {
        default:
        case \Fc2blog\Config::get('BLOG.START_PAGE.NOTICE'):
          $this->redirect(array('controller' => 'Common', 'action' => 'notice'));
          break;

        case \Fc2blog\Config::get('BLOG.START_PAGE.ENTRY'):
          $this->redirect(array('controller' => 'Entries', 'action' => 'create'));
          break;
      }
    } else {
      $this->redirect(array('controller' => 'Common', 'action' => 'notice'));
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

    $comments_model = \Fc2blog\Model\Model::load('Comments');
    $this->set('unread_count', $comments_model->getUnreadCount($blog_id));
    $this->set('unapproved_count', $comments_model->getUnapprovedCount($blog_id));
  }

  /**
  * インストール画面
  */
  public function install()
  {
    $this->layout = 'default_nomenu.html';

    $request = \Fc2blog\Request::getInstance();

    $state = $request->get('state', 0);
    switch ($state) {
      default: case 0:
        // 環境チェック確認
        return ;

      case 1:
        // フォルダの作成
        !file_exists(\Fc2blog\Config::get('TEMP_DIR') . 'blog_template') && mkdir(\Fc2blog\Config::get('TEMP_DIR') . 'blog_template', 0777, true);
        !file_exists(\Fc2blog\Config::get('TEMP_DIR') . 'debug_html') && mkdir(\Fc2blog\Config::get('TEMP_DIR') . 'debug_html', 0777, true);
        !file_exists(\Fc2blog\Config::get('TEMP_DIR') . 'log') && mkdir(\Fc2blog\Config::get('TEMP_DIR') . 'log', 0777, true);

        $msdb = \Fc2blog\Model\MSDB::getInstance();
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
        $table = \Fc2blog\Model\MSDB::getInstance()->find($sql);

        if (count($table)) {
          // 既にDB登録完了
          $this->redirect(\Fc2blog\Config::get('BASE_DIRECTORY') . 'install.php?state=2');
        }
        $sql_path = \Fc2blog\Config::get('CONFIG_DIR') . 'blog.sql';
        $sql = file_get_contents($sql_path);
        if (DB_CHARSET!='UTF8MB4') {
          $sql = str_replace('utf8mb4', strtolower(DB_CHARSET), $sql);
        }

        \Fc2blog\Model\MSDB::getInstance()->multiExecute($sql);

        // 初期公式プラグインを追加
        \Fc2blog\Model\Model::load('Plugins')->addInitialOfficialPlugin();

        $this->redirect(\Fc2blog\Config::get('BASE_DIRECTORY') . 'install.php?state=2');

      case 2:  // 管理者登録
        if (\Fc2blog\Model\Model::load('Users')->isExistAdmin()) {
          // 既にユーザー登録完了
          $this->redirect(\Fc2blog\Config::get('BASE_DIRECTORY') . 'install.php?state=3');
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

    $users_model = \Fc2blog\Model\Model::load('Users');
    $blogs_model = \Fc2blog\Model\Model::load('Blogs');

    // ユーザーとブログの新規登録処理
    $errors = array();
    $errors['user'] = $users_model->registerValidate($request->get('user'), $user_data, array('login_id', 'password'));
    $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, array('id', 'name', 'nickname'));
    if (empty($errors['user']) && empty($errors['blog'])) {
      $user_data['type'] = \Fc2blog\Config::get('USER.TYPE.ADMIN');
      $blog_data['user_id'] = $users_model->insert($user_data);
      if ($blog_data['user_id'] && $blog_id=$blogs_model->insert($blog_data)) {
        $this->setInfoMessage(__('User registration is completed'));
        $this->redirect(\Fc2blog\Config::get('BASE_DIRECTORY') . 'install.php?state=3');
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

