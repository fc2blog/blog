<?php

require_once(Config::get('CONTROLLER_DIR') . 'app_controller.php');

abstract class CronController extends AppController
{

  protected $layout = '';

  /**
  * ブログID取得
  */
  public function getBlogId()
  {
    $request = Request::getInstance();
    return $request->get('blog_id');
  }

}

