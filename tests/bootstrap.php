<?php

declare(strict_types=1);

use Fc2blog\Tests\LoaderHelper;

require_once __DIR__ . "/../vendor/autoload.php";

define("TEST_APP_DIR", __DIR__ . "/../app");
define("THIS_IS_TEST", true);
LoaderHelper::requireBootStrap();
