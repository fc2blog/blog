<?php

namespace Fc2blog\Model;

class FilesModel extends \Fc2blog\Model\Model{

  public static $instance = null;

  public function __construct(){}

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new FilesModel();
    }
    return self::$instance;
  }

  public function getTableName()
  {
    return 'files';
  }

  public function getAutoIncrementCompositeKey()
  {
    return 'blog_id';
  }

  public function fileValidate($file, $request, &$valid_data)
  {
    // データの整形
    if (isset($file['file']['name'])) {
      $name = $file['file']['name'];
      $file['name'] = $name;
      $file['ext'] = strtolower(substr($name, strrpos($name, '.') + 1));
    }
    if (isset($request['name']) && $request['name']!=='') {
      $file['name'] = $request['name'];
    }
    $errors = $this->validate($file, $valid_data, array('name', 'ext', 'file'));

    // 必要以外の内容は削除
    $valid_data['tmp_name'] = $valid_data['file']['tmp_name'];
    unset($valid_data['file']);

    return $errors;
  }

  /**
  * 追加用のバリデート処理
  */
  public function insertValidate($file, $request, &$valid_data)
  {
    // バリデートを定義
    $this->validates = array(
      'name'  => array(
        'maxlength' => array('max' => 100),
      ),
      'ext'   => array(
        'in_array' => array(
          'values'   => array('jpg', 'jpeg', 'png', 'gif'),
          'message'  => __('Please upload an image file'),
        ),
      ),
      'file' => array(
        'file' => array(
          'required'  => true,
          'mime_type' => array('image/jpeg', 'image/png', 'image/gif'),
          'size'      => \Fc2blog\Config::get('FILE.MAX_SIZE'),
        ),
      ),
    );
    return $this->fileValidate($file, $request, $valid_data);
  }

  /**
  * 更新用のバリデート処理
  */
  public function updateValidate($file, $request, $original_file, &$valid_data)
  {
    // バリデートを定義
    $this->validates = array(
      'name'  => array(
        'maxlength' => array('max' => 100),
      ),
      'ext'   => array(
        'required'  => false,
        'in_array'  => array(
          'values'    => array('jpg', 'jpeg', 'png', 'gif'),
          'message'   => __('Please upload an image file'),
        ),
      ),
      'file' => array(
        'file' => array(
          'mime_type' => array('image/jpeg', 'image/png', 'image/gif'),
          'size'      => \Fc2blog\Config::get('FILE.MAX_SIZE'),
        ),
      ),
    );
    $errors = $this->fileValidate($file, $request, $valid_data);
    if (empty($errors) && !empty($file['file']['tmp_name'])) {
      // アップロード画像と元画像の拡張子が違う場合
      if ($valid_data['ext']!=$original_file['ext']) {
        $errors['ext'] = __('Extension and the original file is different');
        return $errors;
      }
    }
    if (empty($errors) && empty($valid_data['tmp_name'])) {
      unset($valid_data['tmp_name'], $valid_data['ext']);
    }
    return $errors;
  }

  public function insert($data, $options=array())
  {
    $data['updated_at'] = $data['created_at'] = date('Y-m-d H:i:s');
    return parent::insert($data, $options);
  }

  public function updateByIdAndBlogId($data, $id, $blog_id, $options=array())
  {
    // 更新日時を設定
    $data['updated_at'] = date('Y-m-d H:i:s');

    return parent::updateByIdAndBlogId($data, $id, $blog_id, $options);
  }

  /**
  * File情報から削除する(実ファイル込み)
  */
  public function deleteByObject($file)
  {
    $flag = parent::deleteByIdAndBlogId($file['id'], $file['blog_id']);

    // ファイル削除
    if ($flag) {
      \Fc2blog\App::deleteFile($file['blog_id'], $file['id']);
    }

    return $flag;
  }

  /**
  * blogidと複数IDをキーとした削除
  */
  public function deleteByIdsAndBlogId($ids=array(), $blog_id)
  {
    // 単体ID対応
    if (is_numeric($ids)) {
      $ids = array($ids);
    }
    // 数値型配列チェック
    if (!$this->is_numeric_array($ids)) {
      return false;
    }

    // 削除対象のファイルを取得(ファイル情報を使用する為取得)
    $files = $this->find('all', array(
      'where'  => 'blog_id=? AND id IN (' . implode(',',array_fill(0, count($ids), '?')) . ')',
      'params' => array_merge(array($blog_id), $ids),
    ));
    if (!count($files)) {
      return false;
    }

    // 削除処理(TODO:時間ができれば処理の最適化を行う)
    $flag = true;
    foreach ($files as $file) {
      $flag = $flag && $this->deleteByObject($file);
    }
    return $flag;
  }

}

