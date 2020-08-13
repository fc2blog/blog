<?php

class BlogsModel extends \Fc2blog\Model\Model
{

  public static $instance = null;

  public function __construct(){}

  public static function getInstance()
  {
    if (!self::$instance) {
      self::$instance = new BlogsModel();
    }
    return self::$instance;
  }

  public function getTableName()
  {
    return 'blogs';
  }

  /**
  * プライベートモード時のパスワード必須チェック
  */
  public static function privateCheck($value, $valid, $k, $d)
  {
    if ($value==null || $value==='') {
      if ($d['open_status']==\Fc2blog\Config::get('BLOG.OPEN_STATUS.PRIVATE')) {
        return __('Please Be sure to set the password if you want to private');
      }
    }
    return true;
  }

  /**
  * ディレクトリとして使用済みかどうか
  */
  public static function useDirectory($value)
  {
    if (is_dir(\Fc2blog\Config::get('WWW_DIR') . $value)) {
      return __('Is already in use');
    }
    return true;
  }

  /**
  * バリデート処理
  */
  public function validate($data, &$valid_data, $white_list=array())
  {
    // バリデートを定義
    $this->validates = array(
      'id' => array(
        'required'     => true,
        'minlength'    => array('min' => 3),
        'maxlength'    => array('max' => 50),
        'alphanumeric' => array(),
        'strtolower'   => array(),
        'own'          => array('method' => 'useDirectory'),
        'unique'       => array(),
      ),
      'name' => array(
        'required'  => true,
        'minlength' => array('min' => 1),
        'maxlength' => array('max' => 50),
      ),
      'introduction' => array(
        'maxlength' => array('max' => 200),
      ),
      'nickname' => array(
        'required' => true,
        'trim' => true,
        'minlength' => array('min' => 1),
        'maxlength' => array('max' => 255),
      ),
      'timezone' => array(
        'required'  => true,
        'in_array'  => array('values'=>array_values(DateTimeZone::listIdentifiers())),
      ),
      'open_status' => array(
        'in_array' => array('values'=>array_keys($this->getOpenStatusList())),
      ),
      'ssl_enable' => array(
        'in_array' => array('values'=>array_keys($this->getSSLEnableSettingList())),
      ),
      'redirect_status_code' => array(
        'in_array' => array('values'=>array_keys($this->getRedirectStatusCodeSettingList())),
      ),
      'blog_password' => array(
        'maxlength' => array('max' => 50),
        'own'       => array('method' => 'privateCheck'),
      ),
    );

    return parent::validate($data, $valid_data, $white_list);
  }

  /**
   * Blog idとして適切か？ static::validate()より転写
   * @param string $blog_id
   * @return bool
   */
  public static function isValidBlogId(string $blog_id)
  {
    if (\Fc2blog\Model\Validate::alphanumeric($blog_id, []) !== true) return false;
    if (\Fc2blog\Model\Validate::minlength($blog_id, ['min' => 3]) !== true) return false;
    if (\Fc2blog\Model\Validate::maxlength($blog_id, ['max' => 50]) !== true) return false;
    if (strtolower($blog_id) !== $blog_id) return false;
    return true;
  }

  /**
  * ブログの公開状態のリストを取得
  */
  public static function getOpenStatusList()
  {
    return array(
        \Fc2blog\Config::get('BLOG.OPEN_STATUS.PUBLIC')  => __('Public'),
        \Fc2blog\Config::get('BLOG.OPEN_STATUS.PRIVATE') => __('Private'),
    );
  }

  /**
  * タイムゾーン一覧
  */
  public static function getTimezoneList()
  {
    $timezone_identifiers = DateTimeZone::listIdentifiers();
    $timezone = array();
    foreach ($timezone_identifiers as $value) {
      $keys = explode('/', $value);
      $group = __(array_shift($keys));
      if (count($keys)) {
        $label = __(implode(' ', $keys));
      } else {
        $label = $group;
      }
      if (empty($timezone[$group])) {
        $timezone[$group] = array();
      }
      $timezone[$group][$value] = $label;
    }
    return $timezone;
  }

  /**
   * ブログのSSL 有効、無効
   */
  public static function getSSLEnableSettingList(): array
  {
    return array(
      \Fc2blog\Config::get('BLOG.SSL_ENABLE.DISABLE') => __("Disable"),
      \Fc2blog\Config::get('BLOG.SSL_ENABLE.ENABLE') => __("Enable"),
    );
  }

