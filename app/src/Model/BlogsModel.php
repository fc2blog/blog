<?php

namespace Fc2blog\Model;

use DateTimeZone;
use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Web\Request;
use InvalidArgumentException;
use OutOfBoundsException;
use PDOException;

class BlogsModel extends Model
{

    public static $instance = null;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new BlogsModel();
        }
        return self::$instance;
    }

    public function getTableName(): string
    {
        return 'blogs';
    }

    /**
     * プライベートモード時のパスワード必須チェック
     * @param $value
     * @param $option
     * @param $key
     * @param $data
     * @return bool|string
     * @noinspection PhpUnusedParameterInspection // $options and $key needs dynamic call
     */
    public static function privateCheck($value, $option, $key, $data)
    {
        if (
            $data['open_status'] == Config::get('BLOG.OPEN_STATUS.PRIVATE') &&
            (
                // パスワードを入力したか、あるいはすでにパスワード設定済みか
                strlen((string)$value) === 0 &&
                !static::isPasswordRegistered($data['_blog_id'])
            )
        ) {
            return __('Please Be sure to set the password if you want to private');
        } else {
            return true;
        }
    }

    /**
     * 指定blog idのブログのパスワードが設定済みか？
     * @param $blog_id
     * @return bool
     */
    public static function isPasswordRegistered($blog_id): bool
    {
        $blog = (new BlogsModel)->findById($blog_id);
        return (!empty($blog) && strlen($blog['blog_password']) > 0);
    }

    /**
     * ディレクトリとして使用済みかどうか
     * @param string $value
     * @return bool|string
     * @noinspection PhpUnused
     */
    public static function usableDirectory(string $value)
    {
        // adminは予約済み
        if ($value === "admin") {
            return __('Name that cannot be specified');
        }

        if (is_dir(App::WWW_DIR . $value)) {
            return __('Is already in use');
        }

        return true;
    }

    /**
     * バリデート処理
     * @param array $data
     * @param array|null $valid_data
     * @param array $white_list
     * @return array
     */
    public function validate(array $data, ?array &$valid_data = [], array $white_list = []): array
    {
        // バリデートを定義
        $this->validates = array(
            'id' => array(
                'required' => true,
                'minlength' => array('min' => 3),
                'maxlength' => array('max' => 50),
                'alphanumeric' => array(),
                'strtolower' => array(),
                'own' => array('method' => 'usableDirectory'),
                'unique' => array(),
            ),
            'name' => array(
                'required' => true,
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
                'required' => true,
                'in_array' => array('values' => array_values(DateTimeZone::listIdentifiers())),
            ),
            'open_status' => array(
                'in_array' => array('values' => array_keys($this->getOpenStatusList())),
            ),
            'ssl_enable' => array(
                'in_array' => array('values' => array_keys($this->getSSLEnableSettingList())),
            ),
            'redirect_status_code' => array(
                'in_array' => array('values' => array_keys($this->getRedirectStatusCodeSettingList())),
            ),
            'blog_password' => array(
                'maxlength' => array('max' => 50),
                'own' => array('method' => 'privateCheck'),
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
        if (Validate::alphanumeric($blog_id, []) !== true) return false;
        if (Validate::minlength($blog_id, ['min' => 3]) !== true) return false;
        if (Validate::maxlength($blog_id, ['max' => 50]) !== true) return false;
        if (strtolower($blog_id) !== $blog_id) return false;
        return true;
    }

    /**
     * ブログの公開状態のリストを取得
     * @return array
     */
    public static function getOpenStatusList(): array
    {
        return array(
            Config::get('BLOG.OPEN_STATUS.PUBLIC') => __('Public'),
            Config::get('BLOG.OPEN_STATUS.PRIVATE') => __('Private'),
        );
    }

    /**
     * タイムゾーン一覧
     * @return array
     */
    public static function getTimezoneList(): array
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
     * @return array
     */
    public static function getSSLEnableSettingList(): array
    {
        return array(
            Config::get('BLOG.SSL_ENABLE.DISABLE') => __("Disable"),
            Config::get('BLOG.SSL_ENABLE.ENABLE') => __("Enable"),
        );
    }

    /**
     * フルURLでリダイレクト時のステータスコード
     * @return array
     */
    public static function getRedirectStatusCodeSettingList(): array
    {
        return array(
            Config::get('BLOG.REDIRECT_STATUS_CODE.MOVED_PERMANENTLY') => __("Moved Permanently"),
            Config::get('BLOG.REDIRECT_STATUS_CODE.FOUND') => __("Found"),
        );
    }

    /**
     * 現在使用中のテンプレートID一覧を取得する
     * @param $blog
     * @return array
     */
    public static function getTemplateIds($blog): array
    {
        $columns = Config::get('BLOG_TEMPLATE_COLUMN');
        return [
            $blog[$columns[1]],
            $blog[$columns[4]],
        ];
    }

    /**
     * ブログの一覧(SelectBox用)
     * @param $user_id
     * @return array
     */
    public function getSelectList($user_id): array
    {
        try {
            return $this->find('list', array(
                'fields' => array('id', 'name'),
                'where' => 'user_id=?',
                'params' => array($user_id),
                'order' => 'created_at DESC',
            ));
        } catch (PDOException $e) {
            // インストール前や初期化前など、クエリが出来ないケースがある
            return [];
        }
    }

    /**
     * ブログIDをキーにユーザーIDを条件としてブログを取得
     * @param $id
     * @param $user_id
     * @param array $options
     * @return mixed
     */
    public function findByIdAndUserId($id, $user_id, array $options = []): array
    {
        $options['where'] = isset($options['where']) ? 'id=? AND user_id=? AND ' . $options['where'] : 'id=? AND user_id=?';
        $options['params'] = isset($options['params']) ? array_merge(array($id, $user_id), $options['params']) : array($id, $user_id);
        return $this->find('row', $options);
    }

    /**
     * ユーザーIDをキーにしてブログを取得
     * @param $user_id
     * @param array $options
     * @return array
     */
    public function findByUserId($user_id, array $options = array())
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
     * @param $user
     * @return array
     */
    public function getLoginBlog($user)
    {
        // ログイン後のブログIDが設定されていれば対象のブログを取得
        if (!empty($user['login_blog_id'])) {
            $blog = $this->find('row', array(
                'where' => 'id=? AND user_id=?',
                'params' => array($user['login_blog_id'], $user['id']),
            ));
            if ($blog) {
                return $blog;
            }
        }
        // 未設定の場合最後に作ったブログを取得
        return $this->find('row', array(
            'where' => 'user_id=?',
            'params' => array($user['id']),
            'order' => 'created_at DESC',
        ));
    }

    /**
     * ユーザーIDをキーにブログのリストを取得
     * @param int $user_id
     * @return array
     */
    public function getListByUserId(int $user_id)
    {
        return $this->find('list', array(
            'fields' => array('id', 'name'),
            'where' => 'user_id=?',
            'params' => array($user_id),
        ));
    }

    /**
     * ユーザーが対象のブログIDを所持しているかチェック
     * @param string $user_id
     * @param string $blog_id
     * @return bool
     */
    public function isUserHaveBlogId(string $user_id, string $blog_id): bool
    {
        return $this->isExist([
            'fields' => 'id',
            'where' => 'id=? AND user_id=?',
            'params' => [$blog_id, $user_id],
        ]);
    }

    /**
     * ブログの追加登録処理
     *  ブログの作成と同時にCategoryのRootNodeの追加も行う
     * TODO: トランザクションがないので、本関数が失敗してもデータが巻き戻らない
     * @param array $data
     * @param array $options
     * @return false|string falseは登録失敗
     */
    public function insert(array $data, array $options = [])
    {
        // 主キーがauto_incrementじゃないのでreturn値の受け取り方を変更
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');
        $options['result'] = PDOQuery::RESULT_SUCCESS;
        if (!parent::insert($data, $options)) {
            return false;
        }
        $id = $data['id'];

        // CategoryのSystem用Nodeの追加(id=1の削除できないノード)
        $data = ['name' => __('Unclassified'), 'blog_id' => $id];
        (new CategoriesModel())->addNode($data, 'blog_id=?', [$id]);

        // ブログ用の設定作成
        (new BlogSettingsModel())->insert(['blog_id' => $id]);

        // 初期のテンプレートを作成する(pc,sp)
        $blog_templates_model = new BlogTemplatesModel();

        $blog_data = [];

        $devices = [
            'template_pc_id' => Config::get('DEVICE_PC'),
            'template_sp_id' => Config::get('DEVICE_SP'),
        ];

        $blog_templates_data_common = [
            'blog_id' => $id,
            'template_id' => 0,
            'title' => '初期テンプレート',
        ];

        foreach ($devices as $key => $device) {
            $template_path = BlogTemplatesModel::getPathDefaultTemplateWithDevice($device);
            $css_path = BlogTemplatesModel::getPathDefaultCssWithDevice($device);

            if (!file_exists($template_path) || !file_exists($css_path)) {
                // 指定のデバイスに対応するテンプレーが無いので、PC用にフォールバック
                $template_path = BlogTemplatesModel::getPathDefaultTemplate();
                $css_path = BlogTemplatesModel::getPathDefaultCss();
            }

            $blog_templates_data['html'] = file_get_contents($template_path);
            $blog_templates_data['css'] = file_get_contents($css_path);
            $blog_templates_data['device_type'] = $device;
            $blog_templates_data['created_at'] = $blog_templates_data['updated_at'] = date('Y-m-d H:i:s');
            $blog_data[$key] = $blog_templates_model->insert(array_merge($blog_templates_data_common, $blog_templates_data));
        }

        if (!$this->update($blog_data, 'id=?', [$id])) {
            return false;
        }

        return $id;
    }

    /**
     * 指定のblogを初期テンプレートにリセット
     * @param string $blog_id
     */
    public function resetToDefaultTemplateByBlogId(string $blog_id): void
    {
        $blogs_model = new BlogsModel();
        $blog = $blogs_model->findById($blog_id);

        // 初期のテンプレートを作成する(pc,sp)
        $blog_templates_model = new BlogTemplatesModel();

        $blog_templates_data_common = [
            'blog_id' => $blog['id'],
            'template_id' => 0,
            'title' => '初期テンプレート',
        ];

        $devices_flipped = array_flip([
            'template_pc_id' => Config::get('DEVICE_PC'),
            'template_sp_id' => Config::get('DEVICE_SP'),
        ]);

        $blog_templates = $blog_templates_model->getTemplatesOfDevice($blog_id);

        $blog_templates_device_id_list = array_keys($blog_templates);

        foreach ($blog_templates_device_id_list as $device_id) {
            foreach ($blog_templates[$device_id] as $template) {
                if ($template['title'] !== '初期テンプレート') continue;

                $template_path = BlogTemplatesModel::getPathDefaultTemplateWithDevice($device_id);
                $css_path = BlogTemplatesModel::getPathDefaultCssWithDevice($device_id);

                if (!file_exists($template_path) || !file_exists($css_path)) {
                    // 指定のデバイスに対応するテンプレーが無いので、PC用にフォールバック
                    $template_path = BlogTemplatesModel::getPathDefaultTemplate();
                    $css_path = BlogTemplatesModel::getPathDefaultCss();
                }

                $template['html'] = file_get_contents($template_path);
                $template['css'] = file_get_contents($css_path);

                // テンプレートデータをアップデート
                $blog_data[$devices_flipped[$device_id]] = $blog_templates_model->updateByIdAndBlogId(
                    array_merge($blog_templates_data_common, $template),
                    $template['id'],
                    $blog['id']
                );

                if (!$blogs_model->isAppliedTemplate($template['id'], $blog_id, $device_id)) {
                    // 初期テンプレートが適用ではないので、初期テンプレートを適用する
                    $blogs_model->switchTemplate(
                        array_merge($blog_templates_data_common, $template),
                        $blog_id
                    );
                }
            }
        }

        static::regeneratePluginPhpByBlogId($blog_id);
    }

    /**
     * PluginのPHPコードをDBから再生成する
     * @param string $blog_id
     */
    public static function regeneratePluginPhpByBlogId(string $blog_id): void
    {
        // pluginのPHPコードを再生成する(PC)
        $blog_plugins_model = new BlogPluginsModel();
        $category_blog_plugins = $blog_plugins_model->getCategoryPlugins($blog_id, Config::get("DEVICE_PC"));

        foreach ($category_blog_plugins as $plugins) { // カテゴリ毎
            foreach ($plugins as $plugin) { // プラグイン毎
                $blog_plugins_model::createPlugin($plugin['contents'], $blog_id, $plugin['id']);
            }
        }

        // pluginのPHPコードを再生成する(SP)
        $category_blog_plugins = $blog_plugins_model->getCategoryPlugins($blog_id, Config::get("DEVICE_SP"));

        foreach ($category_blog_plugins as $plugins) { // カテゴリ毎
            foreach ($plugins as $plugin) { // プラグイン毎
                $blog_plugins_model::createPlugin($plugin['contents'], $blog_id, $plugin['id']);
            }
        }
    }

    /**
     * idをキーとした更新
     * @param array $values
     * @param $id
     * @param array $options
     * @return array|int
     */
    public function updateById(array $values, $id, array $options = [])
    {
        $values['updated_at'] = date('Y-m-d H:i:s');
        return parent::updateById($values, $id, $options);
    }

    /**
     * 最終投稿日時更新
     * @param $id
     * @return array|int
     * @noinspection PhpUnused
     */
    public function updateLastPostedAt($id)
    {
        $values = array('last_posted_at' => date('Y-m-d H:i:s'));
        return parent::updateById($values, $id, array());
    }

    /**
     * テンプレートの切り替え
     * @param array $blog_template
     * @param string $blog_id
     * @return array|int
     */
    public function switchTemplate(array $blog_template, string $blog_id)
    {
        $device_type = $blog_template['device_type'];

        // 使用テンプレートを更新
        $data = array();
        $data[Config::get('BLOG_TEMPLATE_COLUMN.' . $device_type)] = $blog_template['id'];

        // コメントの表示タイプをテンプレートから判断
        $reply_type = strstr($blog_template['html'], '<%comment_reply_body>') ?
            Config::get('BLOG_TEMPLATE.COMMENT_TYPE.REPLY') : Config::get('BLOG_TEMPLATE.COMMENT_TYPE.AFTER');
        // コメントの表示タイプを更新
        (new BlogSettingsModel())->updateReplyType($device_type, $reply_type, $blog_id);

        $ret = $this->updateById($data, $blog_id);

        if ($ret) {
            // 更新に成功した場合 現在のテンプレートを削除
            $template_path = BlogTemplatesModel::getTemplateFilePath($blog_id, $device_type);
            is_file($template_path) && unlink($template_path);
            $css_path = BlogTemplatesModel::getCssFilePath($blog_id, $device_type);
            is_file($css_path) && unlink($css_path);
        }

        return $ret;
    }

    /**
     * ブログの削除処理
     * @param string $blog_id
     * @param int $user_id
     * @param array $options
     * @return bool|int
     */
    public function deleteByIdAndUserId($blog_id, int $user_id, array $options = array())
    {
        if (!parent::deleteById($blog_id, array('where' => 'user_id=?', 'params' => array($user_id)))) {
            return 0;
        }

        // ブログに関連するレコード全て削除
        $tables = [
            'entries',
            'entry_tags',
            'tags',
            'entry_categories',
            'categories',
            'comments',
            'files',
            'blog_settings',
            'blog_templates'
        ];
        foreach ($tables as $table) {
            /** @noinspection SqlResolve */
            $sql = 'DELETE FROM ' . $table . ' WHERE blog_id=?';
            $this->executeSql($sql, array($blog_id));
        }

        // ブログディレクトリー削除
        App::removeBlogDirectory($blog_id);
        return true;
    }

    /**
     * 指定したテンプレートIDが指定したブログIDのデバイステンプレートとして適用されているか判定する
     * @param int $template_id
     * @param string $blog_id
     * @param int $device_type
     * @return bool
     */
    public function isAppliedTemplate(int $template_id, string $blog_id, int $device_type): bool
    {
        try {
            $applied_template_id = static::getAppliedTemplateId($blog_id, $device_type);
            return $applied_template_id == $template_id;
        } catch (OutOfBoundsException $e) {
            return false;
        }
    }

    /**
     * 指定したブログID,デバイスIDのテンプレートIDを取得する
     * @param string $blog_id
     * @param int $device_type
     * @return int
     */
    public function getAppliedTemplateId(string $blog_id, int $device_type): int
    {
        $blog_template_column = Config::get("BLOG_TEMPLATE_COLUMN.{$device_type}");
        $blogs = $this->findById($blog_id);

        if (!isset($blogs[$blog_template_column])) throw new OutOfBoundsException("any applied template found");

        return $blogs[$blog_template_column];
    }

    /**
     * Blog 設定が今アクセスしているSchemaと一致しているか確認
     * @param Request $request
     * @param Blog $blog blog array
     * @return bool
     */
    static public function isCorrectHttpSchemaByBlog(Request $request, Blog $blog): bool
    {
        $is_https = (isset($request->server['HTTPS']) && $request->server['HTTPS'] == 'on');
        return ($blog->ssl_enable === 1 && $is_https) || ($blog->ssl_enable === 0 && !$is_https);
    }

    /**
     * Blog 設定が今アクセスしているSchemaと一致しているか確認
     * @param Request $request
     * @param string $blog_id
     * @return bool
     * @noinspection PhpUnused
     */
    static public function isCorrectHttpSchemaByBlogId(Request $request, string $blog_id): bool
    {
        $is_https = (isset($request->server['HTTPS']) && $request->server['HTTPS'] === 'on');
        $schema = static::getSchemaByBlogId($blog_id);
        return ($schema === "http:" && $is_https === false) || ($schema === "https:" && $is_https === true);
    }

    /**
     * エントリのパーマリンク
     * @param string $blog_id
     * @param int $entry_id
     * @return string
     */
    static public function getEntryFullUrlByBlogIdAndEntryId(string $blog_id, int $entry_id): string
    {
        $schema = static::getSchemaByBlogId($blog_id);
        $domain = Config::get("DOMAIN");
        $port = ($schema === "https:") ? App::HTTPS_PORT_STR : App::HTTP_PORT_STR;
        // default blog ならば blog_idは省略する
        if ($blog_id !== Config::get('DEFAULT_BLOG_ID')) {
            $blog_id_path = '/' . $blog_id;
        } else {
            $blog_id_path = "";
        }
        return $schema . "//" . $domain . $port . $blog_id_path . "/blog-entry-" . $entry_id . ".html";
    }

    /**
     * Blog Idをキーとして、そのブログの`http(s)?://FQDN(:port)/(blog_id)?/`を生成する
     * @param string $blog_id
     * @param ?string $domain 省略時、\Fc2blog\Config::get("DOMAIN")
     * @return string
     */
    static public function getFullUrlByBlogId(string $blog_id, ?string $domain = null): string
    {
        $schema = static::getSchemaByBlogId($blog_id);
        if (is_null($domain)) {
            $domain = Config::get("DOMAIN");
        }
        // default blog ならば blog_idは省略する
        if ($blog_id !== Config::get('DEFAULT_BLOG_ID')) {
            $blog_id_path = '/' . $blog_id;
        } else {
            $blog_id_path = "";
        }
        $port = ($schema === "https:") ? App::HTTPS_PORT_STR : App::HTTP_PORT_STR;
        return $schema . "//" . $domain . $port . $blog_id_path . "/";
    }

    /**
     * Blog Idをキーとして、そのブログの`http(s)://FQDN(:port)`を生成する
     * @param string $blog_id
     * @param ?string $domain 省略時、\Fc2blog\Config::get("DOMAIN")
     * @return string
     */
    static public function getFullHostUrlByBlogId(string $blog_id, ?string $domain = null): string
    {
        $schema = static::getSchemaByBlogId($blog_id);
        if (is_null($domain)) {
            $domain = Config::get("DOMAIN");
        }
        $port = ($schema === "https:") ? App::HTTPS_PORT_STR : App::HTTP_PORT_STR;
        return $schema . "//" . $domain . $port;
    }

    /**
     * Blog Idをキーとして、そのブログのssl_enable設定からリンク時のSchemaを決定する
     * @param string $blog_id
     * @return string
     */
    static public function getSchemaByBlogId(string $blog_id): string
    {
        if (!static::isValidBlogId($blog_id)) throw new InvalidArgumentException("invalid blog id :{$blog_id}");
        $blogs_model = static::getInstance();
        $blog_array = $blogs_model->findById($blog_id);

        if (!is_array($blog_array) || !isset($blog_array['ssl_enable'])) {
            throw new InvalidArgumentException("blog id `{$blog_id}` notfound.");
        }

        return static::getSchemaBySslEnableValue($blog_array['ssl_enable']);
    }

    /**
     * Valueをキーとして、そのブログのssl_enable設定からリンク時のSchemaを決定する
     * @param int $value
     * @return string
     */
    static public function getSchemaBySslEnableValue(int $value): string
    {
        return ($value === Config::get("BLOG.SSL_ENABLE.DISABLE")) ? 'http:' : 'https:';
    }

    /**
     * Blog Idをキーとして、そのブログのssl_enable設定からリンク時のSchemaを決定する
     * @param string $blog_id
     * @return int
     */
    static public function getRedirectStatusCodeByBlogId(string $blog_id): int
    {
        if (!static::isValidBlogId($blog_id)) throw new InvalidArgumentException("invalid blog id :{$blog_id}");
        $blogs_model = static::getInstance();
        $blog_array = $blogs_model->findById($blog_id);

        if (!is_array($blog_array) || !isset($blog_array['redirect_status_code'])) {
            throw new InvalidArgumentException("blog id `{$blog_id}` notfound.");
        }

        return $blog_array['redirect_status_code'];
    }

    /**
     * @return string|null
     */
    static public function getDefaultBlogId(): ?string
    {
        if (strlen(Config::get("DEFAULT_BLOG_ID", "")) > 0) {
            return Config::get("DEFAULT_BLOG_ID");
        } else {
            return null;
        }
    }

    /**
     * @param Request $request
     * @return string|null
     * @noinspection PhpUnused
     */
    static public function getBlogIdByRequestOrDefault(Request $request): ?string
    {
        if ($request->getBlogId()) {
            return $request->getBlogId();

        } else if (Config::get("DEFAULT_BLOG_ID", "") && !is_null(BlogsModel::getDefaultBlogId())) {
            return BlogsModel::getDefaultBlogId();

        } else {
            return null;
        }
    }
}
