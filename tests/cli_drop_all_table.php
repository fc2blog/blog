#!/usr/bin/env php
<?php

declare(strict_types=1);

use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\LoaderHelper;

require_once __DIR__ . "/../app/vendor/autoload.php";

define("TEST_APP_DIR", __DIR__ . "/../app");
define("THIS_IS_TEST", true);

/** @noinspection PhpUnhandledExceptionInspection */
LoaderHelper::requireBootStrap();

DBHelper::clearDb();
