<?php

namespace Fc2blog\Model;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Util\PhpCodeLinter;
use Fc2blog\Web\Fc2BlogTemplate;
use Fc2blog\Web\Session;

class BlogPluginsModel extends Model
{

    public static $instance = null;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new BlogPluginsModel();
        }
        return self::$instance;
    }

    public function getTableName(): string
    {
        return 'blog_plugins';
    }

    public function getAutoIncrementCompositeKey(): string
    {
        return 'blog_id';
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
            'title' => array(
                'required' => true,
                'maxlength' => array('max' => 50),
            ),
            'title_align' => array(
                'default_value' => 'left',
                'in_array' => array('values' => array_keys(self::getAttributeAlign())),
            ),
            'title_color' => array(
                'default_value' => '',
                'in_array' => array('values' => array_keys(self::getAttributeColor())),
            ),
            'contents' => array(
                'required' => true,
                'maxlength' => array('max' => 100000),
                'own' => array('method' => 'fc2PluginSyntax')
            ),
            'contents_align' => array(
                'default_value' => 'left',
                'in_array' => array('values' => array_keys(self::getAttributeAlign())),
            ),
            'contents_color' => array(
                'default_value' => '',
                'in_array' => array('values' => array_keys(self::getAttributeColor())),
            ),
            'device_type' => array(
                'default_value' => Config::get('DEVICE_PC'),
                'in_array' => array('values' => array_keys(Config::get('DEVICE_NAME'))),
            ),
            'category' => array(
                'default_value' => 1,
                'in_array' => array('values' => array(1, 2, 3)),
            ),
        );

        $errors = parent::validate($data, $valid_data, $white_list);
        if (!empty($errors)) {
            return $errors;
        }

        // title_align,title_color...をattributesに纏める
        if (in_array('title_align', $white_list) || in_array('title_color', $white_list)
            || in_array('contents_align', $white_list) || in_array('contents_color', $white_list)
        ) {
            $attribute = array(
                'title_align' => $valid_data['title_align'],
                'title_color' => $valid_data['title_color'],
                'contents_align' => $valid_data['contents_align'],
                'contents_color' => $valid_data['contents_color'],
            );
            $valid_data['attribute'] = json_encode($attribute);
            unset($valid_data['title_align'], $valid_data['title_color'], $valid_data['contents_align'], $valid_data['contents_color']);
        }
        return $errors;
    }

    /**
     * 文字方向の設定
     */
    public static function getAttributeAlign(): array
    {
        return array(
            'left' => __('Flush left'),
            'center' => __('Center justification'),
            'right' => __('Right justification'),
        );
    }

    /**
     * 文字色の設定
     * @param bool $text
     * @return array
     */
    public static function getAttributeColor($text = false): array
    {
        if ($text) {
            return array(
                '' => __('Nothing'),
                'red' => __('Red'),
                'green' => __('Green'),
                'blue' => __('Blue'),
                'purple' => __('Purple'),
                'pink' => __('Pink'),
                'orange' => __('Orange'),
                'navy' => __('Navy'),
                'gray' => __('Gray'),
            );
        }
        return array(
            '' => __('Nothing'),
            'red' => '<span style="color:red">■</span>',
            'green' => '<span style="color:green">■</span>',
            'blue' => '<span style="color:blue">■</span>',
            'purple' => '<span style="color:purple">■</span>',
            'pink' => '<span style="color:pink">■</span>',
            'orange' => '<span style="color:orange">■</span>',
            'navy' => '<span style="color:navy">■</span>',
            'gray' => '<span style="color:gray">■</span>',
        );
    }

    /**
     * FC2テンプレートの構文チェック
     * @param string $php_code
     * @return string|true
     */
    public static function fc2PluginSyntax(string $php_code)
    {
        // フォルダが存在しない場合作成
        $plugin_path = App::BLOG_TEMPLATE_DIR . App::getBlogLayer(Session::get('blog_id')) . '/plugins/syntax.php';
        $plugin_dir = dirname($plugin_path);
        if (!file_exists($plugin_dir)) {
            mkdir($plugin_dir, 0777, true);
        }

        // HTMLをPHPテンプレートに変換してテンプレートファイルの作成
        $html = Fc2BlogTemplate::convertToPhp($php_code);
        file_put_contents($plugin_path, $html);
        chmod($plugin_path, 0777);

        // PHPのシンタックスチェック
        if (PhpCodeLinter::isParsablePhpCode($html)) {
            return true;
        } else {
            return __('There may be a problem with the template or plug-in, installed in the blog.');
        }
    }

    /**
     * カテゴリー毎のプラグイン一覧
     * @param $blog_id
     * @param $device_type
     * @return array[]
     */
    public function getCategoryPlugins($blog_id, $device_type): array
    {
        $options = array(
            'where' => 'blog_id=? AND device_type=?',
            'params' => array($blog_id, $device_type),
            'order' => 'category ASC, plugin_order ASC',
        );
        $blog_plugins = $this->find('all', $options);

        $category_blog_plugins = array(1 => array());
        if ($device_type == Config::get('DEVICE_PC')) {
            // PC版のみ3つまでカテゴリーが存在する
            $category_blog_plugins = array(1 => array(), 2 => array(), 3 => array());
        }
        foreach ($blog_plugins as $blog_plugin) {
            $category_blog_plugins[$blog_plugin['category']][] = $blog_plugin;
        }
        return $category_blog_plugins;
    }

    /**
     * idとblog_idの複合キーからデータを取得
     * attributeデータを振り分け
     * @param $id
     * @param string|null $blog_id
     * @param array $options
     * @return array|mixed
     */
    public function findByIdAndBlogId($id, ?string $blog_id, $options = []): array
    {
        $data = parent::findByIdAndBlogId($id, $blog_id, $options);
        if (empty($data)) {
            return $data;
        }

        $attribute = json_decode($data['attribute']);
        if (empty($attribute)) {
            $data['title_align'] = $data['contents_align'] = 'left';
            $data['title_color'] = $data['contents_color'] = '';
        } else {
            $data['title_align'] = $attribute->title_align;
            $data['title_color'] = $attribute->title_color;
            $data['contents_align'] = $attribute->contents_align;
            $data['contents_color'] = $attribute->contents_color;
        }
        return $data;
    }

    /**
     * デバイスタイプとカテゴリーの条件にマッチしたプラグインを返却
     * @param $device_type
     * @param $category
     * @param $blog_id
     * @return mixed
     */
    public function findByDeviceTypeAndCategory($device_type, $category, $blog_id)
    {
        $options = array(
            'where' => 'blog_id=? AND device_type=? AND category=? AND display=' . Config::get('APP.DISPLAY.SHOW'),
            'params' => array($blog_id, $device_type, $category),
            'order' => 'plugin_order ASC',
        );
        $plugins = $this->find('all', $options);
        foreach ($plugins as $key => $value) {
            $attribute = json_decode($value['attribute']);
            if (empty($attribute)) {
                $plugins[$key]['title_align'] = $plugins[$key]['contents_align'] = 'left';
                $plugins[$key]['title_color'] = $plugins[$key]['contents_color'] = '';
            } else {
                $plugins[$key]['title_align'] = $attribute->title_align;
                $plugins[$key]['title_color'] = $attribute->title_color;
                $plugins[$key]['contents_align'] = $attribute->contents_align;
                $plugins[$key]['contents_color'] = $attribute->contents_color;
            }
        }
        return $plugins;
    }

    /**
     * 最後の表示順を取得する
     * @param $blog_id
     * @param $device_type
     * @param $category
     * @return int
     */
    public function getNextPluginOrder($blog_id, $device_type, $category): int
    {
        $plugin_order = $this->find('one', array(
            'fields' => 'plugin_order',
            'where' => 'blog_id=? AND device_type=? AND category=?',
            'params' => array($blog_id, $device_type, $category),
            'order' => 'plugin_order DESC',
            'limit' => 1,
        ));
        if (empty($plugin_order)) {
            return 0;
        }
        return $plugin_order + 1;
    }

    /**
     * テンプレートの作成
     * @param $values
     * @param array $options
     * @return array|false|int|mixed
     */
    public function insert($values, $options = array())
    {
        $default_values = [
            'list' => '',
            'attribute' => '',
        ];
        $values += $default_values;

        $values['updated_at'] = $values['created_at'] = date('Y-m-d H:i:s');
        $values['plugin_order'] = $this->getNextPluginOrder($values['blog_id'], $values['device_type'], $values['category']);
        $id = parent::insert($values, $options);
        if ($id) {
            // プラグインのPHPファイル作成
            self::createPlugin($values['contents'], $values['blog_id'], $id);
        }
        return $id;
    }

    /**
     * テンプレートの更新
     * @param array $values
     * @param $id
     * @param string $blog_id
     * @param array $options
     * @return bool
     */
    public function updateByIdAndBlogId(array $values, $id, string $blog_id, array $options = array()): bool
    {
        $values['updated_at'] = date('Y-m-d H:i:s');
        if (!parent::updateByIdAndBlogId($values, $id, $blog_id, $options)) {
            return false;
        }
        // プラグインのPHPファイル作成
        if (isset($values['contents'])) {
            self::createPlugin($values['contents'], $blog_id, $id);
        }
        return true;
    }

    /**
     * 表示方法の変更を行う
     *  params => array([id=>display], ...)の形式
     * @param array $params
     * @param string $blog_id
     * @return bool
     */
    public function updateDisplay(array $params, string $blog_id)
    {
        if (!count($params)) {
            return false;
        }

        $displays = array();
        $displays[Config::get('APP.DISPLAY.SHOW')] = array();
        $displays[Config::get('APP.DISPLAY.HIDE')] = array();
        foreach ($params as $id => $display) {
            // show,hide以外のdisplayは更新対象としない
            if (isset($displays[$display])) {
                $displays[$display][] = $id;
            }
        }

        $ret = true;
        foreach ($displays as $display => $values) {
            if (!count($values)) {
                continue;
            }
            $where = 'blog_id=? AND id IN (' . implode(',', array_fill(0, count($values), '?')) . ')';
            $ret = $ret && $this->update(array('display' => $display), $where, array_merge(array($blog_id), $values));
        }

        return $ret;
    }

    /**
     * idとblog_idをキーとした削除 + ファイル削除も行う
     * @param $id
     * @param string $blog_id
     * @param array $options
     * @return array|false|int|mixed
     */
    public function deleteByIdAndBlogId($id, string $blog_id, array $options = array())
    {
        // プラグインファイルの削除
        $plugin_file = App::getPluginFilePath($blog_id, $id);
        is_file($plugin_file) && unlink($plugin_file);

        // 本体削除
        return parent::deleteByIdAndBlogId($id, $blog_id, $options);
    }

    /**
     * 並べ替えて更新
     *  [id] => array(order=>x, category=>x)の形
     * @param array $sort_values
     * @param string $device_type
     * @param string $blog_id
     */
    public function sort(array $sort_values, string $device_type, string $blog_id)
    {
        $blog_plugins = $this->find('all', array(
            'fields' => array('id, plugin_order, category'),
            'where' => 'blog_id=? AND device_type=?',
            'params' => array($blog_id, $device_type),
        ));
        $ids = array_keys($sort_values);
        foreach ($blog_plugins as $blog_plugin) {
            if (!in_array($blog_plugin['id'], $ids)) {
                $this->deleteByIdAndBlogId($blog_plugin['id'], $blog_id);
                continue;
            }
            $values = $sort_values[$blog_plugin['id']];
            if ($blog_plugin['plugin_order'] == $values['order'] && $blog_plugin['category'] == $values['category']) {
                // 順序、カテゴリーに変更なし
                continue;
            }
            // 並べ替えの順序で更新
            $params = array(
                'plugin_order' => intval($values['order']),
                'category' => intval($values['category']),
            );
            $this->updateByIdAndBlogId($params, $blog_plugin['id'], $blog_id);
        }
    }

    /**
     * テンプレートを作成
     * @param string $html
     * @param string $blog_id
     * @param string $id
     */
    public static function createPlugin(string $html, string $blog_id, string $id = 'preview'): void
    {
        // フォルダが存在しない場合作成
        $plugin_path = App::getPluginFilePath($blog_id, $id);
        $plugin_dir = dirname($plugin_path);
        if (!file_exists($plugin_dir)) {
            mkdir($plugin_dir, 0777, true);
        }

        // HTMLをPHPテンプレートに変換してテンプレートファイルの作成
        $html = Fc2BlogTemplate::convertToPhp($html);
        file_put_contents($plugin_path, $html);
        chmod($plugin_path, 0777);
    }
}
