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
      new TwigFunction(
        'ifCookie',
        function (Request $request, string $cookie_name, string $str) {
          if ($request->getCookie($cookie_name)) {
            return $str;
          } else {
            return null;
          }
        }
      ),
      new TwigFunction(
        'ifReqGet',
        function (Request $request, string $key_name, string $str) {
          if ($request->get($key_name)) {
            return $str;
          } else {
            return null;
          }
        }
      ),
      new TwigFunction(
        'ifNotReqGet',
        function (Request $request, string $key_name, string $str) {
          if (!$request->get($key_name)) {
            return $str;
          } else {
            return null;
          }
        }
      ),
      new TwigFunction(
        'spaceIndent',
        function (int $num, string $str = '&nbsp;&nbsp;&nbsp;', bool $zero_base = true) {
          if ($zero_base) $num--;
          return str_repeat($str, $num);
        }
      ),
      new TwigFunction( // TODO refactoring.
        'renderCategoriesTree',
        function (array $categories, array $entry_categories) {
          $level = 1;
          foreach ($categories as $category) {
            if ($level < $category['level']) {
              $level = $category['level'];
              echo "<li><ul>";
            }

            if ($level > $category['level']) {
              for (; $level > $category['level']; $level--) {
                echo "</ul>";
              }
            }

            if ($level == $category['level']) { ?>
                <li
                  <?php if (in_array($category['id'], $entry_categories['category_id'])) : ?>class="active"<?php endif; ?>>
                    <input id="sys-entry-categories-id-<?php echo $category['id']; ?>"
                           type="checkbox" name="entry_categories[category_id][]"
                           value="<?php echo $category['id']; ?>"
                           <?php if (in_array($category['id'], $entry_categories['category_id'])) : ?>checked="checked"<?php endif; ?>
                           onclick="categoryChange(this);"
                    />
                    <label for="sys-entry-categories-id-<?php echo $category['id']; ?>"><?php echo h($category['name']); ?></label>
                </li>
              <?php
            }
          }
          // タグを全部閉じる
          for (; $level > 1; $level--) {
            echo "</li></ul>";
          }
        }
      ),
    ];
  }
}
