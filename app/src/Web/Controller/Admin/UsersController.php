<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\Model;
use Fc2blog\Model\UsersModel;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

class UsersController extends AdminController
{
    /**
     * 一覧表示(デバッグ用)
     * @param Request $request
     * @return string
     */
    public function index(Request $request)
    {
        if (!$this->isAdmin()) {
            return $this->error404();
        }

        $options = array(
            'limit' => Config::get('PAGE.USER.LIMIT', 10),
            'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
            'order' => 'id DESC',
        );
        $users_model = Model::load('Users');
        $users = $users_model->find('all', $options);
        $blogs_model = new BlogsModel();
        foreach ($users as &$user) {
            $user['blog_list'] = $blogs_model->findByUserId($user['id']);
        }
        $paging = $users_model->getPaging($options);

        $this->set('users', $users);
        $this->set('paging', $paging);
        return 'admin/users/index.twig';
    }

    /**
     * 新規作成
     * @param Request $request
     * @return string|void
     */
    public function register(Request $request)
    {
        if (Config::get('USER.REGIST_SETTING.FREE') != Config::get('USER.REGIST_STATUS')) {
            return $this->error404();
        }

        // 初期表示時
        if (!$request->get('user')) {
            return;
        }

        /** @var UsersModel $users_model */
        $users_model = Model::load('Users');
        /** @var BlogsModel $blogs_model */
        $blogs_model = Model::load('Blogs');

        // ユーザーとブログの新規登録処理
        $errors = array();
        $errors['user'] = $users_model->registerValidate($request->get('user'), $user_data, array('login_id', 'password'));
        $errors['blog'] = $blogs_model->validate($request->get('blog'), $blog_data, array('id', 'name', 'nickname'));
        if (empty($errors['user']) && empty($errors['blog'])) {
            $blog_data['user_id'] = $users_model->insert($user_data);
            if ($blog_data['user_id'] && $blog_id = $blogs_model->insert($blog_data)) {
                $this->setInfoMessage(__('User registration is completed'));
                $this->redirect($request, array('action' => 'login'));
            } else {
                // ブログ作成失敗時には登録したユーザーを削除
                $users_model->deleteById($blog_data['user_id']);
            }
            $this->setErrorMessage(__('I failed to register'));
            return;
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);
    }

    /**
     * ユーザー情報変更処理
     * @param Request $request
     * @return string
     */
    public function edit(Request $request): string
    {
        $users_model = new UsersModel();
        $user_id = $this->getUserId();
        $this->set('tab', 'edit');

        // 初期表示時に編集データの取得&設定
        if (!$request->get('user') || !$request->isValidSig()) {
            $user = $users_model->findById($user_id);
            unset($user['password']);
            $request->set('user', $user);
            $blogs_model = new BlogsModel();
            $this->set('blogs_list', $blogs_model->getListByUserId($user_id));
            return 'admin/users/edit.twig';
        }

        // 更新処理
        $errors = [];
        $white_list = ['password', 'login_blog_id'];
        $errors['user'] = $users_model->updateValidate($request->get('user'), $data_user, $white_list);
        if (empty($errors['user'])) {
            if ($users_model->updateById($data_user, $user_id)) {
                $this->setInfoMessage(__('Update User Information'));
                $this->redirect($request, ['action' => 'edit']);
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);

        return 'admin/users/edit.twig';
    }

    /**
     * 退会
     * @param Request $request
     * @return string
     */
    public function withdrawal(Request $request): string
    {
        $this->set('tab', 'withdrawal');

        // 退会チェック
        if (!$request->get('user.delete') || !$request->isValidSig()) {
            return 'admin/users/withdrawal.twig';
        }

        // 削除処理
        Model::load('Users')->deleteById($this->getUserId());
        $this->setInfoMessage(__('Was completed withdrawal'));
        if ($this->isLogin()) {
            Session::destroy($request);
        }

        $this->redirect($request, ['controller' => 'session', 'action' => 'login']);
        return "";
    }
}

