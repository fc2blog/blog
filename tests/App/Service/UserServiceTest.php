<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Service;

use Fc2blog\Model\User;
use Fc2blog\Service\UserService;
use PHPUnit\Framework\TestCase;

class UserServiceTest extends TestCase
{
    public function testUpdatePassword(): void
    {
        $login_id = 'testadmin@localhost';

        $user = UserService::getByLoginId($login_id);
        $this->assertInstanceOf(User::class, $user);

        $old_pass_hash = $user->password;

        $user->setPassword('testadmin@localhost1');
        UserService::update($user);

        $user_new = UserService::getByLoginId($login_id);
        $this->assertNotEquals($old_pass_hash, $user_new->password);

        $this->assertTrue($user_new->verifyPassword('testadmin@localhost1'));
        $this->assertFalse($user_new->verifyPassword('testadmin@localhost'));

        UserService::updatePassword($user, 'testadmin@localhost');
    }
}
