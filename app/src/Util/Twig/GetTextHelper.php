<?php

declare(strict_types=1);

namespace Fc2blog\Util\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetTextHelper extends AbstractExtension
{
  public function getFunctions(): array
  {
    return [new TwigFunction(
      '_',
      function (string $str) {
        return __($str);
      }
    )];
  }
}
