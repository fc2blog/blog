<?php

require_once(Config::get('CONTROLLER_DIR') . 'user/user_controller.php');

class BlogsController extends UserController
{

  /**
   * ランダムなブログにリダイレクト
   * プラグインインストールでポータル画面化予定
   */
  public function index()
  {
    $blog = Model::load('Blogs')->findByRandom();
    if (empty($blog)) {
      return $this->error404();
    }
    $this->redirect(Config::get('BASE_DIRECTORY') . $blog['id'] . '/');
  }

}

