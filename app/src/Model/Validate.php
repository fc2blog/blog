<?php
declare(strict_types=1);

namespace Fc2blog\Model;

class Validate
{
    /**
     * 必須チェック
     * @param $value
     * @param bool $isNeed true/false
     * @return bool|string True is valid, string is some error message.
     */
    public static function required($value, bool $isNeed)
    {
        if ($value == null || $value === '') {
            // データが存在しない場合
            if ($isNeed === false) {
                return false;
            }
            return __('Please be sure to input');
        }
        // データが存在する場合 Validate処理を続ける
        return true;
    }

    /**
     * 数値チェック
     * @param int|string $value
     * @param array $options
     * @return bool|string True is valid, string is some error message.
     */
    public static function numeric($value, array $options)
    {
        $tmp = intval($value);
        if ((string)$tmp === (string)$value) {
            return true;
        }
        return $options['message'] ?? __('Please enter a number');
    }

    /**
     * 半角英数チェック
     * @param string $value
     * @param array $options
     * @return bool|string True is valid, string is some error message.
     */
    public static function alphanumeric(string $value, array $options)
    {
        if (preg_match("/^[a-zA-Z0-9]+$/", $value)) {
            return true;
        }
        return $options['message'] ?? __('Please enter alphanumeric');
    }

    /**
     * 最大文字列チェック
     * @param ?string $value (comment編集で、NULLがくることがある)
     * @param array $options [*max] 最大文字列
     * @return bool|string True is valid, string is some error message.
     */
    public static function maxlength(?string $value, array $options)
    {
        if (mb_strlen((string)$value, 'UTF-8') <= $options['max']) {
            return true;
        }
        $message = $options['message'] ?? __('Please enter at %d characters or less');
        return sprintf($message, $options['max']);
    }

    /**
     * 最小文字列チェック
     * @param string|null $value
     * @param array $options [*min] 最小文字列
     * @return bool|string True is valid, string is some error message.
     */
    public static function minlength(?string $value, array $options)
    {
        if (mb_strlen((string)$value, 'UTF-8') >= $options['min']) {
            return true;
        }
        $message = $options['message'] ?? __('Please enter at %d characters or more');
        return sprintf($message, $options['min']);
    }

    /**
     * 最大値チェック
     * @param int|string $value
     * @param array $options [*max] 最大値
     * @return bool|string True is valid, string is some error message.
     */
    public static function max($value, array $options)
    {
        if ($value <= $options['max']) {
            return true;
        }
        $message = $options['message'] ?? __('Please enter a value of %d or less');
        return sprintf($message, $options['max']);
    }

    /**
     * 最小値チェック
     * @param int|string $value
     * @param array $options [*min] 最小値
     * @return bool|string True is valid, string is some error message.
     */
    public static function min($value, array $options)
    {
        if ($value >= $options['min']) {
            return true;
        }
        $message = $options['message'] ?? __('Please enter a value of %d or more');
        return sprintf($message, $options['min']);
    }

    /**
     * 日時チェック
     * @param string $value
     * @param array $options
     * @return bool|string True is valid, string is some error message.
     */
    public static function datetime(string $value, array $options)
    {
        $format = $options['format'] ?? '%Y-%m-%d %H:%M:%S';
        if (strptime($value, $format) === false || strtotime($value) === false) {
            return $options['message'] ?? __('Please enter the date and time');
        }
        return true;
    }

    /**
     * メールアドレスチェック
     * @param string $value
     * @param array $options
     * @return bool|string True is valid, string is some error message.
     */
    public static function email(string $value, array $options)
    {
        /** @noinspection Annotator */
        /** @noinspection RegExpUnnecessaryNonCapturingGroup */
        /** @noinspection RegExpRedundantEscape */
        /** @noinspection RegExpUnexpectedAnchor */
        if (preg_match('/^(?:(?:(?:(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+)(?:\.(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+))*)|(?:"(?:\\[^\r\n]|[^\\"])*")))\@(?:(?:(?:(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+)(?:\.(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+))*)|(?:\[(?:\\\S|[\x21-\x5a\x5e-\x7e])*\])))$/', $value)) {
            return true;
        }
        return $options['message'] ?? __('Please enter your e-mail address');
    }

