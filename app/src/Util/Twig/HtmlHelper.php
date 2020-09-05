<?php

declare(strict_types=1);

namespace Fc2blog\Util\Twig;

use Fc2blog\App;
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
        },
        ['is_safe' => ['html']]
      ),
      new TwigFunction(
        'entryUrl',
        function (Request $request, $blog_id, $entry_id, $is_sp = false) {
          $opt = ['controller' => 'Entries', 'action' => 'view', 'blog_id' => $blog_id, 'id' => $entry_id];
          if ($is_sp) {
            $opt['sp'] = "1";
          }
          return App::userURL($request, $opt, false, true);
        },
        ['is_safe' => ['html']]
      ),
      new TwigFunction(
        'userPreviewUrl',
        function (Request $request, $blog_id, $template_id, $device_key) {
          $opt = ['controller' => 'Entries', 'action' => 'preview', 'blog_id' => $blog_id, 'template_id' => $template_id, $device_key => 1];
          return App::userURL($request, $opt, false, true);
        },
        ['is_safe' => ['html']]
      ),
      new TwigFunction(
        'userUrl',
        function (Request $request, array $args = [], bool $reused = false, bool $abs = false) {
          $opt = array_merge(['controller' => 'Entries', 'action' => 'preview'], $args);
          return App::userURL($request, $opt, $reused , $abs);
        },
        ['is_safe' => ['html']]
      ),
      new TwigFunction(
        't',
        function (string $text, int $length = 10, string $etc = '...') {
          if (!$length) {
            return '';
          }
          if (mb_strlen($text, "UTF-8") > $length) {
            return mb_substr($text, 0, $length, "UTF-8") . $etc;
          }
          return $text;
        },
      ),
      new TwigFunction(
        'ue',
        function (?string $text) {
          if (is_null($text)) return "";
          return rawurlencode($text);
        },
      ),
      new TwigFunction(
        'd',
        function (?string $text, $default = "") {
          if ($text === null || $text === '') {
            return $default;
          }
          return $text;
        },
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
        'getNumRangeOptionTags',
        function (string $start, string $end) {
          $html = "";
          for ($i = $start; $i <= $end; $i++) {
            $html .= '<option value="' . sprintf('%02d', $i) . '">' . sprintf('%02d', $i) . '</option>';
          }
          return $html;
        },
        ['is_safe' => ['html']]
      ),
      new TwigFunction(
        'spaceIndent',
        function (int $num, string $str = '&nbsp;&nbsp;&nbsp;', bool $zero_base = true) {
          if ($zero_base) $num--;
          return str_repeat($str, $num);
        },
        ['is_safe' => ['html']]
      ),
      new TwigFunction(
        'inArray',
        function (string $needle, array $list): bool {
          return in_array($needle, $list);
        },
        ['is_safe' => ['html']]
      ),
      new TwigFunction( // TODO refactoring.
        'renderCategoriesTree',
        function (array $categories, array $entry_categories, bool $is_sp = false) {
          $level = 1;
          foreach ($categories as $category) {
            if ($level < $category['level']) {
              $level = $category['level'];
              echo "<li><ul>";
            }

            if ($level > $category['level']) {
              for (; $level > $category['level']; $level--) {
                if ($is_sp) {
                  echo "</li></ul>";
                } else {
                  echo "</ul>";
                }
              }
            }

            if ($level == $category['level']) {
              if ($is_sp) { ?>
                  <label for="sys-entry-categories-id-<?php echo $category['id']; ?>">
                      <input id="sys-entry-categories-id-<?php echo $category['id']; ?>"
                             class="checkbox_btn_input"
                             type="checkbox" name="entry_categories[category_id][]"
                             value="<?php echo $category['id']; ?>"
                             <?php if (in_array($category['id'], $entry_categories['category_id'])) : ?>checked="checked"<?php endif; ?>
                             onchange="categoryChange(this);"
                      />
                    <?php echo h($category['name']); ?>
                  </label>
                <?php
              } else {
                ?>

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
          }
          // タグを全部閉じる
          for (; $level > 1; $level--) {
            echo "</li></ul>";
          }
        },
      ),
    ];
  }
}
