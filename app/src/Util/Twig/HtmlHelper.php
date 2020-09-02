<?php

declare(strict_types=1);

namespace Fc2blog\Util\Twig;

use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class HtmlHelper extends AbstractExtension
{
  public function getFunctions(): array
  {
    return [
      new TwigFunction(
        'input',
        function (Request $request, $name, $type, array $attrs = [], array $option_attrs = []) {
          return Html::input($request, $name, $type, $attrs, $option_attrs);
        },
        ['is_safe' => ['html']]
      ),
      new TwigFunction(
        'url',
        function (Request $request, string $controller, string $action, array $args = [], $reused = false, $full_url = false) {
          $args = array_merge(
            [
              'controller' => $controller,
              'action' => $action
            ],
            $args
          );
          return Html::url($request, $args, $reused, $full_url);
        }
      ),
      new TwigFunction(
        '_s',
        function (string $str, ...$args) {
          return sprintf(__($str), ...$args);
        }
      ),
    ];
  }
}