  /**
   * フルURLでリダイレクト時のステータスコード
   */
  public static function getRedirectStatusCodeSettingList(): array
  {
    return array(
      \Fc2blog\Config::get('BLOG.REDIRECT_STATUS_CODE.MOVED_PERMANENTLY') => __("Moved Permanently"),
      \Fc2blog\Config::get('BLOG.REDIRECT_STATUS_CODE.FOUND') => __("Found"),
    );
  }

  /**
  * 現在使用中のテンプレートID一覧を取得する
  */
  public static function getTemplateIds($blog){
    $columns = \Fc2blog\Config::get('BLOG_TEMPLATE_COLUMN');
    return array($blog[$columns[1]], $blog[$columns[2]], $blog[$columns[4]], $blog[$columns[8]]);
  }

  /**
  * ブログの一覧(SelectBox用)
  */
  public function getSelectList($user_id)
  {
    return $this->find('list', array(
      'fields' => array('id', 'name'),
      'where'  => 'user_id=?',
      'params' => array($user_id),
      'order'  => 'created_at DESC',
    ));
  }

  /**
  * ブログIDをキーにユーザーIDを条件としてブログを取得
  */
  public function findByIdAndUserId($blog_id, $user_id, $options=array())
  {
    $options['where'] = isset($options['where']) ? 'id=? AND user_id=? AND ' . $options['where'] : 'id=? AND user_id=?';
    $options['params'] = isset($options['params']) ? array_merge(array($blog_id, $user_id), $options['params']) : array($blog_id, $user_id);
    return $this->find('row', $options);
  }

  /**
  * ユーザーIDをキーにしてブログを取得
  */
  public function findByUserId ($user_id, $options=array())
  {
    $options['where'] = isset($options['where']) ? 'user_id=? AND ' . $options['where'] : 'user_id=?';
    $options['params'] = isset($options['params']) ? array_merge(array($user_id), $options['params']) : array($user_id);
    return $this->find('all', $options);
  }

  /**
  * ランダムに１件ブログを取得する
  */
  public function findByRandom()
  {
    $options = array(
      'order' => 'RAND()',
    );
    return $this->find('row', $options);
  }

  /**
  * ログイン後最初に表示するブログを取得する
  */
  public function getLoginBlog($user){
    // ログイン後のブログIDが設定されていれば対象のブログを取得
    if (!empty($user['login_blog_id'])) {
      $blog = $this->find('row', array(
        'where'  => 'id=? AND user_id=?',
        'params' => array($user['login_blog_id'], $user['id']),
      ));
      if ($blog) {
        return $blog;
      }
    }
    // 未設定の場合最後に作ったブログを取得
    return $this->find('row', array(
      'where'  => 'user_id=?',
      'params' => array($user['id']),
      'order'  => 'created_at DESC',
    ));
  }

  /**
  * ユーザーIDをキーにブログのリストを取得
  */
  public function getListByUserId($user_id)
  {
    return $this->find('list', array(
      'fields' => array('id', 'name'),
      'where'  => 'user_id=?',
      'params' => array($user_id),
    ));
  }

  /**
  * ユーザーが対象のブログIDを所持しているかチェック
  */
  public function isUserHaveBlogId($user_id, $blog_id){
    static $is_list = array();    // キャッシュ用

    $key = $user_id . '_' . $blog_id;
    if (isset($is_list[$key])) {
      return $is_list[$key];
    }
    return $is_list[$key] = $this->isExist(array(
      'fields' => 'id',
      'where'  => 'id=? AND user_id=?',
      'params' => array($blog_id, $user_id),
    ));
  }

