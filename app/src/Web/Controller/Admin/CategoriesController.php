<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Config;
use Fc2blog\Model\CategoriesModel;
use Fc2blog\Model\Model;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

class CategoriesController extends AdminController
{

    /**
     * カテゴリー一覧、新規作成
     * @param Request $request
     * @return string
     */
    public function create(Request $request): string
    {
        $categories_model = new CategoriesModel();

        $blog_id = $this->getBlogId($request);

        $this->set('categories_model_order_list', $categories_model::getOrderList());
        $this->set('categories', $categories_model->getList($this->getBlogId($request)));

        // 親カテゴリー一覧
        $options = $categories_model->getParentList($blog_id);
        $this->set('category_parents', [0 => ''] + $options);

        // カテゴリ登録数
        $create_limit = Config::get('CATEGORY.CREATE_LIMIT');
        $is_limit_create_category = $create_limit > 0 && $create_limit <= count($options);
        $this->set('is_limit_create_category', $is_limit_create_category);

        if ($is_limit_create_category) {
            $this->setErrorMessage(__('Exceeded the maximum number of registered category'));
            $request->set('category', null);
            return "admin/categories/create.twig";
        }

        // 初期表示時
        if (!$request->get('category') || !$request->isValidSig()) {
            return "admin/categories/create.twig";
        }

        // 新規登録処理
        $category_request = $request->get('category');
        $category_request['blog_id'] = $blog_id;
        $errors = $categories_model->validate($category_request, $data, ['parent_id', 'name', 'category_order']);
        if (empty($errors)) {
            $data['blog_id'] = $blog_id;
            if ($categories_model->addNode($data, 'blog_id=?', [$blog_id])) {
                $this->setInfoMessage(__('I added a category'));
                $this->redirect($request, ['action' => 'create']);
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);

        return "admin/categories/create.twig";
    }

    /**
     * 編集
     * @param Request $request
     * @return string
     */
    public function edit(Request $request): string
    {
        $categories_model = new CategoriesModel();

        $id = $request->get('id');
        $blog_id = $this->getBlogId($request);

        // 親カテゴリー一覧
        $options = $categories_model->getParentList($blog_id, $id);
        $this->set('category_parents', [0 => ''] + $options);
        $this->set('categories_model_order_list', $categories_model::getOrderList());

        // 初期表示時に編集データの取得&設定
        if (!$request->get('category') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
            if (!$category = $categories_model->findByIdAndBlogId($id, $blog_id)) {
                $this->redirect($request, ['action' => 'create']);
            }
            $request->set('category', $category);
            return "admin/categories/edit.twig";
        }

        // 更新処理
        $category_request = $request->get('category');
        $category_request['id'] = $id;            // 入力チェック用
        $category_request['blog_id'] = $blog_id;  // 入力チェック用
        $errors = $categories_model->validate($category_request, $data, ['parent_id', 'name', 'category_order']);
        if (empty($errors)) {
            if ($categories_model->updateNodeById($data, $id, 'blog_id=?', [$blog_id])) {
                $this->setInfoMessage(__('I have updated the category'));
                $this->redirect($request, ['action' => 'create']);
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);
        return "admin/categories/edit.twig";
    }

    /**
     * 削除
     * @param Request $request
     */
    public function delete(Request $request)
    {
        $categories_model = Model::load('Categories');

        $id = $request->get('id');
        $blog_id = $this->getBlogId($request);

        if (!Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
            $request = new Request();
            $this->redirect($request, array('action' => 'create'));
            return;
        }

        // 削除データの取得(未分類であるid=1は削除させない)
        if ($id == 1 || !$categories_model->findByIdAndBlogId($id, $blog_id)) {
            $this->redirect($request, array('action' => 'create'));
        }

        // 削除処理
        $categories_model->deleteNodeByIdAndBlogId($id, $blog_id);
        $this->setInfoMessage(__('I removed the category'));
        $this->redirect($request, array('action' => 'create'));
    }

    /**
     * ajax用のカテゴリ追加 admin/entries/create からインクルード
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function ajax_add(Request $request): string
    {
        if ($this->isInvalidAjaxRequest($request)) {
            return $this->error403();
        }

        /** @var CategoriesModel $categories_model */
        $categories_model = Model::load('Categories');

        $blog_id = $this->getBlogId($request);

        $json = array('status' => 0);

        if (!$request->isValidSig()) {
            $this->setContentType("application/json; charset=utf-8");
            $this->setStatusCode(404);
            $this->set('json', ['error' => 'invalid sig']);
            return "admin/common/json.twig";
        }

        $category_request = $request->get('category');
        $category_request['blog_id'] = $blog_id;
        $errors = $categories_model->validate($category_request, $data, array('parent_id', 'name'));
        if (empty($errors)) {
            $data['blog_id'] = $blog_id;
            if ($id = $categories_model->addNode($data, 'blog_id=?', array($blog_id))) {
                $json['status'] = 1;
                $json['category'] = array(
                    'id' => $id,
                    'parent_id' => $data['parent_id'],
                    'name' => $data['name'],
                );
            }
        }

        $json['error'] = $errors;

        $this->setContentType("application/json; charset=utf-8");
        $this->set('json', $json);
        return "admin/common/json.twig";
    }

}

