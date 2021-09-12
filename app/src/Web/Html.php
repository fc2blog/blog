<?php
declare(strict_types=1);

namespace Fc2blog\Web;

use Fc2blog\App;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Service\TwigService;
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
                is_null(App::getDefaultBlogId()) ||
                // 指定されたblog_idがDefault blog idと異なる場合
                $blog_id !== App::getDefaultBlogId()
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
        $attrs['name'] = ($type == 'checkbox' && count($options) > 0) ? $name . '[]' : $name;
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

        // TODO singleton化
        $twig = TwigService::getTwigInstance();

        switch ($type) {
            default:
                $html = $twig->render('fragment/undefined.twig', ['type' => $type]);
                break;

            case 'text':
                $html = $twig->render('fragment/text.twig', ['attr' => $attr, 'rvalue' => $rvalue]);
                break;

            case 'password':
                $html = $twig->render('fragment/password.twig', ['attr' => $attr, 'rvalue' => $rvalue]);
                break;

            case 'blank_password':
                $html = $twig->render('fragment/password.twig', ['attr' => $attr, 'rvalue' => '']);
                break;

            case 'file':
                $html = $twig->render('fragment/file.twig', ['attr' => $attr]);
                break;

            case 'hidden':
                $html = $twig->render('fragment/hidden.twig', ['attr' => $attr, 'rvalue' => $rvalue]);
                break;

            case 'token': // TODO すでに使われていないのではないか？
                $html = $twig->render('fragment/hidden.twig', ['attr' => $attr, 'rvalue' => Session::get($name)]);
                break;

            case 'captcha':
                $html = $twig->render('fragment/text.twig', ['attr' => $attr, 'rvalue' => ""]);
                break;

            case 'textarea':
                $html = $twig->render('fragment/textarea.twig', ['attr' => $attr, 'rvalue' => $rvalue]);
                break;

            case 'select':
                $html = $twig->render('fragment/select.twig', [
                    'attr' => $attr,
                    'rvalue' => $rvalue,
                    'suffix' => $suffix,
                    'option_list' => $options,
                ]);
                break;

            case 'radio':
                $labelKey = 'sys-radio-' . str_replace(['[', ']'], ['-', ''], $name) . '-';
                $li_attr = $option_attrs['li'] ?? '';
                $label_attr = $option_attrs['label'] ?? '';
                $html .= $twig->render('fragment/ul.twig', ['attr' => $attr, 'rvalue' => $rvalue, 'option_list' => $options, 'label_key' => $labelKey, 'li_attr' => $li_attr, 'label_attr' => $label_attr]);
                break;

            case 'checkbox':
                $labelKey = 'sys-checkbox-' . str_replace(['[', ']'], ['-', ''], $name) . '-';
                $rvalue = is_array($rvalue) ? $rvalue : [$rvalue];
                $is_checked_key_list = [];
                foreach ($options as $key => $option) {
                    if (in_array($key, $rvalue)) {
                        $is_checked_key_list[] = $key;
                    }
                }
                $html = $twig->render('fragment/checkbox.twig', ['attr' => $attr, 'rvalue' => $rvalue, 'option_list' => $options, 'label_key' => $labelKey, 'is_checked_key_list' => $is_checked_key_list]);
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

