<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Model\Fc2TemplatesModel;
use Fc2blog\Model\Model;
use Fc2blog\Service\BlogService;
use Fc2blog\Web\Request;

class BlogTemplatesController extends AdminController
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
        if (App::isPC($request)) {
            $device_type = $request->get('device_type', 0);
        } else {
            $device_type = $request->get('device_type', 1);
        }

        $blog = BlogService::getById($blog_id);
        $blogs_model = new BlogsModel();
        $this->set('template_ids', $blogs_model->getTemplateIds($blog));

        // デバイス毎に分けられたテンプレート一覧を取得
        $blog_template = new BlogTemplatesModel();
        $device_blog_templates = $blog_template->getTemplatesOfDevice($blog_id, $device_type);
        foreach ($device_blog_templates as $_device_type => &$blog_templates) {
            foreach ($blog_templates as &$blog_template) {
                $blog_template['device_key'] = App::getDeviceFc2Key($_device_type);
            }
        }
        $this->set('device_blog_templates', $device_blog_templates);
        $this->set('devices', BlogTemplatesModel::DEVICE_NAME);

        return "admin/blog_templates/index.twig";
    }

    /**
     * FC2のテンプレート一覧
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function fc2_index(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // デバイスタイプの設定
        $device_type = $request->get('device_type', (string)App::DEVICE_PC);
        $request->set('device_type', $device_type);

        // 条件設定
        $condition = array();
        $condition['page'] = $request->get('page', 0, Request::VALID_UNSIGNED_INT);
        $condition['device'] = App::getDeviceFc2Key($device_type);

        // テンプレート一覧取得
        $fc2_templates_model = new Fc2TemplatesModel();
        $fc2_templates = $fc2_templates_model->getListAndPaging($condition);
        $templates = $fc2_templates['templates'];
        $paging = $fc2_templates['pages'];

        $this->set('templates', $templates);
        $this->set('paging', $paging);
        $this->set('devices', BlogTemplatesModel::DEVICE_NAME);

        return "admin/blog_templates/fc2_index.twig";
    }

    /**
     * FC2のテンプレート詳細（スマホ用）
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function fc2_view(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // 戻る用URLの設定
        $back_url = $request->getReferer();
        if (!empty($back_url)) {
            $request->set('back_url', $request->getReferer());
        }

        // デバイスタイプの設定
        $device_type = $request->get('device_type', (string)App::DEVICE_PC);
        $request->set('device_type', $device_type);

        // テンプレート取得
        $device_key = App::getDeviceFc2Key($device_type);
        $template = Model::load('Fc2Templates')->findByIdAndDevice($request->get('fc2_id'), $device_key);
        if (empty($template)) {
            return $this->error404();
        }
        $this->set('template', $template);

        return "admin/blog_templates/fc2_view.twig"; // これはSP版しかないページです
    }

    /**
     * 新規作成
     * @param Request $request
     * @return string
     */
    public function create(Request $request): string
    {
        $blog_templates_model = new BlogTemplatesModel();

        // テンプレート置換用変数読み込み
        Config::read('fc2_template.php');
        $this->set('template_syntaxes', array_merge(array_keys(Config::get('fc2_template_foreach')), array_keys(Config::get('fc2_template_if'))));

        // 初期表示時
        if (!$request->get('blog_template') || !$request->isValidPost()) {
            // FC2テンプレートダウンロード
            if ($request->get('fc2_id')) {
                $device_type = $request->get('device_type');
                $device_key = App::getDeviceFc2Key($device_type);
                $template = Model::load('Fc2Templates')->findByIdAndDevice($request->get('fc2_id'), $device_key);
                $request->set('blog_template', [
                    'title' => $template['name'],
                    'html' => $template['html'],
                    'css' => $template['css'],
                    'device_type' => $device_type,
                ]);
            } else {
                $request->set('blog_template.device_type', $request->get('device_type'));
                $request->set('blog_template.html', BlogTemplatesModel::getBodyDefaultTemplateHtmlWithDevice($request->get('device_type')));
                $request->set('blog_template.css', BlogTemplatesModel::getBodyDefaultTemplateCssWithDevice($request->get('device_type')));
            }

            return "admin/blog_templates/create.twig";
        }

        // 新規登録処理
        if (!$request->isPost()) return $this->error400();
        $errors = [];
        $white_list = ['title', 'html', 'css', 'device_type'];
        $errors['blog_template'] = $blog_templates_model->validate($request->get('blog_template'), $blog_template_data, $white_list);
        if (empty($errors['blog_template'])) {
            $blog_template_data['blog_id'] = $this->getBlogIdFromSession();
            if ($blog_templates_model->insert($blog_template_data)) {
                $this->setInfoMessage(__('I created a template'));
                $this->redirect($request, ['action' => 'index']);
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);
        return "admin/blog_templates/create.twig";
    }

    /**
     * 編集
     * @param Request $request
     * @return string
     */
    public function edit(Request $request): string
    {
        $blog_templates_model = new BlogTemplatesModel();

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        // 初期表示時に編集データの取得&設定
        if (!$request->get('blog_template') || !$request->isValidPost()) {
            if (!$blog_template = $blog_templates_model->findByIdAndBlogId($id, $blog_id)) {
                $this->redirect($request, ['action' => 'index']);
            }
            $request->set('blog_template', $blog_template);
            return "admin/blog_templates/edit.twig";
        }

        // 更新処理
        if (!$request->isPost()) return $this->error400();
        $errors = [];
        $white_list = ['title', 'html', 'css'];
        $errors['blog_template'] = $blog_templates_model->validate($request->get('blog_template'), $blog_template_data, $white_list);
        if (empty($errors['blog_template'])) {
            if ($blog_templates_model->updateByIdAndBlogId($blog_template_data, $id, $blog_id)) {
                $this->setInfoMessage(__('I have updated the template'));
                $this->redirect($request, ['action' => 'index']);
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);
        return "admin/blog_templates/edit.twig";
    }

    /**
     * 対象のテンプレートをブログのテンプレートとして設定する
     * @param Request $request
     * @return string
     */
    public function apply(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_templates_model = Model::load('BlogTemplates');

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        $blog_template = $blog_templates_model->findByIdAndBlogId($id, $blog_id);
        if (empty($blog_template)) {
            $this->setErrorMessage(__('Template to be used can not be found'));
            $this->redirectBack($request, array('action' => 'index'));
        }

        if ($request->isValidSig()) {
            // テンプレートの切り替え作業
            Model::load('Blogs')->switchTemplate($blog_template, $blog_id);
            $this->setInfoMessage(__('I switch the template'));
        }
        $this->redirectBack($request, array('action' => 'index'));
        return "";
    }

    /**
     * 対象のテンプレートをブログのテンプレートとして設定する
     * @param Request $request
     * @return string
     */
    public function ajax_apply(Request $request): string
    {
        if ($this->isInvalidAjaxRequest($request) || !$request->isValidPost()) {
            return $this->error400();
        }

        $blog_templates_model = new BlogTemplatesModel();
        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        $blog_template = $blog_templates_model->findByIdAndBlogId($id, $blog_id);
        if (empty($blog_template)) {
            $this->setErrorMessage(__('Template to be used can not be found'));
            return "";
        }

        // テンプレートの切り替え作業
        Model::load('Blogs')->switchTemplate($blog_template, $blog_id);
        $this->setInfoMessage(__('I switch the template'));
        return "";
    }

    /**
     * テンプレートダウンロード
     * @param Request $request
     * @return string
     */
    public function download(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_templates_model = new BlogTemplatesModel();

        $id = $request->get('fc2_id');
        $device_type = $request->get('device_type');
        if (empty($id) || empty($device_type)) {
            return $this->error404();
        }

        $device_key = App::getDeviceFc2Key($device_type);
        $template = Model::load('Fc2Templates')->findByIdAndDevice($id, $device_key);
        if (empty($template)) {
            $this->setErrorMessage(__('Template does not exist'));
            $this->redirectBack($request, array('controller' => 'blog_templates', 'action' => 'fc2_index', 'device_type' => $device_type));
        }

        // 追加用のデータを取得データから作成
        $blog_template = array(
            'title' => $template['name'],
            'html' => $template['html'],
            'css' => $template['css'],
            'device_type' => $device_type,
        );

        // 新規登録処理
        $errors = array();
        $white_list = array('title', 'html', 'css', 'device_type');
        $errors['blog_template'] = $blog_templates_model->validate($blog_template, $blog_template_data, $white_list);
        if (empty($errors['blog_template'])) {
            $blog_template_data['blog_id'] = $this->getBlogIdFromSession();
            if ($blog_templates_model->insert($blog_template_data)) {
                $this->setInfoMessage('「' . h($blog_template['title']) . '」' . __('I downloaded the template'));
                $this->redirect($request, array('action' => 'index', 'device_type' => $device_type));
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('There is a flaw in the template to be downloaded'));
        $this->redirectBack($request, array('controller' => 'blog_templates', 'action' => 'fc2_index', 'device_type' => $device_type));
        return "";
    }

    /**
     * 削除
     * @param Request $request
     * @return string
     */
    public function delete(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_templates_model = Model::load('BlogTemplates');

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        // 使用中のテンプレートであれば削除させない
        $blog = BlogService::getById($blog_id);
        $template_ids = BlogsModel::getTemplateIds($blog);
        if (in_array($id, $template_ids)) {
            $this->setErrorMessage(__('You can not delete a template in use'));
            $this->redirect($request, array('action' => 'index'));
        }

        // 削除データの取得
        if (!$blog_templates_model->findByIdAndBlogId($id, $blog_id)) {
            $this->redirect($request, array('action' => 'index'));
        }

        // TODO 削除処理のPOST必須化
        if ($request->isValidSig()) {
            // 削除処理
            $blog_templates_model->deleteByIdAndBlogId($id, $blog_id);
            $this->setInfoMessage(__('I removed the template'));
        }
        $this->redirectBack($request, array('action' => 'index'));
        return "";
    }

    /**
     * 削除
     * @param Request $request
     * @return string
     */
    public function ajax_delete(Request $request): string
    {
        if ($this->isInvalidAjaxRequest($request) || !$request->isValidPost()) {
            return $this->error400();
        }

        $blog_templates_model = new BlogTemplatesModel();

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        // 使用中のテンプレートであれば削除させない
        $blog = BlogService::getById($blog_id);
        $template_ids = BlogsModel::getTemplateIds($blog);
        if (in_array($id, $template_ids)) {
            $this->setErrorMessage(__('You can not delete a template in use'));
            return "";
        }

        // 削除処理
        $blog_templates_model->deleteByIdAndBlogId($id, $blog_id);
        $this->setInfoMessage(__('I removed the template'));
        return "";
    }
}

