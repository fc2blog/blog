<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\UsersModel;
use Fc2blog\Service\UserService;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

class SessionController extends AdminController
{
    /**
     * ログインフォーム
     * @param Request $request
     * @return string
     */
    public function login(Request $request): string
    {
        if ($this->isLogin()) {
            // ログイン済みならトップページへリダイレクト
            $this->redirect($request, $request->baseDirectory);
        }

        $request->generateNewSig();

        // 初期表示時
        return 'admin/session/login.twig';
    }

    /**
     * ログイン処理
     * @param Request $request
     * @return string
     */
    public function doLogin(Request $request): string
    {
        if (!$request->isValidPost()) {
            return $this->error400();
        }

        if ($this->isLogin()) {
            // ログイン済みならトップページへリダイレクト
            $this->redirect($request, $request->baseDirectory);
        }

        // ログインフォームのバリデート
        $users_model = new UsersModel();
        $errors = $users_model->loginValidate($request->get('user'), $data, ['login_id', 'password']);
        if (!empty($errors)) {
            $this->setErrorMessage(__('Input error exists'));
            $this->set('errors', $errors);
            return 'admin/session/login.twig';
        }

        // ユーザー認証
        if (is_null($user = UserService::getByLoginIdAndPassword($data['login_id'], $data['password']))) {
            // Penalty for failed login
            sleep(3);
            $this->set('errors', ['login_id' => __('Login ID or password is incorrect')]);
            return 'admin/session/login.twig';
        }

        // ログイン処理
        $blog = (new BlogsModel())->getLoginBlog($user);
        $this->loginProcess($user, $blog);
        $users_model->updateById(['logged_at' => date('Y-m-d H:i:s')], $user['id']);

        if (!$this->isSelectedBlog()) {
            $this->redirect($request, ['controller' => 'Blogs', 'action' => 'create']);
            return "";
        } else {
            $this->redirect($request, $request->baseDirectory);   // トップページへリダイレクト
            return "";
        }
    }

    /**
     * ログアウト
     * @param Request $request
     * @return string
     */
    public function logout(Request $request): string
    {
        // TODO refactoring
        if ($this->isLogin()) {
            Session::destroy($request);
        }
        $this->redirect($request, ['controller' => 'Session', 'action' => 'login']);
        return "";
    }
}

