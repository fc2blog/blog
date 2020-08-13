<?php

require_once(\Fc2blog\Config::get('CONTROLLER_DIR') . 'app_controller.php');

abstract class CronController extends AppController
{

  protected $layout = '';

  /**
  * ブログID取得
  */
  public function getBlogId()
  {
    $request = \Fc2blog\Request::getInstance();
    return $request->get('blog_id');
  }

}

