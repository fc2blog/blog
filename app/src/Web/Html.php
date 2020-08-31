<?php
/**
 * HTMLの便利関数群
 */

namespace Fc2blog\Web;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Util\StringCaseConverter;

class Html
{

  private static $include_css = array();
  private static $include_js = array();

  /**
   * URLを作成する
   * TODO:ユーザー側のURL生成時はApp:userURLを使用に置き換え最終的にblog_idの部分を削る
   * @param Request $request
   * @param array $args
   * @param bool $reused
   * @param bool $full_url
   * @return string
   */
  public static function url(Request $request, $args = array(), $reused = false, $full_url = false)
  {
    // 現在のURLの引数を引き継ぐ
    if ($reused == true) {
      $gets = $request->getGet();
      unset($gets[Config::get('ARGS_CONTROLLER')]);
      unset($gets[Config::get('ARGS_ACTION')]);
      $args = array_merge($gets, $args);
    }

    $controller = Config::get('ControllerName');
    if (isset($args['controller'])) {
      $controller = $args['controller'];
      unset($args['controller']);
    }
    $controller = StringCaseConverter::snakeCase($controller);

    $action = Config::get('ActionName');
    if (isset($args['action'])) {
      $action = $args['action'];
      unset($args['action']);
    }

    // 引数のデバイスタイプを取得
    $device_name = App::getArgsDevice($request);
    if (!empty($device_name) && isset($args[$device_name])) {
      unset($args[$device_name]);
    }

    // URL/Controller/Methodの形で返却
    if (Config::get('URL_REWRITE') && !$full_url) {
      $params = array();
      foreach ($args as $key => $value) {
        $params[] = $key . '=' . $value;
      }
      if (!empty($device_name)) {
        $params[] = $device_name;
      }

      $url = Config::get('BASE_DIRECTORY') . $controller . '/' . $action;
      if (count($params)) {
        $url .= '?' . implode('&', $params);
      }
      return $url;
    }

    // BlogIdを先頭に付与する
    $blog_id = null;
    if (isset($args['blog_id'])) {
      $blog_id = $args['blog_id'];
      unset($args['blog_id']);
    }

    $params = array();
    $params[] = Config::get('ARGS_CONTROLLER') . '=' . $controller;
    $params[] = Config::get('ARGS_ACTION') . '=' . $action;
    foreach ($args as $key => $value) {
      $params[] = $key . '=' . $value;
    }
    if (!empty($device_name)) {
      $params[] = $device_name;
    }

    $url = Config::get('BASE_DIRECTORY') . Config::get('DIRECTORY_INDEX');
    if (count($params)) {
      $url .= '?' . implode('&', $params);
    }
    if ($blog_id) {
      $url = '/' . $blog_id . $url;
    }

    // フルのURLを取得する（SSL強制リダイレクト用）
    if ($full_url && !is_null($blog_id)) {
      $url = BlogsModel::getFullHostUrlByBlogId($blog_id) . $url;
    }
    return $url;
  }

