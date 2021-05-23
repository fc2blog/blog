<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use ArrayAccess;
use Countable;
use IteratorAggregate;

class Blog implements ArrayAccess, IteratorAggregate, Countable
{
    use ArrayIterableTrait;

    public $id;
    public $user_id;
    public $name;
    public $nickname;
    public $introduction;
    public $template_pc_id;
    public $template_sp_id;
    public $timezone;
    public $open_status;
    public $ssl_enable;
    public $redirect_status_code;
    public $blog_password;
    public $trip_salt;
    public $last_posted_at;
    public $created_at;
    public $updated_at;
}
