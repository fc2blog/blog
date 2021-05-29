<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use ArrayAccess;
use Countable;
use IteratorAggregate;

class User implements ArrayAccess, IteratorAggregate, Countable
{
    use ArrayIterableTrait;

    public $id;
    public $login_id;
    public $password;
    public $login_blog_id;
    public $type;
    public $created_at;
    public $logged_at;

    public function setPassword(string $password): void
    {
        $this->password = UsersModel::passwordHash($password);
    }

    public function verifyPassword(string $input_password): bool
    {
        return password_verify($input_password, $this->password);
    }
}
