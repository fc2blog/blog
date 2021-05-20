<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Model\PasswordResetToken;
use Fc2blog\Model\PasswordResetTokenService;
use Fc2blog\Service\UserService;
use Fc2blog\Web\Request;

class PasswordResetController extends AdminController
{
    public function requestForm(Request $request): string
    {
        // 未ログインだとSigが生成されていないため、ここで生成する。
        // 複数個ウインドウを開くとsigがエラーになるが、ありえないかと思われる。
        $request->generateNewSig();
        return 'admin/password_reset/request_form.twig';
    }

    public function request(Request $request): string
    {
        if (!$request->isValidPost()) {
            return $this->error400();
        }

        $login_id = $request->get('login_id');

        // Avoid some mass mail sending attack.
        $request->generateNewSig();

        if (!is_null($user = UserService::getByLoginId($login_id))) {
            $token = PasswordResetToken::factoryWithUser($user);
            PasswordResetTokenService::create($token);

//            error_log("password reset token: " . print_r($token, true));

            // TODO send mail
        }
        // Avoid brute force account search attack.
        sleep(3);

        return 'admin/password_reset/requested.twig';
    }

    public function resetForm(Request $request): string
    {
        $token_str = $request->get('token');
        $token = PasswordResetTokenService::getByToken($token_str);

        if (is_null($token) || $token->isExpired()) {
            return 'admin/password_reset/expired.twig';
        }

        $this->set('token', $token->token);
        return 'admin/password_reset/reset_form.twig';
    }

    public function reset(Request $request): string
    {
        if (!$request->isValidPost()) {
            return $this->error400();
        }

        $token_str = $request->get('token');
        $new_password = $request->get('password');

        $token = PasswordResetTokenService::getByToken($token_str);
        if (is_null($token) || $token->isExpired()) {
            return 'admin/password_reset/expired.twig';
        }

        if (strlen($new_password) < 8) {
            $this->set('token', $token->token);
            $this->set('errors', ['password' => __("Password is too short (least 6chars)")]);
            return 'admin/password_reset/reset_form.twig';
        }

        $user = UserService::getById($token->user_id);
        if (is_null($user)) {
            return 'admin/password_reset/expired.twig';
        }

        UserService::updatePassword($user, $new_password);
        PasswordResetTokenService::revokeToken($token);

        return 'admin/password_reset/complete.twig';
    }
}
