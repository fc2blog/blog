<?php

namespace Fc2blog\Web\Controller\User;

use Fc2blog\Model\BlogsModel;
use Fc2blog\Web\Request;

class BlogsController extends UserController
{
  /**
   * ランダムなブログにリダイレクト
   * プラグインインストールでポータル画面化予定
   * @param Request $request
   * @return string
   */
  public function index(Request $request)
  {
    $blog = (new BlogsModel())->findByRandom();
    if (empty($blog)) {
      return $this->error404();
    }
    $this->redirect($request, $request->baseDirectory . $blog['id'] . '/');
  }
}
