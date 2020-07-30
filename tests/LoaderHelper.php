<?php
declare(strict_types=1);

namespace Fc2blog\Tests;

use PHPUnit\Framework\TestCase;

class LoaderHelper extends TestCase
{
  public static function requireBootStrap()
  {
    if (file_exists(TEST_APP_DIR . "/../public/config.php")) {
      require_once(TEST_APP_DIR . "/../public/config.php");
    } else {
      require_once(TEST_APP_DIR . "/../docker/docker.config.php");
    }
  }
}
