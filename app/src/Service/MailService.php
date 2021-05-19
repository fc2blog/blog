<?php
declare(strict_types=1);

namespace Fc2blog\Service;

use Fc2blog\Model\Email;

class MailService
{
    public static function send(Email $email): bool
    {
        // TODO this is mock
        error_log(print_r($email, true));
        return true;
    }
}
