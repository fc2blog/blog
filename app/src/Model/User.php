<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Iterator;
use IteratorAggregate;
use ReflectionClass;
use ReflectionException;

class User implements ArrayAccess, IteratorAggregate, Countable
{
    public $id;
    public $login_id;
    public $password;
    public $login_blog_id;
    public $type;
    public $created_at;
    public $logged_at;

    public static function factory($list): ?self
    {
        if (!is_array($list)) {
            return null;
        }
        $self = new static();
        $self->id = $list['id'];
        $self->login_id = $list['login_id'];
        $self->password = $list['password'];
        $self->login_blog_id = $list['login_blog_id'];
        $self->type = $list['type'];
        $self->created_at = $list['created_at'];
        $self->logged_at = $list['logged_at'];
        return $self;
    }

    public function asArray(): array
    {
        return (array)$this;
    }

    public function setPassword(string $password): void
    {
        $this->password = UsersModel::passwordHash($password);
    }

    public function verifyPassword(string $input_password): bool
    {
        return password_verify($input_password, $this->password);
    }

    // ==== for array access ====
    public function offsetExists($offset): bool
    {
        return isset($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->offsetExists($offset) ? $this->{$offset} : null;
    }

    public function offsetSet($offset, $value)
    {
        $r = new ReflectionClass(static::class);
        try {
            $prop = $r->getProperty($offset);
            if ($prop->isPublic()) {
                $this->{$prop->getName()} = $value;
            }
        } catch (ReflectionException $e) {
        }
    }

    public function offsetUnset($offset)
    {
        // don't un-settable
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator((array)$this);
    }

    public function count(): int
    {
        $r = new ReflectionClass(static::class);
        return count($r->getProperties());
    }
}
