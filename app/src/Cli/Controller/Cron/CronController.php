<?php

namespace Fc2blog\Cli\Controller\Cron;

use Fc2blog\Web\Controller\AppController;
use Fc2blog\Web\Request;

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

