<?php

declare(strict_types=1);

namespace Fc2blog\Exception;

class RedirectExit extends PseudoExit
{
  public $redirectUrl = "";
  public $statusCode = 0;
}
