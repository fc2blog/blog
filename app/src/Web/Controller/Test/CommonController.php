<?php

namespace Fc2blog\Web\Controller\Test;

class CommonController extends TestController
{
  public function index()
  {
    echo "this is test";
  }

  public function phpinfo()
  {
    phpinfo();
  }

  public function redirect_test_no_full_url()
  {
    // $url, $hash = '', bool $full_url = false, string $blog_id = null
    $this->redirect($request, '/_for_unit_test_/phpinfo', '');
  }
  public function redirect_test_full_url()
  {
    // $url, $hash = '', bool $full_url = false, string $blog_id = null
    $this->redirect($request, '/_for_unit_test_/phpinfo', '', true, 'testblog1');
  }
}

