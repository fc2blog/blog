<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogPluginsModel;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Model\Model;
use Fc2blog\Model\PluginsModel;
use Fc2blog\Web\Request;

class BlogPluginsController extends AdminController
{
    /**
     * 一覧表示
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $this->getBlogIdFromSession();
        $device_type = $request->get('device_type', (string)App::DEVICE_PC, Request::VALID_IN_ARRAY, App::ALLOW_DEVICES);
        $this->set('device_type', $device_type);
        $this->set('devices', BlogTemplatesModel::DEVICE_NAME);

        // デバイス毎に分けられたテンプレート一覧を取得
        $blog_plugins_model = new BlogPluginsModel();
        $category_blog_plugins = $blog_plugins_model->getCategoryPlugins($blog_id, $device_type);
        $this->set('category_blog_plugins', $category_blog_plugins);
        $this->set('app_display_show', App::APP_DISPLAY_SHOW);
        $this->set('app_display_hide', App::APP_DISPLAY_HIDE);

        $blog_plugin_json = [];
        foreach ($category_blog_plugins as $blog_plugins) {
            foreach ($blog_plugins as $blog_plugin) {
                $blog_plugin_json[] = array(
                    'id' => $blog_plugin['id'],
                    'category' => $blog_plugin['category'],
                    'title' => $blog_plugin['title'],
                );
            }
        }
        $this->set('blog_plugin_json', $blog_plugin_json);

        return "admin/blog_plugins/index.twig";
    }

    /**
     * 公式プラグイン検索
     * @param Request $request
     * @return string
     */
    public function official_search(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        return $this->plugin_search($request);
    }

    /**
     * 共有プラグイン検索
     * @param Request $request
     * @return string
     */
    public function share_search(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        return $this->plugin_search($request, false);
    }

    /**
     * プラグイン検索 （内部呼び出し）
     * @param Request $request
     * @param bool $is_official
     * @return string
     */
    private function plugin_search(Request $request, bool $is_official = true): string
    {
        $plugins_model = new PluginsModel();

        // デバイスタイプの取得
        $device_type = $request->get('device_type', (string)App::DEVICE_PC, Request::VALID_IN_ARRAY, App::ALLOW_DEVICES);
        $request->set('device_type', $device_type);

        // 検索条件作成
        $where = 'device_type=?';
        $params = array($device_type);

        if ($is_official) {
            $where .= ' AND user_id=0';
        } else {
            $where .= ' AND user_id<>0';
        }

        // 並び順
        $order = 'updated_at DESC, id DESC';

        $options = array(
            'where' => $where,
            'params' => $params,
            'limit' => $request->get('limit', Config::get('PAGE.PLUGIN.DEFAULT.LIMIT'), Request::VALID_POSITIVE_INT),
            'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
            'order' => $order,
        );
        $plugins = $plugins_model->find('all', $options);
        $paging = $plugins_model->getPaging($options);

        $this->set('plugins', $plugins);
        $this->set('paging', $paging);
        $this->set('user_id', $this->getUserId());
        $this->set('devices', BlogTemplatesModel::DEVICE_NAME);
        $this->set('req_device_name', __(BlogTemplatesModel::getDeviceName((int)$request->get('device_type'))));
        $this->set('device_key', App::getDeviceFc2Key($request->get('device_type')));

        return 'admin/blog_plugins/plugin_search.twig';
    }

