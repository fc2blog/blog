<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Config;
use Fc2blog\Model\EmailLoginTokenService;
use Fc2blog\Model\UsersModel;
use Fc2blog\Service\UserService;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use Twig\Error\Error;

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
            $this->setErrorMessage(__('Input error exists'));
            $this->set('errors', ['login_id' => __('Login ID or password is incorrect')]);
            return 'admin/session/login.twig';
        }

        // MFA処理
        if (Config::get('MFA_EMAIL') === "1") {
            // create and send login mail
            try {
                if (false === EmailLoginTokenService::createAndSendToken($request, $user)) {
                    return "admin/email_login/mail_sending_error.twig";
                }
            } catch (Error $e) {
                return 'admin/email_login/mail_sending_error.twig';
            }
            // MFA必須の場合はメール経由でmailLogin()へ
            return "admin/email_login/requested.twig";
        }

        // ログイン処理
        $this->loginProcess($user);

        if (!$this->isSelectedBlog()) {
            $this->redirect($request, ['controller' => 'Blogs', 'action' => 'create']);
        } else {
            $this->redirect($request, $request->baseDirectory);   // トップページへリダイレクト
        }
        return "";
    }

    /**
     * ログイン処理
     * @param Request $request
     * @return string
     */
    public function mailLogin(Request $request): string
    {
        // get&check login token
        $token_str = $request->get('token');

        $token = EmailLoginTokenService::getByToken($token_str);

        // ok / ng
        if (is_null($token) || $token->isExpired()) {
            return "admin/email_login/expired.twig";
        }

        EmailLoginTokenService::revokeToken($token);

        $user = UserService::getById($token->user_id);

        if (is_null($user)) {
            return "admin/email_login/expired.twig";
        }

        // ログイン処理
        $this->loginProcess($user);

        if (!$this->isSelectedBlog()) {
            $this->redirect($request, ['controller' => 'Blogs', 'action' => 'create']);
        } else {
            $this->redirect($request, $request->baseDirectory);   // トップページへリダイレクト
        }
        return "";
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

