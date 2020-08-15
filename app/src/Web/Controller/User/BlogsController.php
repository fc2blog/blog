<?php

namespace Fc2blog\Web\Controller\User;

use Fc2blog\Config;
use Fc2blog\Model\Model;

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

