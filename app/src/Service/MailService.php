<?php
declare(strict_types=1);

namespace Fc2blog\Service;

use Fc2blog\Model\Email;
use Fc2blog\Repo\MailerInterface;
use Fc2blog\Repo\SendmailMailer;

class MailService
{
    public static $mailerClassName = SendmailMailer::class;

    public static function send(Email $email): bool
    {
        $mailer = static::getMailer();
        return $mailer->send($email);
    }

    public static function getMailer(): MailerInterface
    {
        return new static::$mailerClassName();
    }
}
