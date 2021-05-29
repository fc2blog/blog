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

    public static function getByLoginIdAndPassword(string $login_id, string $password): ?User
    {
        $repo = new UsersModel();
        $row = $repo->findByLoginId($login_id);
        if ($row === false) return null;
        $user = User::factory($row);
        return !is_null($user) && $user->verifyPassword($password) ? $user : null;
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

    public static function getById(int $user_id): ?User
    {
        $repo = new UsersModel();
        $res = $repo->findById($user_id);
        if ($res === false) return null;
        return User::factory($res);
    }
}
