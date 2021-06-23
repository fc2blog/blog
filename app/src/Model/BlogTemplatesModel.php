<?php

namespace Fc2blog\Model;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Web\Fc2BlogTemplate;

class BlogTemplatesModel extends Model
{

    public static $instance = null;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new BlogTemplatesModel();
        }
        return self::$instance;
    }

    public function getTableName(): string
    {
        return 'blog_templates';
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
            'html' => array(
                'required' => true,
                'maxlength' => array('max' => 100000),
                'own' => array('method' => 'fc2TemplateSyntax')
            ),
            'css' => array(
                'required' => false,
                'maxlength' => array('max' => 100000),
            ),
            'device_type' => array(
                'default_value' => Config::get('DEVICE_PC'),
                'in_array' => array('values' => array_keys(Config::get('DEVICE_NAME'))),
            ),
        );

        return parent::validate($data, $valid_data, $white_list);
    }

    /**
     * バリデーションでつかうために、Proxy呼び出し
     * @param string $php_code
     * @return bool|string
     */
    public static function fc2TemplateSyntax(string $php_code)
    {
        return Fc2BlogTemplate::fc2TemplateSyntax($php_code);
    }

    /**
     * テンプレートのパスを返却
     * @param string $blog_id
     * @param int $device_type
     * @param bool $isPreview
     * @return string
     */
    public static function getTemplateFilePath(string $blog_id, int $device_type = 0, bool $isPreview = false)
    {
        return App::BLOG_TEMPLATE_DIR . App::getBlogLayer($blog_id) . '/' . $device_type . '/' . ($isPreview ? 'preview' : 'index') . '.php';
    }

    /**
     * CSSのパスを返却
     * @param $blog_id
     * @param $device_type
     * @param bool $isPreview
     * @return string
     */
    public static function getCssFilePath($blog_id, $device_type, $isPreview = false)
    {
        return App::WWW_UPLOAD_DIR . App::getBlogLayer($blog_id) . '/' . $device_type . '/' . ($isPreview ? 'preview' : 'index') . '.css';
    }

    /**
     * CSSのURLを返却
     * @param $blog_id
     * @param $device_type
     * @param bool $isPreview
     * @return string
     */
    public static function getCssUrl($blog_id, $device_type, $isPreview = false)
    {
        return '/uploads/' . App::getBlogLayer($blog_id) . '/' . $device_type . '/' . ($isPreview ? 'preview' : 'index') . '.css';
    }

    /**
     * テンプレートを作成
     * @param $templateId
     * @param $blog_id
     * @param $device_type
     * @param null $html
     * @param null $css
     */
    public static function createTemplate($templateId, $blog_id, $device_type, $html = null, $css = null)
    {
        $isPreview = !empty($html);   // HTMLの情報が入っている場合プレビュー用として作成

        if (!$isPreview) {
            // DBからHTMLとCSSを取得
            $blog_templates_model = Model::load('BlogTemplates');
            $blogTemplate = $blog_templates_model->findByIdAndBlogId($templateId, $blog_id);
            $html = $blogTemplate['html'];
            $css = $blogTemplate['css'];
        }

        // フォルダが存在しない場合作成
        $templatePath = self::getTemplateFilePath($blog_id, $device_type, $isPreview);
        $templateDir = dirname($templatePath);
        if (!file_exists($templateDir)) {
            mkdir($templateDir, 0777, true);
        }

        // HTMLをPHPテンプレートに変換してテンプレートファイルの作成
        $php_code = Fc2BlogTemplate::convertToPhp($html);
        file_put_contents($templatePath, $php_code);
        chmod($templatePath, 0777);

        // pluginのPHPを再生成（すでにファイルがあれば不要な処理だが、このタイミングよりよい再生成タイミングがない）
        BlogsModel::regeneratePluginPhpByBlogId($blog_id);

        // フォルダが存在しない場合作成
        $cssPath = self::getCssFilePath($blog_id, $device_type, $isPreview);
        $cssDir = dirname($cssPath);
        if (!file_exists($cssDir)) {
            mkdir($cssDir, 0777, true);
        }

        // CSSファイルの作成
        file_put_contents($cssPath, $css);
        chmod($cssPath, 0777);
    }

    /**
     * デバイス毎のテンプレート一覧
     * @param $blog_id
     * @param int $device_type
     * @return array
     */
    public function getTemplatesOfDevice($blog_id, $device_type = 0)
    {
        $options = array(
            'fields' => array('id', 'blog_id', 'template_id', 'title', 'device_type', 'created_at', 'updated_at'),
            'where' => 'blog_id=?',
            'params' => array($blog_id),
            'order' => 'device_type ASC, title ASC',
        );
        if ($device_type) {
            $options['where'] .= ' AND device_type=?';
            $options['params'][] = $device_type;
        } else {
            $options['where'] .= ' AND device_type IN (' . implode(',', Config::get('ALLOW_DEVICES')) . ')';
        }
        $blog_templates = $this->find('all', $options);

        $device_blog_templates = array();
        foreach ($blog_templates as $blog_template) {
            if (empty($device_blog_templates[$blog_template['device_type']])) {
                $device_blog_templates[$blog_template['device_type']] = array();
            }
            $device_blog_templates[$blog_template['device_type']][] = $blog_template;
        }
        return $device_blog_templates;
    }

    /**
     * テンプレートの作成
     * @param array $values
     * @param array $options
     * @return array|false|int|mixed
     */
    public function insert(array $values, array $options = [])
    {
        $default_values = [
            'template_id' => 0,
        ];
        $values += $default_values;
        $values['updated_at'] = $values['created_at'] = date('Y-m-d H:i:s');
        return parent::insert($values, $options);
    }

    /**
     * テンプレートの更新
     * @param $values
     * @param $id
     * @param $blog_id
     * @param array $options
     * @return bool
     */
    public function updateByIdAndBlogId($values, $id, $blog_id, $options = array())
    {
        $values['updated_at'] = date('Y-m-d H:i:s');
        if (!parent::updateByIdAndBlogId($values, $id, $blog_id, $options)) {
            return false;
        }

        // デバイスタイプを取得
        if (!$blogTemplate = $this->findByIdAndBlogId($id, $blog_id, array('fields' => 'device_type'))) {
            return false;
        }
        $device_type = $blogTemplate['device_type'];

        // 作成済みテンプレート,CSSの削除(HTMLとCSSが変更されている場合 又は 使用テンプレートの変更が行われた場合)
        $templatePath = self::getTemplateFilePath($blog_id, $device_type);
        is_file($templatePath) && unlink($templatePath);

        $cssFilePath = self::getCssFilePath($blog_id, $device_type);
        is_file($cssFilePath) && unlink($cssFilePath);

        // 適用中のテンプレート取得
        if (Model::load('Blogs')->isAppliedTemplate($id, $blog_id, $device_type)) {
            // コメントの表示タイプをテンプレートから判断
            $reply_type = strstr($values['html'], '<%comment_reply_body>') ?
                Config::get('BLOG_TEMPLATE.COMMENT_TYPE.REPLY') : Config::get('BLOG_TEMPLATE.COMMENT_TYPE.AFTER');
            // コメントの表示タイプを更新
            Model::load('BlogSettings')->updateReplyType($device_type, $reply_type, $blog_id);
        }

        return true;
    }

    static public function getPathDefaultTemplate(): string
    {
        return static::getPathDefaultTemplateWithDevice(Config::get('DEVICE_PC'));
    }

    static public function getPathDefaultCss(): string
    {
        return static::getPathDefaultCssWithDevice(Config::get('DEVICE_PC'));
    }

    static public function getPathDefaultTemplateWithDevice(string $device): string
    {
        return App::APP_DIR . 'templates/default/fc2_default_template' . Config::get('DEVICE_PREFIX.' . $device) . '.php';
    }

    static public function getPathDefaultCssWithDevice(string $device): string
    {
        return App::APP_DIR . 'templates/default/fc2_default_css' . Config::get('DEVICE_PREFIX.' . $device) . '.css';
    }

    static public function getBodyDefaultTemplateHtmlWithDevice(string $device): string
    {
        return file_get_contents(static::getPathDefaultTemplateWithDevice($device));
    }

    static public function getBodyDefaultTemplateCssWithDevice(string $device): string
    {
        return file_get_contents(static::getPathDefaultCssWithDevice($device));
    }
}