  /**
  * ブログの追加登録処理
  *  ブログの作成と同時にCategoryのRootNodeの追加も行う
  */
  public function insert($data, $options=array())
  {
    // 主キーがauto_incrementじゃないのでreturn値の受け取り方を変更
    $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
    $options['result'] = \Fc2blog\Model\DBInterface::RESULT_SUCCESS;
    if (!parent::insert($data, $options)) {
      return false;
    }
    $id = $data['id'];

    // CategoryのSystem用Nodeの追加(id=1の削除できないノード)
    $data = array('name'=>__('Unclassified'), 'blog_id'=>$id);
    \Fc2blog\Model\Model::load('Categories')->addNode($data, 'blog_id=?', array($id));

    // ブログ用の設定作成
    \Fc2blog\Model\Model::load('BlogSettings')->insert(array('blog_id'=>$id));

    // 初期のテンプレートを作成する(pc,mb,sp,tb)
    $blog_templates_model = \Fc2blog\Model\Model::load('BlogTemplates');

    $blog_data = array();

    $devices = array(
      'template_pc_id' => \Fc2blog\Config::get('DEVICE_PC'),
      'template_mb_id' => \Fc2blog\Config::get('DEVICE_MB'),
      'template_sp_id' => \Fc2blog\Config::get('DEVICE_SP'),
      'template_tb_id' => \Fc2blog\Config::get('DEVICE_TB'),
    );
    $blog_templates_data = array(
      'blog_id'     => $id,
      'template_id' => 0,
      'title'       => '初期テンプレート',
    );
    foreach ($devices as $key => $device) {
      // TODO:ファイルではなくテンプレのDBから呼び出す or ユーザーに選択させる予定
      $template_name = 'fc2_default_template' . \Fc2blog\Config::get('DEVICE_PREFIX.' . $device) . '.php';
      $css_name = 'fc2_default_css' . \Fc2blog\Config::get('DEVICE_PREFIX.' . $device) . '.php';
      if (file_exists(\Fc2blog\Config::get('CONFIG_DIR') . $template_name) && file_exists(\Fc2blog\Config::get('CONFIG_DIR') . $css_name)) {
        $blog_templates_data['html'] = file_get_contents(\Fc2blog\Config::get('CONFIG_DIR') . $template_name);
        $blog_templates_data['css'] = file_get_contents(\Fc2blog\Config::get('CONFIG_DIR') . $css_name);
      } else {
        $blog_templates_data['html'] = file_get_contents(\Fc2blog\Config::get('CONFIG_DIR') . 'fc2_default_template.php');
        $blog_templates_data['css'] = file_get_contents(\Fc2blog\Config::get('CONFIG_DIR') . 'fc2_default_css.php');
      }

      $blog_templates_data['device_type'] = $device;
      $blog_templates_data['created_at'] = $blog_templates_data['updated_at'] = date('Y-m-d H:i:s');
      $blog_data[$key] = $blog_templates_model->insert($blog_templates_data);
    }
    if (!$this->update($blog_data, 'id=?', array($id))) {
      return false;
    }
    return $id;
  }

  /**
  * idをキーとした更新
  */
  public function updateById($values, $id, $options=array())
  {
    $values['updated_at'] = date('Y-m-d H:i:s');
    return parent::updateById($values, $id, $options);
  }

  /**
  * 最終投稿日時更新
  */
  public function updateLastPostedAt($id)
  {
    $values = array('last_posted_at' => date('Y-m-d H:i:s'));
    return parent::updateById($values, $id, array());
  }

  /**
  * テンプレートの切り替え
  */
  public function switchTemplate($blog_template, $blog_id)
  {
    $device_type = $blog_template['device_type'];

    // 使用テンプレートを更新
    $data = array();
    $data[\Fc2blog\Config::get('BLOG_TEMPLATE_COLUMN.' . $device_type)] = $blog_template['id'];

    // コメントの表示タイプをテンプレートから判断
    $reply_type = strstr($blog_template['html'], '<%comment_reply_body>') ?
      \Fc2blog\Config::get('BLOG_TEMPLATE.COMMENT_TYPE.REPLY') : \Fc2blog\Config::get('BLOG_TEMPLATE.COMMENT_TYPE.AFTER');
    // コメントの表示タイプを更新
    \Fc2blog\Model\Model::load('BlogSettings')->updateReplyType($device_type, $reply_type, $blog_id);

    $ret = $this->updateById($data, $blog_id);

    if ($ret) {
      // 更新に成功した場合 現在のテンプレートを削除
      \Fc2blog\Model\Model::load('BlogTemplates');
      $template_path = BlogTemplatesModel::getTemplateFilePath($blog_id, $device_type);
      is_file($template_path) && unlink($template_path);
      $css_path = BlogTemplatesModel::getCssFilePath($blog_id, $device_type);
      is_file($css_path) && unlink($css_path);
    }

    return $ret;
  }