    /**
     * URLチェック
     * @param string $value
     * @param array $options
     * @return bool|string True is valid, string is some error message.
     */
    public static function url(string $value, array $options)
    {
        if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:@&=+\$,%#]+)$/', $value)) {
            return true;
        }
        return $options['message'] ?? __('Please enter the URL');
    }

    /**
     * ユニークチェック
     * @param $value
     * @param array $options
     * @param string $key
     * @param $data
     * @param $model
     * @return bool|string True is valid, string is some error message.
     */
    public static function unique($value, array $options, string $key, $data, $model)
    {
        if (!$model->isExist(array('where' => $key . '=?', 'params' => array($value)))) {
            return true;
        }
        return $options['message'] ?? __('Is already in use');
    }


    /**
     * 配列に存在する値かチェック
     * @param $value
     * @param array $options
     * @return bool|string True is valid, string is some error message.
     */
    public static function in_array($value, array $options)
    {
        if (is_scalar($value)) {
            $tmp = array_flip($options['values']);
            if (isset($tmp[$value])) {
                return true;
            }
        }
        return $options['message'] ?? __('Value that does not exist has been entered');
    }


    /**
     * Fileチェック
     * @param array $value
     * @param array $option
     * @return bool|string True is valid, string is some error message.
     */
    public static function file(array $value, array $option)
    {
        switch ($value['error']) {
            case UPLOAD_ERR_OK:
                break;  // OK

            case UPLOAD_ERR_NO_FILE:
                if (empty($option['required'])) {
                    return false;
                }
                return __('Please upload a file');

            case UPLOAD_ERR_INI_SIZE:   // php.ini定義の最大サイズ超過
            case UPLOAD_ERR_FORM_SIZE:  // フォーム定義の最大サイズ超過
                return __('File size is too large');

            default:  // 以外のエラー
                return __('I failed to file upload');
        }

        // mimetype取得チェック
        if (!empty($option['mime_type'])) {
            if (function_exists('finfo_file') && defined('FILEINFO_MIME_TYPE')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime_type = finfo_file($finfo, $value['tmp_name']);
                finfo_close($finfo);
            } else if (function_exists('mime_content_type')) {
                // 非推奨
                $mime_type = mime_content_type($value['tmp_name']);
            } else {
                // 調べる関数がライブラリに存在しない
                return __('It was not possible to determine the mime type of the file');
            }

            if (!in_array($mime_type, $option['mime_type'])) {
                return __('File format is different');
            }
        }

        // sizeチェック
        if (isset($option['size']) && $value['size'] > $option['size']) {
            return __('File size is too large');
        }

        return true;
    }

    /**
     * 独自チェック
     * @param $value
     * @param array $option
     * @param string $key
     * @param $data
     * @param $model
     * @return mixed
     */
    public static function own(&$value, array $option, string $key, $data, $model)
    {
        $method = $option['method'];
        return $model->$method($value, $option, $key, $data, $model);
    }

    /**
     * 配列チェック関数
     * @param $values
     * @param array $valid
     * @param $k
     * @param $d
     * @param $model
     * @return mixed
     */
    public static function multiple(&$values, array $valid, $k, $d, $model): bool
    {
        if (!is_array($values)) {
            $values = array();
        }
        foreach ($valid as $mKey => $options) {
            $method = is_array($options) && isset($options['rule']) ? $options['rule'] : $mKey;
            foreach ($values as $key => $value) {
                $error = Validate::$method($value, $options, $key, $values, $model);
                if ($error === false) {
                    break;
                }
                if (getType($error) === 'string') {
                    return $error;
                }
            }
        }
        return true;
    }

    /**
     * 空白除去処理
     * @param string $value
     * @return bool
     */
    public static function trim(string &$value): bool
    {
        if (is_string($value)) {
            $value = preg_replace("/(^\s+)|(\s+$)/us", "", $value);
        }
        return true;
    }

    /**
     * int型に変換
     * @param string|int $value
     * @return bool
     */
    public static function int(&$value): bool
    {
        $value = intval($value);
        return true;
    }

    /**
     * 小文字に変換
     * @param string $value
     * @return bool
     */
    public static function strtolower(string &$value): bool
    {
        $value = strtolower($value);
        return true;
    }

    /**
     * 配列内重複排除
     * @param $values
     * @param bool $options
     * @return bool
     */
    public static function array_unique(&$values, bool $options): bool
    {
        if (!is_array($values)) {
            $values = array();
            return true;
        }
        $values = array_unique($values);
        return true;
    }


    /**
     * デフォルトデータ設定
     * @param $value
     * @param $default
     * @return bool
     * @noinspection PhpUnused
     */
    public static function default_value(&$value, $default): bool
    {
        if ($value === null || $value === "") {
            $value = $default;
        }
        return true;
    }

    /**
     * データを置き換える
     * @param $value
     * @param $replaces
     * @return bool
     */
    public static function replace(&$value, $replaces): bool
    {
        $value = str_replace(array_keys($replaces), array_values($replaces), (string)$value);
        return true;
    }
}
