<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use Fc2blog\App;

class PasswordResetToken
{
    public $id;
    public $user_id;
    public $token;
    public $expire_at;
    public $updated_at;
    public $created_at;

    public static function factoryWithUser(User $user): self
    {
        $self = new static();
        $self->user_id = $user->id;
        $self->token = App::genRandomString();
        $self->expire_at = date("Y-m-d H:i:s", time() + 60 * 60 * 24);
        $self->updated_at = date("Y-m-d H:i:s");
        $self->created_at = date("Y-m-d H:i:s");
        return $self;
    }

    public static function factoryFromArray(array $list): self
    {
        $self = new static();
        $self->id = $list['id'];
        $self->user_id = $list['user_id'];
        $self->token = $list['token'];
        $self->expire_at = $list['expire_at'];
        $self->updated_at = $list['updated_at'];
        $self->created_at = $list['created_at'];
        return $self;
    }

    public function isExpired(): bool
    {
        return strtotime($this->expire_at) < time();
    }
}
