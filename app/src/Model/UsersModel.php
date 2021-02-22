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

  public function getTableName():string
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
  public function registerValidate(array $data, ?array &$valid_data=[], $white_list=[])
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
  public function updateValidate(array $data, ?array &$valid_data=[], $white_list=[])
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
  public function loginValidate(array $data, ?array &$valid_data=[], $white_list=[])
  {
    // Validateの設定
    $this->setValidate($white_list);

    return $this->validate($data, $valid_data, $white_list);
  }


  /**
   * ユーザーパスワード用のハッシュを作成
   * @param string $password
   * @return string
   */
  public static function passwordHash(string $password): string
  {
    return password_hash($password, PASSWORD_DEFAULT);
  }

  /**
   * ログインIDとパスワードからユーザーを取得する
   * @param $id
   * @param $password
   * @return mixed
   */
  public function findByLoginIdAndPassword($id, $password)
  {
    $options = [
      'where' => 'login_id=?',
      'params' => [$id],
    ];
    $user = $this->find('row', $options);
    var_dump(__FILE__ . ":" . __LINE__);
    var_dump($user);
    var_dump(DB_CONNECT_LIB);

    if (is_array($user) && count($user) > 0 && password_verify($password, $user['password'])) {
      return $user;
    } else {
      return false;
    }
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
    $blogs_model = new BlogsModel();

    // ユーザーが所持しているブログがあればすべて削除
    $blogs = $blogs_model->findByUserId($user_id);

    if(is_array($blogs)) {
      foreach ($blogs as $blog) {
        $blogs_model->deleteByIdAndUserId($blog['id'], $user_id);
      }
    }

    return parent::deleteById($user_id, $options);
  }
}
