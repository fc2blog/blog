<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use Fc2blog\Service\MailService;
use Fc2blog\Service\TwigService;
use Fc2blog\Web\Request;
use RuntimeException;
use Twig\Error\Error;

class PasswordResetTokenService
{
    public static function create(PasswordResetToken $token): ?int
    {
        $pr_model = new PasswordResetTokenModel();
        return $pr_model->insert((array)$token);
    }

    public static function getByToken(string $token): ?PasswordResetToken
    {
        $pr_model = new PasswordResetTokenModel();
        $result = $pr_model->find('row', [
            'where' => 'token = :token',
            'params' => ['token' => $token]
        ]);

        if ($result === false) return null;

        return PasswordResetToken::factoryFromArray($result);
    }

    public static function revokeToken(PasswordResetToken $token): void
    {
        $pr_model = new PasswordResetTokenModel();
        $pr_model->deleteById($token->id);
    }

    /**
     * @throws Error
     */
    public static function createAndSendToken(Request $request, User $user): bool
    {
        // create token
        $token = PasswordResetToken::factoryWithUser($user);
        if (is_null(static::create($token))) {
            throw new RuntimeException("create password reset token failed.");
        }

        // mail sending.
        $reset_url = $token->getResetUrl($request);

        $email = new Email();
        $email->setFrom(
            ADMIN_MAIL_ADDRESS,
            "Fc2blog OSS"
        );
        $email->setTo($user->login_id);
        $email->setSubjectAndBodyByTwig(
            TwigService::getTwigInstance(),
            'mail/password_recovery_request.twig',
            ['url' => $reset_url]
        );
        return MailService::send($email);
    }

    // TODO clean up token.
}
