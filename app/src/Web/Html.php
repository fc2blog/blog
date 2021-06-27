<?php
declare(strict_types=1);

namespace Fc2blog\Web;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Util\StringCaseConverter;

class Html
{
    /**
     * Admin系画面用（BlogId非対応）のurl()のwrapper, コントローラー名とアクション名を簡便に指定する
     * @param Request $request
     * @param string $controller
     * @param string $action
     * @param array $args
     * @param bool $full_url
     * @return string
     */
    public static function adminUrl(Request $request, string $controller, string $action, array $args = [], bool $full_url = false): string
    {
        $path = static::url(
            $request,
            array_merge(
                ['controller' => $controller, 'action' => $action],
                $args
            ),
        );
        if ($full_url) {
            return static::getServerUrl($request) . $path;
        } else {
            return $path;
        }
    }

    /**
     * URLを作成する
     * TODO: ユーザー側のURL生成時はApp:userURLを使用に置き換え最終的にblog_idの部分を削る
     * @param Request $request
     * @param array $args
     * @param bool $reused
     * @param bool $full_url
     * @param bool $use_base_dir
     * @return string
     */
    public static function url(Request $request, array $args = array(), bool $reused = false, bool $full_url = false, bool $use_base_dir = true): string
    {
        // 現在のURLの引数を引き継ぐ
        if ($reused == true) {
            $gets = $request->getGet();
            unset($gets['mode']);
            unset($gets['process']);
            $args = array_merge($gets, $args);
        }

        $controller = $request->shortControllerName;
        if (isset($args['controller'])) {
            $controller = $args['controller'];
            unset($args['controller']);
        }
        $controller = StringCaseConverter::snakeCase($controller);

        $action = $request->methodName;
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
        if ($request->urlRewrite && !$full_url) {
            $params = array();
            foreach ($args as $key => $value) {
                $params[] = $key . '=' . $value;
            }
            if (!empty($device_name)) {
                $params[] = $device_name;
            }

            $url = $request->baseDirectory . $controller . '/' . $action;
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
        $params[] = 'mode=' . $controller;
        $params[] = 'process=' . $action;
        foreach ($args as $key => $value) {
            $params[] = $key . '=' . rawurlencode((string)$value);
        }
        if (!empty($device_name)) {
            $params[] = $device_name;
        }

        if ($use_base_dir) {
            $url = $request->baseDirectory . 'index.php';
        } else {
            $url = "/index.php";
        }
        if (count($params)) {
            $url .= '?' . implode('&', $params);
        }
        // シングルテナントモードのために、/testblog2/〜〜 のブログ指定部分を省略するか？を決定
        if (
            $blog_id && (
                // Default Blog IDが未指定か
                strlen(Config::get('DEFAULT_BLOG_ID', "")) === 0 ||
                // 指定されたblog_idがDefault blog idと異なる場合
                $blog_id !== Config::get('DEFAULT_BLOG_ID')
            )
        ) {
            $url = '/' . $blog_id . $url;
        }

        // フルのURLを取得する（SSL強制リダイレクト用）
        if ($full_url && !is_null($blog_id)) {
            $url = BlogsModel::getFullHostUrlByBlogId($blog_id) . $url;
        }
        return $url;
    }

    /** @noinspection XmlInvalidId HTML組み立てが複雑で、Linterが解析しきれないため */
    public static function input(Request $request, $name, $type, $attrs = array(), $option_attrs = array()): string
    {
        $default = $attrs['default'] ?? null; // デフォルト文字列
        $options = $attrs['options'] ?? array(); // オプション
        $label = $attrs['label'] ?? ''; // ラベル
        $suffix = $attrs['suffix'] ?? ''; // 〜「件」 などのSuffix

        unset($attrs['default'], $attrs['options'], $attrs['label']);

        // Requestの親キー(default判定)
        $parentKey = explode('[', $name);
        $parentKey = $parentKey[0];
        if ($request->get($parentKey)) {
            $default = null;
        }

        // Requestから取得する用のname
        $r_name = str_replace(array('[', ']'), array('.', ''), $name);
        $rvalue = $request->get($r_name, $default);

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
                $html = '<input type="text" ' . $attr . ' value="' . h((string)$rvalue) . '" />';
                break;

            case 'password':
                $html = '<input type="password" ' . $attr . ' value="' . h((string)$rvalue) . '" />';
                break;

            case 'blank_password':
                // 一方向に設定するので、表示しない
                $html = '<input type="password" ' . $attr . ' />';
                break;

            case 'file':
                $html = '<input type="file" ' . $attr . ' />';
                break;

            case 'hidden':
                $html = '<input type="hidden" ' . $attr . ' value="' . h((string)$rvalue) . '" />';
                break;

            case 'token':
                $html = '<input type="hidden" ' . $attr . ' value="' . h(Session::get($name)) . '" />';
                break;

            case 'captcha':
                $html = '<input type="text" ' . $attr . ' value="" />';
                break;

            case 'textarea':
                $html = '<textarea ' . $attr . '>' . h((string)$rvalue) . '</textarea>';
                break;

            case 'select':
                $html = '<select ' . $attr . '>';
                foreach ($options as $key => $option) {
                    if (is_array($option)) {
                        // オプショングループ付きSelect
                        if (!isset($option['value'])) {
                            $html .= '<optgroup label="' . $key . '">';
                            foreach ($option as $k => $v) {
                                $html .= '<option value="' . $k . '" ' . ($rvalue !== null && $k == $rvalue ? 'selected="selected"' : '') . '>' . h($v . $suffix) . '</option>';
                            }
                            $html .= '</optgroup>';
                            continue;
                        }
                        // 属性付きオプション
                        $optionAttr = ($rvalue !== null && $key == $rvalue ? 'selected="selected"' : '');
                        if (!empty($option['disabled'])) {
                            $optionAttr .= ' disabled="disabled" ';
                        }
                        $html .= '<option value="' . $key . '" ' . $optionAttr . '>' . str_repeat('&nbsp;&nbsp;&nbsp;', $option['level'] - 1) . h($option['value'] . $suffix) . '</option>';
                    } else {
                        // 通常のオプション
                        $html .= '<option value="' . $key . '" ' . ($rvalue !== null && $key == $rvalue ? 'selected="selected"' : '') . '>' . h($option . $suffix) . '</option>';
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
                    $html .= '  <input type="radio" value="' . $key . '" ' . ($key == $rvalue ? 'checked="checked"' : '') . ' ' . $attr . ' id="' . $labelKey . $key . '" />';
                    $html .= '  <label for="' . $labelKey . $key . '" ' . $label_attr . '>' . $option . '</label>';
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

    public static function getServerUrl(Request $request): string
    {
        /** @noinspection HttpUrlsUsage */
        $url = $request->isHttps() ? 'https://' : 'http://';
        $url .= App::DOMAIN;
        $url .= $request->isHttps() ? App::HTTPS_PORT_STR : App::HTTP_PORT_STR;
        return $url;
    }
}

