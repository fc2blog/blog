<?php

namespace Fc2blog\Model;

use Fc2blog\Config;
use Fc2blog\Web\Session;

class UsersModel extends Model
{

  public static $instance = null;

  public function __construct(){}

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new UsersModel();
    }
    return self::$instance;
  }

  public function getTableName()
  {
    return 'users';
  }

  /**
   * バリデートを設定
   * @param $white_list
   */
  private function setValidate($white_list)
  {
    $this->validates = array(
      'login_id' => array(
        'required' => true,
        'trim' => true,
        'minlength' => array('min' => 6),
        'maxlength' => array('max' => 50),
      ),
      'password' => array(
        'required' => true,
        'minlength' => array('min' => 6),
        'maxlength' => array('max' => 50),
      ),
    );
    if (in_array('login_blog_id', $white_list)) {
      $this->validates['login_blog_id'] = array(
        'in_array' => array('values'=>array_keys(Model::load('Blogs')->getListByUserId(Session::get('user_id')))),
      );
    }
  }

  /**
   * 登録用のバリデート処理
   * @param $data
   * @param $valid_data
   * @param array $white_list
   * @return array
   */
  public function registerValidate($data, &$valid_data, $white_list=array())
  {
    // Validateの設定
    $this->setValidate($white_list);

    // Validateの追加設定
    $this->validates['login_id']['unique'] = array();   // ユニークチェックを追加

    $errors = $this->validate($data, $valid_data, $white_list);

    // Validateの後処理
    if (empty($errors)) {
      $valid_data['password'] = $this->passwordHash($valid_data['password']);
    }
    $valid_data['created_at'] = date('Y-m-d H:i:s');
    return $errors;
  }

  /**
   * 更新用のバリデート
   * @param $data
   * @param $valid_data
   * @param array $white_list
   * @return array
   */
  public function updateValidate($data, &$valid_data, $white_list=array())
  {
    // Validateの設定
    $this->setValidate($white_list);

    // Validateの追加設定
    if (isset($data['password']) && $data['password']=='') {
      // パスワード設定無しの場合は検証リストから外す
      $unsetKey = array_search('password', $white_list);
      if ($unsetKey!==false) {
        unset($white_list[$unsetKey]);
        $white_list = array_values($white_list);
      }
    }

    $errors = $this->validate($data, $valid_data, $white_list);

    // Validateの後処理
    if (empty($errors)) {
      if (!empty($valid_data['password'])) {
        $valid_data['password'] = $this->passwordHash($valid_data['password']);
      }
    }
    return $errors;
  }

  /**
   * ログイン用のバリデート処理
   * @param $data
   * @param $valid_data
   * @param array $white_list
   * @return array
   */
  public function loginValidate($data, &$valid_data, $white_list=array())
  {
    // Validateの設定
    $this->setValidate($white_list);

    return $this->validate($data, $valid_data, $white_list);
  }


  /**
   * ユーザーパスワード用のハッシュを作成
   * @param $password
   * @return string
   */
  public static function passwordHash($password)
  {
    return hash('sha256', $password . Config::get('PASSWORD_SALT'));
  }

  /**
   * ログインIDとパスワードからユーザーを取得する
   * @param $id
   * @param $password
   * @return mixed
   */
  public function findByLoginIdAndPassword($id, $password)
  {
    $options = array(
      'where'   => 'login_id=? AND password=?',
      'params'  => array($id, $this->passwordHash($password)),
    );
    return $this->find('row', $options);
  }

  /**
  * 管理者ユーザーが存在するかを取得する
  */
  public function isExistAdmin()
  {
    return $this->isExist(array(
      'where' => 'type=' . Config::get('USER.TYPE.ADMIN'),
    ));
  }

  /**
   * 削除処理(付随する情報も全て削除)
   * @param $user_id
   * @param array $options
   * @return array|false|int|mixed
   */
  public function deleteById($user_id, $options=array())
  {
    $blogs_model = Model::load('Blogs');

    // ユーザーが所持しているブログを全て削除
    $blogs = $blogs_model->findByUserId($user_id);
    foreach ($blogs as $blog) {
      $blogs_model->deleteByIdAndUserId($blog['id'], $user_id);
    }

    return parent::deleteById($user_id, $options);
  }

}

