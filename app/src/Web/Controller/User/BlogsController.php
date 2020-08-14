<?php

namespace Fc2blog\Web\Controller\User;

class BlogsController extends UserController
{

  /**
   * ランダムなブログにリダイレクト
   * プラグインインストールでポータル画面化予定
   */
  public function index()
  {
    $blog = \Fc2blog\Model\Model::load('Blogs')->findByRandom();
    if (empty($blog)) {
      return $this->error404();
    }
    $this->redirect(\Fc2blog\Config::get('BASE_DIRECTORY') . $blog['id'] . '/');
  }

}