  public static function input(Request $request, $name, $type, $attrs = array(), $option_attrs = array())
  {
    $default = isset($attrs['default']) ? $attrs['default'] : null;    // デフォルト文字列
    $options = isset($attrs['options']) ? $attrs['options'] : array(); // オプション
    $label = isset($attrs['label']) ? $attrs['label'] : '';          // ラベル
    unset($attrs['default'], $attrs['options'], $attrs['label']);

    // Requestの親キー(default判定)
    $parentKey = explode('[', $name);
    $parentKey = $parentKey[0];
    if ($request->get($parentKey)) {
      $default = null;
    }

    // Requestから取得する用のname
    $rname = str_replace(array('[', ']'), array('.', ''), $name);
    $rvalue = $request->get($rname, $default);

    // 属性作成
    $attrs['name'] = ($type == 'checkbox' && count($options)) ? $name . '[]' : $name;
    $attr = array();
    foreach ($attrs as $key => $value) {
      $attr[] = $key . '="' . $value . '"';
    }
    $attr = implode(' ', $attr);

    foreach ($option_attrs as $key => $attrs) {
      $option_attr = array();
      foreach ($attrs as $attr_key => $attr_value) {
        $option_attr[] = $attr_key . '="' . $attr_value . '"';
      }
      $option_attrs[$key] = implode(' ', $option_attr);
    }

    // HTMLを作成
    $html = '';
    switch ($type) {
      default:
        $html = '<span>[' . $type . ']は未実装です</span>';
        break;

      case 'text':
        $html = '<input type="text" ' . $attr . ' value="' . h($rvalue) . '" />';
        break;

      case 'password':
        $html = '<input type="password" ' . $attr . ' value="' . h($rvalue) . '" />';
        break;

      case 'file':
        $html = '<input type="file" ' . $attr . ' />';
        break;

      case 'hidden':
        $html = '<input type="hidden" ' . $attr . ' value="' . h($rvalue) . '" />';
        break;

      case 'token':
        $html = '<input type="hidden" ' . $attr . ' value="' . h(Session::get($name)) . '" />';
        break;

      case 'captcha':
        $html = '<input type="text" ' . $attr . ' value="" />';
        break;

      case 'textarea':
        $html = '<textarea ' . $attr . '>' . h($rvalue) . '</textarea>';
        break;

      case 'select':
        $html = '<select ' . $attr . '>';
        foreach ($options as $key => $option) {
          if (is_array($option)) {
            // オプショングループ付きSelect
            if (!isset($option['value'])) {
              $html .= '<optgroup label="' . $key . '">';
              foreach ($option as $k => $v) {
                $html .= '<option value="' . $k . '" ' . ($rvalue !== null && $k == $rvalue ? 'selected="selected"' : '') . '>' . h($v) . '</option>';
              }
              $html .= '</optgroup>';
              continue;
            }
            // 属性付きオプション
            $optionAttr = ($rvalue !== null && $key == $rvalue ? 'selected="selected"' : '');
            if (!empty($option['disabled'])) {
              $optionAttr .= ' disabled="disabled" ';
            }
            $html .= '<option value="' . $key . '" ' . $optionAttr . '>' . str_repeat('&nbsp;&nbsp;&nbsp;', $option['level'] - 1) . h($option['value']) . '</option>';
          } else {
            // 通常のオプション
            $html .= '<option value="' . $key . '" ' . ($rvalue !== null && $key == $rvalue ? 'selected="selected"' : '') . '>' . h($option) . '</option>';
          }
        }
        $html .= '</select>';
        break;

      case 'radio':
        $labelKey = 'sys-radio-' . str_replace(array('[', ']'), array('-', ''), $name) . '-';
        $html .= '<ul class="form-radio-list">';
        $li_attr = isset($option_attrs['li']) ? ' ' . $option_attrs['li'] : '';
        $label_attr = isset($option_attrs['label']) ? ' ' . $option_attrs['label'] : '';
        foreach ($options as $key => $option) {
          $html .= '<li' . $li_attr . '>';
          $html .= '<input type="radio" value="' . $key . '" ' . ($key == $rvalue ? 'checked="checked"' : '') . ' ' . $attr . ' id="' . $labelKey . $key . '" />';
          $html .= ' <label for="' . $labelKey . $key . '"' . $label_attr . '>' . $option . '</label>';
          $html .= '</li>';
        }
        $html .= '</ul>';
        break;

      case 'checkbox':
        if (count($options)) {
          $labelKey = 'sys-checkbox-' . str_replace(array('[', ']'), array('-', ''), $name) . '-';
          $rvalue = is_array($rvalue) ? $rvalue : array();
          foreach ($options as $key => $option) {
            $html .= '<input type="checkbox" value="' . $key . '" ' . (in_array($key, $rvalue) ? 'checked="checked"' : '') . ' ' . $attr . ' id="' . $labelKey . $key . '" />';
            $html .= '<label for="' . $labelKey . $key . '">' . $option . '</label>';
          }
        } else {
          $labelKey = 'sys-checkbox-' . str_replace(array('[', ']'), array('-', ''), $name);
          $is_checked = $rvalue !== null && isset($attrs['value']) && $attrs['value'] == $rvalue;
          $html .= '<input type="checkbox" ' . ($is_checked ? 'checked="checked"' : '') . ' ' . $attr . ' id="' . $labelKey . '" />';
          if ($label) {
            $html .= '<label for="' . $labelKey . '">' . $label . '</label>';
          }
        }
        break;
    }

    return $html;
  }

  /**
   * CSSを追加する
   * @param $css
   * @param array $options
   */
  public static function addCSS($css, $options = array())
  {
    self::$include_css[] = array($css, $options);
  }

  /**
   * 追加されたCSSのHTMLを取得
   */
  public static function getCSSHtml()
  {
    $css_html = '';
    foreach (self::$include_css as $css) {
      $attrs = array(
          'rel' => 'stylesheet',
          'type' => 'text/css',
          'charset' => 'utf-8',
          'media' => 'all',
        ) + $css[1];
      $css_html .= '<link href="' . $css[0] . '"';
      foreach ($attrs as $key => $value) {
        $css_html .= ' ' . $key . '="' . $value . '"';
      }
      $css_html .= ' />' . "\n";
    }
    return $css_html;
  }

  /**
   * JSを追加する
   * @param $js
   * @param array $options
   */
  public static function addJS($js, $options = array())
  {
    self::$include_js[] = array($js, $options);
  }

  /**
   * 追加されたJSのHTMLを取得
   */
  public static function getJSHtml()
  {
    $js_html = '';
    foreach (self::$include_js as $js) {
      $attrs = array(
          'type' => 'text/javascript',
          'charset' => 'utf-8',
        ) + $js[1];
      $js_html .= '<script src="' . $js[0] . '"';
      foreach ($attrs as $key => $value) {
        $js_html .= ' ' . $key . '="' . $value . '"';
      }
      $js_html .= '></script>' . "\n";
    }
    return $js_html;
  }

  public static function getServerUrl()
  {
    $url = (empty($_SERVER["HTTPS"])) ? 'http://' : 'https://';
    $url .= Config::get('DOMAIN');
    return $url;
  }
}

