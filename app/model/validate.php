<?php
/**
* 入力エラーチェッククラス
*/

class Validate
{

  /**
  * 必須チェック
  */
  public static function required($value, $options)
  {
    if ($value==null || $value==='') {
      // データが存在しない場合
      if ($options===false) {
        return false;
      }
      return __('Please be sure to input');
    }
    // データが存在する場合 Validate処理を続ける
    return true;
  }

  /**
  * 数値チェック
  */
  public static function numeric($value, $options)
  {
    $tmp = intval($value);
    if ((string)$tmp === (string)$value) {
      return true;
    }
    return isset($options['message']) ? $options['message'] : __('Please enter a number');
  }

  /**
  * 半角英数チェック
  */
  public static function alphanumeric($value, $options)
  {
    if (preg_match("/^[a-zA-Z0-9]+$/", $value)) {
      return true;
    }
    return isset($options['message']) ? $options['message'] : __('Please enter alphanumeric');
  }

  /**
  * 最大文字列チェック
  * @param string $value
  * @param array $options [*max] 最大文字列
  */
  public static function maxlength($value, $options)
  {
    if (mb_strlen($value) <= $options['max']) {
      return true;
    }
    $message = isset($options['message']) ? $options['message'] : __('Please enter at %d characters or less');
    return sprintf($message, $options['max']);
  }

  /**
  * 最小文字列チェック
  * @param string $value
  * @param array $options [*min] 最小文字列
  */
  public static function minlength($value, $options)
  {
    if (mb_strlen($value) >= $options['min']) {
      return true;
    }
    $message = isset($options['message']) ? $options['message'] : __('Please enter at %d characters or more');
    return sprintf($message, $options['min']);
  }

  /**
  * 最大値チェック
  * @param string $value
  * @param array $options [*max] 最大値
  */
  public static function max($value, $options)
  {
    if ($value <= $options['max']) {
      return true;
    }
    $message = isset($options['message']) ? $options['message'] : __('Please enter a value of %d or less');
    return sprintf($message, $options['max']);
  }

  /**
  * 最小値チェック
  * @param string $value
  * @param array $options [*min] 最小値
  */
  public static function min($value, $options)
  {
    if ($value >= $options['min']) {
      return true;
    }
    $message = isset($options['message']) ? $options['message'] : __('Please enter a value of %d or more');
    return sprintf($message, $options['min']);
  }

  /**
  * 日時チェック
  */
  public static function datetime($value, $options)
  {
    $format = isset($options['format']) ? $options['format'] : '%Y-%m-%d %H:%M:%S';
    if (strptime($value, $format)===false || strtotime($value)===false) {
      return isset($options['message']) ? $options['message'] : __('Please enter the date and time');
    }
    return true;
  }

  /**
  * メールアドレスチェック
  */
  public static function email($value, $options)
  {
    if (preg_match('/^(?:(?:(?:(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+)(?:\.(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+))*)|(?:"(?:\\[^\r\n]|[^\\"])*")))\@(?:(?:(?:(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+)(?:\.(?:[a-zA-Z0-9_!#\$\%&\'*+\/=?\^`{}~|\-]+))*)|(?:\[(?:\\\S|[\x21-\x5a\x5e-\x7e])*\])))$/', $value)) {
      return true;
    }
    return isset($options['message']) ? $options['message'] : __('Please enter your e-mail address');
  }

  /**
  * URLチェック
  */
  public static function url($value, $options)
  {
    if (preg_match('/^(https?|ftp)(:\/\/[-_.!~*\'()a-zA-Z0-9;\/?:\@&=+\$,%#]+)$/', $value)) {
      return true;
    }
    return isset($options['message']) ? $options['message'] : __('Please enter the URL');
  }

  /**
  * ユニークチェック
  */
  public static function unique($value, $options, $key, $data, $model)
  {
    if (!$model->isExist(array('where'=>$key . '=?', 'params'=>array($value)))) {
      return true;
    }
    return isset($options['message']) ? $options['message'] : __('Is already in use');
  }


  /**
  * 配列に存在する値かチェック
  */
  public static function in_array($value, $options)
  {
    if (is_scalar($value)) {
      $tmp = array_flip($options['values']);
      if (isset($tmp[$value])) {
        return true;
      }
    }
    return isset($options['message']) ? $options['message'] : __('Value that does not exist has been entered');
  }


  /**
  * Fileチェック
  */
  public static function file($value, $option)
  {
    switch ($value['error']) {
      case UPLOAD_ERR_OK: break;  // OK

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
      $mime_type = null;
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
  */
  public static function own(&$value, $option, $key, $data, $model)
  {
    $method = $option['method'];
    return $model->$method($value, $option, $key, $data, $model);
  }


  /**
  * 配列チェック関数
  */
  public static function multiple(&$values, $valid, $k, $d, $model)
  {
    if (!is_array($values)) {
      $values = array();
    }
    foreach ($valid as $mKey => $options) {
      $method = is_array($options) && isset($options['rule']) ? $options['rule'] : $mKey;
      foreach ($values as $key => $value) {
        $error = Validate::$method($values[$key], $options, $key, $values, $model);
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
  */
  public static function trim(&$value)
  {
    if (is_string($value)) {
      $value = preg_replace("/(^\s+)|(\s+$)/us", "", $value);
    }
    return true;
  }

  /**
  * int型に変換
  */
  public static function int(&$value)
  {
    $value = intval($value);
    return true;
  }

  /**
  * 小文字に変換
  */
  public static function strtolower(&$value)
  {
    $value = strtolower($value);
    return true;
  }

  /**
  * 配列内重複排除
  */
  public static function array_unique(&$values, $options)
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
  */
  public static function default_value(&$value, $default)
  {
    if ($value===null || $value==="") {
      $value = $default;
    }
    return true;
  }

  /**
  * データを置き換える
  */
  public static function replace(&$value, $replaces)
  {
    $value = str_replace(array_keys($replaces), array_values($replaces), $value);
    return true;
  }

}

