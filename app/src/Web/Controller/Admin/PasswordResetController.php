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

        // Avoid abuse use to mass mail sending.
        $request->generateNewSig();

        // Show same complete page if user exists or not exists.
        if (!is_null($user = UserService::getByLoginId($login_id))) {
            if (defined("EMERGENCY_PASSWORD_RESET_ENABLE") && EMERGENCY_PASSWORD_RESET_ENABLE === "1") {
                // Show password reset form directly when EMERGENCY_PASSWORD_RESET_ENABLE is "1".
                // This is a emergency feature for Unable to send mail enviroment.
                $token = PasswordResetToken::factoryWithUser($user);
                PasswordResetTokenService::create($token);
                $this->set('token', $token->token);
                return 'admin/password_reset/reset_form.twig';
            }

            $result = PasswordResetTokenService::createAndSendToken($request, $user);
            if ($result === false) {
                return 'admin/password_reset/mail_sending_error.twig';
            }
        }

        // Avoid abuse use to mass mail sending.
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
