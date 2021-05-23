<?php
declare(strict_types=1);

namespace Fc2blog\Repo;

use Exception;
use Fc2blog\Model\Email;
use Swift_Mailer;
use Swift_Message;
use Swift_RfcComplianceException;
use Swift_SendmailTransport;

class SendmailMailer implements MailerInterface
{
    public static $sendmailPath = SENDMAIL_PATH;

    public function send(Email $email): bool
    {
        if (strlen(static::$sendmailPath) === 0) {
            static::$sendmailPath = ini_get('sendmail_path');
        }
        $transport = new Swift_SendmailTransport(static::$sendmailPath);
        $mailer = new Swift_Mailer($transport);

        try {
            $message = (new Swift_Message())
                ->setSubject($email->subject)
                ->setFrom([$email->from => $email->fromName])
                ->setTo([$email->to => $email->toName])
                ->setBody($email->body);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (Swift_RfcComplianceException $e) {
            error_log("mail address is invalid. please check login_id or FC2_ADMIN_MAIL_ADDRESS configuration " . $e->getMessage() . " " . print_r($email, true));
            return false;
        }

        try {
            $resultNum = $mailer->send($message);
            return $resultNum > 0;
        } catch (Exception $e) {
            // ignore mail sending error.
            error_log("mail send failed. please check sendmail configuration. " . $e->getMessage() . " " . print_r($email, true));
            return false;
        }
    }
}