    /**
     * 新規作成
     * @param Request $request
     * @return string
     */
    public function create(Request $request): string
    {
        /** @var BlogPluginsModel $blog_plugins_model */
        $blog_plugins_model = Model::load('BlogPlugins');

        $this->set('blog_plugin_attribute_align', BlogPluginsModel::getAttributeAlign());
        $this->set('blog_plugin_attribute_color', BlogPluginsModel::getAttributeColor());
        $this->set('device_key_list', App::DEVICE_FC2_KEY);
        $this->set('device_type', $request->get('blog_plugin.device_type'));

        // テンプレート置換用変数読み込み
        Config::read('fc2_template.php');
        $template_syntaxes = array_merge(array_keys(Config::get('fc2_template_foreach')), array_keys(Config::get('fc2_template_if')));
        $this->set('template_syntaxes', $template_syntaxes);

        // 初期表示時
        if (!$request->get('blog_plugin') || !$request->isValidSig()) {
            $request->set('blog_plugin', array(
                'device_type' => $request->get('device_type', App::DEVICE_PC, Request::VALID_IN_ARRAY, App::ALLOW_DEVICES),
                'category' => $request->get('category', 1),
            ));
            return "admin/blog_plugins/create.twig";
        }

        // 以下は更新処理なので、POST必須
        if (!$request->isPost()) return $this->error400();

        // 新規登録処理
        $errors = array();
        $white_list = array('title', 'title_align', 'title_color', 'contents', 'contents_align', 'contents_color', 'device_type', 'category');
        $errors['blog_plugin'] = $blog_plugins_model->validate($request->get('blog_plugin'), $blog_plugin_data, $white_list);
        if (empty($errors['blog_plugin'])) {
            $blog_plugin_data['blog_id'] = $this->getBlogIdFromSession();
            if ($blog_plugins_model->insert($blog_plugin_data)) {
                $this->setInfoMessage(__('I created a plugin'));
                $this->redirect($request, array('action' => 'index', 'device_type' => $blog_plugin_data['device_type']));
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);
        return "admin/blog_plugins/create.twig";
    }

    /**
     * 編集
     * @param Request $request
     * @return string
     */
    public function edit(Request $request): string
    {
        /** @var BlogPluginsModel $blog_plugins_model */
        $blog_plugins_model = Model::load('BlogPlugins');

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        $this->set('blog_plugin_attribute_align', BlogPluginsModel::getAttributeAlign());
        $this->set('blog_plugin_attribute_color', BlogPluginsModel::getAttributeColor());
        $this->set('device_key_list', App::DEVICE_FC2_KEY);
        $this->set('device_type', $request->get('blog_plugin.device_type'));
        $this->set('device_type_sp', (string)App::DEVICE_SP);

        // 編集対象のデータ取得、なければリダイレクト
        if (!$blog_plugin = $blog_plugins_model->findByIdAndBlogId($id, $blog_id)) {
            $this->redirect($request, array('action' => 'index'));
        }

        // 初期表示時に編集データの設定
        if (!$request->get('blog_plugin') || !$request->isValidSig()) {
            $request->set('blog_plugin', $blog_plugin);
            return "admin/blog_plugins/edit.twig";
        }

        // 以下は更新処理なので、POST必須
        if (!$request->isPost()) return $this->error400();

        // 更新処理
        $errors = array();
        $white_list = array('title', 'title_align', 'title_color', 'contents', 'contents_align', 'contents_color');
        $errors['blog_plugin'] = $blog_plugins_model->validate($request->get('blog_plugin'), $blog_plugin_data, $white_list);
        if (empty($errors['blog_plugin'])) {
            if ($blog_plugins_model->updateByIdAndBlogId($blog_plugin_data, $id, $blog_id)) {
                $this->setInfoMessage(__('I have updated the plugin'));
                $this->redirect($request, ['action' => 'index', 'device_type' => $blog_plugin['device_type']]);
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);

        return "admin/blog_plugins/edit.twig";
    }

    /**
     * 削除
     * @param Request $request
     * @return string
     */
    public function delete(Request $request): string
    {
        $blog_plugins_model = Model::load('BlogPlugins');

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        // 削除データの取得
        $blog_plugin = $blog_plugins_model->findByIdAndBlogId($id, $blog_id);
        if (!$blog_plugin) {
            $this->redirect($request, array('action' => 'index'));
        }

        // 以下は更新処理なので、POST必須
        if (!$request->isPost()) return $this->error400();

        if ($request->isValidSig()) {
            // 削除処理
            $blog_plugins_model->deleteByIdAndBlogId($id, $blog_id);
            $this->setInfoMessage(__('I removed the plugin'));
        }
        $this->redirect($request, array('action' => 'index', 'device_type' => $blog_plugin['device_type']));
        return "";
    }

    /**
     * 登録
     * @param Request $request
     * @return string
     */
    public function register(Request $request): string
    {
        $plugins_model = new PluginsModel();
        $blog_plugins_model = new BlogPluginsModel();

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        // 登録データの取得
        $blog_plugin = $blog_plugins_model->findByIdAndBlogId($id, $blog_id);
        if (!$blog_plugin) {
            $this->redirect($request, ['action' => 'index']);
        }
        $this->set('blog_plugin', $blog_plugin);

        if (!$request->get('plugin') || !$request->isValidSig()) {
            // 初期値入力
            if ($blog_plugin['plugin_id']) {
                // 既に登録済み
                $plugin = $plugins_model->findByIdAndUserId($blog_plugin['plugin_id'], $this->getUserId());
                $request->set('plugin.title', $plugin['title']);
                $request->set('plugin.body', $plugin['body']);
            } else {
                // 未登録
                $request->set('plugin.title', $blog_plugin['title']);
            }
            return 'admin/blog_plugins/register.twig';
        }

        // 以下は更新処理なので、POST必須
        if (!$request->isPost()) return $this->error400();

        // 新規登録処理
        $errors = [];
        $white_list = ['title', 'body'];
        $errors['plugin'] = $plugins_model->validate($request->get('plugin'), $plugin_data, $white_list);
        if (empty($errors['plugin'])) {
            // 登録処理
            if ($plugins_model->register($blog_plugin, $plugin_data, $this->getUserId())) {
                $this->setInfoMessage(__('I have registered the plug-in'));
            } else {
                $this->setErrorMessage(__('I failed to register the plug-in'));
            }
            $this->redirect($request, ['action' => 'index']);
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);

        return 'admin/blog_plugins/register.twig';
    }

    /**
     * 登録済みのプラグイン削除
     * @param Request $request
     * @return string
     */
    public function plugin_delete(Request $request): string
    {
        $plugins_model = Model::load('Plugins');

        $id = $request->get('id');
        $user_id = $this->getUserId();

        // 削除データの取得
        if (!$plugins_model->findByIdAndUserId($id, $user_id)) {
            $this->redirect($request, array('action' => 'search'));
        }

        // 以下は更新処理なので、POST必須
        if (!$request->isPost()) return $this->error400();

        if ($request->isValidSig()) {
            // 削除処理
            $plugins_model->deleteByIdAndUserId($id, $user_id);
            $this->setInfoMessage(__('I removed the plugin'));
        }
        $this->redirectBack($request, array('action' => 'search'));
        return "";
    }

    /**
     * プラグインのダウンロード
     * @param Request $request
     * @return string
     */
    public function download(Request $request): string
    {
        $id = $request->get('id');
        $plugin = Model::load('Plugins')->findById($id);
        if (empty($plugin)) {
            $this->setErrorMessage(__('Plugin does not exist'));
            $this->redirectBack($request, array('controller' => 'blog_plugins', 'action' => 'index'));
        }

        // 以下は更新処理なので、POST必須
        if (!$request->isPost()) return $this->error400();

        if ($request->isValidSig()) {
            // 追加用のデータを取得データから作成
            $blog_plugin_data = array(
                'title' => $plugin['title'],
                'contents' => $plugin['contents'],
                'device_type' => $plugin['device_type'],
                'category' => $request->get('category'),
            );

            // 新規登録処理
            $blog_plugin_data['blog_id'] = $this->getBlogIdFromSession();
            if (Model::load('BlogPlugins')->insert($blog_plugin_data)) {
                $this->setInfoMessage(__('I created a plugin'));
                $this->redirect($request, array('action' => 'index', 'device_type' => $plugin['device_type']));
            }

            $this->setErrorMessage(__('I failed to download the plug-in'));
        }
        $this->redirectBack($request, array('controller' => 'blog_plugins', 'action' => 'index'));
        return "";
    }

    /**
     * 並べ替え
     * @param Request $request
     * @return string
     */
    public function sort(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_plugins_model = Model::load('BlogPlugins');

        $blog_id = $this->getBlogIdFromSession();
        $device_type = $request->get('device_type', App::DEVICE_PC, Request::VALID_IN_ARRAY, App::ALLOW_DEVICES);

        // 並べ替え処理
        // TODO Sigチェック不足
        $blog_plugins_model->sort($request->get('blog_plugins', array()), $device_type, $blog_id);

        $this->setInfoMessage(__('I have completed the sorting'));
        if (App::isSP($request)) {
            $this->redirect($request, array('action' => 'index', 'device_type' => $device_type, 'state' => 'sort'));
        }
        $this->redirect($request, array('action' => 'index', 'device_type' => $device_type));
        return "";
    }

    /**
     * プラグインの表示設定
     * @param Request $request
     * @return string
     */
    public function display_changes(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_plugins_model = Model::load('BlogPlugins');

        $blog_id = $this->getBlogIdFromSession();
        $device_type = $request->get('device_type', App::DEVICE_PC, Request::VALID_IN_ARRAY, App::ALLOW_DEVICES);

        if ($request->isValidSig()) {
            // プラグインの表示可否の一括変更
            $blog_plugins_model->updateDisplay($request->get('blog_plugins'), $blog_id);
            $this->setInfoMessage(__('I changed the display settings'));
        }

        if (App::isSP($request)) {
            $this->redirect($request, array('action' => 'index', 'device_type' => $device_type, 'state' => 'display'));
        }
        $this->redirect($request, array('action' => 'index', 'device_type' => $device_type));
        return "";
    }

    /**
     * プラグインの表示設定
     * @param Request $request
     * @return string
     */
    public function display_change(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_plugins_model = Model::load('BlogPlugins');

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();
        $display = $request->get('display') ? App::APP_DISPLAY_SHOW : App::APP_DISPLAY_HIDE;  // 表示可否

        // 編集対象のデータ取得
        if (!$blog_plugins_model->findByIdAndBlogId($id, $blog_id) || !$request->isValidSig()) {
            $this->redirect($request, array('action' => 'index'));
        }

        // 表示・非表示設定
        $blog_plugins_model->updateByIdAndBlogId(array('display' => $display), $id, $blog_id);
        return "";
    }
}

