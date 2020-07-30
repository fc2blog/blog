<?php

require_once(Config::get('CONTROLLER_DIR') . 'test/test_controller.php');

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
    $this->redirect('/_for_unit_test_/phpinfo', '');
  }
  public function redirect_test_full_url()
  {
    // $url, $hash = '', bool $full_url = false, string $blog_id = null
    $this->redirect('/_for_unit_test_/phpinfo', '', true, 'testblog1');
  }
}

