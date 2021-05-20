<?php
declare(strict_types=1);

namespace Fc2blog\Repo;

use Fc2blog\Model\Email;

// The mailer for development environments or environments where mail cannot be sent
class ErrorLogMailer implements MailerInterface
{
    public function send(Email $email): bool
    {
        file_put_contents("php://stderr", print_r($email, true));
        return true;
    }
}
