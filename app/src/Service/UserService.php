<?php
declare(strict_types=1);

namespace Fc2blog\Service;

use Fc2blog\Model\User;
use Fc2blog\Model\UsersModel;

class UserService
{
    public static function getByLoginId(string $login_id): ?User
    {
        $repo = new UsersModel();
        return User::factory($repo->findByLoginId($login_id));
    }

    public static function updatePassword(User $user, string $password): bool
    {
        $user->setPassword($password);
        return UserService::update($user);
    }

    public static function update(User $user): bool
    {
        $repo = new UsersModel();
        return $repo->updateById($user->asArray(), $user->id) === 1;
    }
}
