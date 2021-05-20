<?php
declare(strict_types=1);

namespace Fc2blog\Repo;

use Fc2blog\Model\Email;

interface MailerInterface
{
    public function send(Email $email): bool;
}
