<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

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
            error_log("password reset requested:{$login_id}");

            // TODO create password reset token

            // TODO send mail

        }
        // Avoid brute force account search attack.
        sleep(3);

        return 'admin/password_reset/requested.twig';
    }

    public function resetForm(Request $request): string
    {
        //TODO
    }

    public function reset(Request $request): string
    {
        //TODO
    }
}
