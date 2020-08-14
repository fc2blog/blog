<?php

namespace Fc2blog\Cli\Controller\Cron;

abstract class CronController extends \Fc2blog\Web\Controller\AppController
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

