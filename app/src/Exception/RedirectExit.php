<?php

declare(strict_types=1);

namespace Fc2blog\Exception;

/**
 * Class 疑似Exit、Redirect情報プロパティを持つ。主にテスト用
 * @package Fc2blog\Exception
 */
class RedirectExit extends PseudoExit
{
    public $redirectUrl = "";
    public $statusCode = 0;
}
