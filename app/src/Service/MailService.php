<?php
declare(strict_types=1);

namespace Fc2blog\Service;

use Fc2blog\Model\Email;
use Fc2blog\Repo\MailerInterface;
use Fc2blog\Repo\StdErrOutputMailer;

class MailService
{
    public static function send(Email $email): bool
    {
        $mailer = static::getMailer();
        return $mailer->send($email);
    }

    public static function getMailer(): MailerInterface
    {
        return defined('MAILER_CLASS_NAME') && strlen(MAILER_CLASS_NAME) > 0 ?
            new ('\\Fc2blog\\Repo\\' . MAILER_CLASS_NAME)() :
            new StdErrOutputMailer();
    }
}
