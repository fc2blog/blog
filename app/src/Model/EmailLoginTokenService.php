<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use Fc2blog\Config;
use Fc2blog\Service\MailService;
use Fc2blog\Service\TwigService;
use Fc2blog\Web\Request;
use RuntimeException;

class EmailLoginTokenService
{
    public static function create(EmailLoginToken $token): ?int
    {
        $pr_model = new EmailLoginTokenModel();
        return $pr_model->insert((array)$token);
    }

    public static function getByToken(string $token): ?EmailLoginToken
    {
        $pr_model = new EmailLoginTokenModel();
        $result = $pr_model->find('row', [
            'where' => 'token = :token',
            'params' => ['token' => $token]
        ]);

        if ($result === false) return null;

        return EmailLoginToken::factoryFromArray($result);
    }

    public static function revokeToken(EmailLoginToken $token): void
    {
        $pr_model = new EmailLoginTokenModel();
        $pr_model->deleteById($token->id);
    }

    public static function createAndSendToken(Request $request, User $user): bool
    {
        // create token
        $token = EmailLoginToken::factoryWithUser($user);
        if (is_null(static::create($token))) {
            throw new RuntimeException("create password reset token failed.");
        }

        // mail sending.
        $login_url = $token->getLoginUrl($request);

        $email = new Email();
        $email->setFrom(
            Config::get("ADMIN_MAIL_ADDRESS"),
            "Fc2blog OSS"
        );
        $email->setTo($user->login_id);
        $email->setSubjectAndBodyByTwig(
            TwigService::getTwigInstance(),
            'mail/email_login.twig',
            ['url' => $login_url]
        );
        return MailService::send($email);
    }

    // TODO clean up token.
}
