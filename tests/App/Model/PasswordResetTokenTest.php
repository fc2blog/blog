<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model;

use Fc2blog\Model\PasswordResetToken;
use Fc2blog\Model\PasswordResetTokenService;
use Fc2blog\Service\UserService;
use PHPUnit\Framework\TestCase;

class PasswordResetTokenTest extends TestCase
{
    public function testInsert(): void
    {
        $user = UserService::getByLoginId('testadmin');
        $token = PasswordResetToken::factoryWithUser($user);

//        var_dump($token);

        $last_insert_id = PasswordResetTokenService::create($token);

        $this->assertIsInt($last_insert_id);

        $token2 = PasswordResetTokenService::getByToken($token->token);

//        var_dump($token2);

        $this->assertInstanceOf(PasswordResetToken::class, $token2);
    }

    public function testDelete(): void
    {
        $user = UserService::getByLoginId('testadmin');
        $token = PasswordResetToken::factoryWithUser($user);
        $last_insert_id = PasswordResetTokenService::create($token);
        $this->assertIsInt($last_insert_id);

        $token2 = PasswordResetTokenService::getByToken($token->token);

        PasswordResetTokenService::revokeToken($token2);

        $token3 = PasswordResetTokenService::getByToken($token->token);

        $this->assertNull($token3);
    }
}