  /**
  * ブログの削除処理
  */
  public function deleteByIdAndUserId($blog_id, $user_id, $options=array())
  {
    if (!parent::deleteById($blog_id, array('where'=>'user_id=?', 'params'=>array($user_id)), $options)){
      return 0;
    }

    // ブログに関連するレコード全て削除
    $tables = array(
      'entries', 'entry_tags', 'tags', 'entry_categories', 'categories', 'comments',
      'files', 'blog_settings', 'blog_templates'
    );
    foreach($tables as $table){
      $sql = 'DELETE FROM ' . $table . ' WHERE blog_id=?';
      $this->executeSql($sql, array($blog_id));
    }

    // ブログディレクトリー削除
    \Fc2blog\App::removeBlogDirectory($blog_id);
    return true;
  }

  /**
   * 指定したテンプレートIDが指定したブログIDのデバイステンプレートとして適用されているか判定する
   * @param $template_id
   * @param $blog_id
   * @param $device_type
   * @return bool
   */
  public function isAppliedTemplate($template_id, $blog_id, $device_type): bool
  {
    $blog_template_column = \Fc2blog\Config::get("BLOG_TEMPLATE_COLUMN.{$device_type}");
    $blogs = $this->findById($blog_id);

    $isAppliedTemplate = (
      isset($blogs[$blog_template_column]) &&
      $blogs[$blog_template_column] == $template_id
    );

    return $isAppliedTemplate;
  }

  /**
   * Blog 設定が今アクセスしているSchemaと一致しているか確認
   * @param array $blog blog array
   * @return bool
   */
  static public function isCorrectHttpSchemaByBlogArray(array $blog): bool
  {
    $is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on');
    return ($blog['ssl_enable'] === 1 && $is_https) || ($blog['ssl_enable'] === 0 && !$is_https);
  }

  /**
   * Blog 設定が今アクセスしているSchemaと一致しているか確認
   * @param string $blog_id
   * @return bool
   */
  static public function isCorrectHttpSchemaByBlogId(string $blog_id): bool
  {
    $is_https = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
    $schema = static::getSchemaByBlogId($blog_id);
    return ($schema === "http:" && $is_https===false) || ($schema === "https:" && $is_https===true);
  }

  /**
   * Blog Idをキーとして、そのブログの`http(s)://FQDN(:port)`を生成する
   * @param string $blog_id
   * @param null $domain 省略時、\Fc2blog\Config::get("DOMAIN")
   * @return string
   */
  static public function getFullHostUrlByBlogId(string $blog_id, $domain=null){
    $schema = static::getSchemaByBlogId($blog_id);
    if(is_null($domain)) {
      $domain = \Fc2blog\Config::get("DOMAIN");
    }
    $port = ($schema === "https:") ? \Fc2blog\Config::get("HTTPS_PORT_STR") : \Fc2blog\Config::get("HTTP_PORT_STR");
    return $schema . "//" . $domain . $port;
  }

  /**
   * Blog Idをキーとして、そのブログのssl_enable設定からリンク時のSchemaを決定する
   * @param string $blog_id
   * @return string
   */
  static public function getSchemaByBlogId(string $blog_id){
    if(!static::isValidBlogId($blog_id)) throw new InvalidArgumentException("invalid blog id :{$blog_id}");
    $blogs_model = static::getInstance();
    $blog_array = $blogs_model->findById($blog_id);

    if(!is_array($blog_array) || !isset($blog_array['ssl_enable'])) {
      throw new InvalidArgumentException("blog id `{$blog_id}` notfound.");
    }

    return static::getSchemaBySslEnableValue($blog_array['ssl_enable']);
  }

  /**
   * Valueをキーとして、そのブログのssl_enable設定からリンク時のSchemaを決定する
   * @param int $value
   * @return string
   */
  static public function getSchemaBySslEnableValue(int $value){
    return ($value === \Fc2blog\Config::get("BLOG.SSL_ENABLE.DISABLE")) ? 'http:' : 'https:';
  }

  /**
   * Blog Idをキーとして、そのブログのssl_enable設定からリンク時のSchemaを決定する
   * @param string $blog_id
   * @return int
   */
  static public function getRedirectStatusCodeByBlogId(string $blog_id):int{
    if(!static::isValidBlogId($blog_id)) throw new InvalidArgumentException("invalid blog id :{$blog_id}");
    $blogs_model = static::getInstance();
    $blog_array = $blogs_model->findById($blog_id);

    if(!is_array($blog_array) || !isset($blog_array['redirect_status_code'])) {
      throw new InvalidArgumentException("blog id `{$blog_id}` notfound.");
    }

    return $blog_array['redirect_status_code'];
  }

}

