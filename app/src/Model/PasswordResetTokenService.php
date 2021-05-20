<?php
declare(strict_types=1);

namespace Fc2blog\Model;

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

    // TODO clean up token.
}
