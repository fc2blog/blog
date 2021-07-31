<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Model\CategoriesModel;
use Fc2blog\Model\Model;
use Fc2blog\Web\Request;

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

        $blog_id = $this->getBlogIdFromSession();

        $this->set('categories_model_order_list', $categories_model::getOrderList());
        $this->set('categories', $categories_model->getList($this->getBlogIdFromSession()));

        // 親カテゴリー一覧
        $options = $categories_model->getParentList($blog_id);
        $this->set('category_parents', [0 => ''] + $options);

        // カテゴリ登録数
        $create_limit = CategoriesModel::CATEGORY['CREATE_LIMIT'];
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
        if (!$request->isPost()) return $this->error400();
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
        $blog_id = $this->getBlogIdFromSession();

        // 親カテゴリー一覧
        $options = $categories_model->getParentList($blog_id, $id);
        $this->set('category_parents', [0 => ''] + $options);
        $this->set('categories_model_order_list', $categories_model::getOrderList());

        // 初期表示時に編集データの取得&設定
        if (!$request->get('category') || !$request->isValidSig()) {
            if (!$category = $categories_model->findByIdAndBlogId($id, $blog_id)) {
                $this->redirect($request, ['action' => 'create']);
            }
            $request->set('category', $category);
            return "admin/categories/edit.twig";
        }

        // 更新処理
        if (!$request->isPost()) return $this->error400();
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
     * @return string
     */
    public function delete(Request $request): string
    {
        if (!$request->isValidPost()) {
            return $this->error400();
        }

        $id = $request->get('id');
        $blog_id = $this->getBlogIdFromSession();

        // 未分類であるid=1は削除させない
        if ($id === "1") {
            return $this->error400();
        }
        // 削除データの取得
        $categories_model = Model::load('Categories');
        if (!$categories_model->findByIdAndBlogId($id, $blog_id)) {
            $this->redirect($request, array('action' => 'create'));
            return "";
        }

        // 削除処理
        $categories_model->deleteNodeByIdAndBlogId($id, $blog_id);
        $this->setInfoMessage(__('I removed the category'));
        $this->redirect($request, array('action' => 'create'));
        return "";
    }

    /**
     * ajax用のカテゴリ追加 admin/entries/create からインクルード
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function ajax_add(Request $request): string
    {
        if (!$request->isValidPost() || $this->isInvalidAjaxRequest($request)) {
            $this->setContentType("application/json; charset=utf-8");
            $this->setStatusCode(400);
            $this->set('json', ['status' => 0, 'error' => 'invalid sig']);
            return "admin/common/json.twig";
        }

        $category_request = $request->get('category');
        $blog_id = $this->getBlogIdFromSession();
        $category_request['blog_id'] = $blog_id;
        /** @var CategoriesModel $categories_model */
        $categories_model = Model::load('Categories');
        $errors = $categories_model->validate($category_request, $data, array('parent_id', 'name'));
        if (empty($errors)) {
            $data['blog_id'] = $blog_id;
            if ($id = $categories_model->addNode($data, 'blog_id=?', array($blog_id))) {
                $json = [
                    'status' => 1,
                    'category' => [
                        'id' => $id,
                        'parent_id' => $data['parent_id'],
                        'name' => $data['name'],
                    ],
                ];
                $this->setContentType("application/json; charset=utf-8");
                $this->set('json', $json);
                return "admin/common/json.twig";
            } else {
                return $this->error500();
            }
        }

        $this->setContentType("application/json; charset=utf-8");
        $this->set('json', ['status' => 0, 'error' => $errors]);
        return "admin/common/json.twig";
    }
}
