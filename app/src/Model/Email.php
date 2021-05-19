<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use Twig\Environment;

class Email
{
    public $to;
    public $toName;
    public $from;
    public $fromName;
    public $subject;
    public $body;

    public function setSubjectAndBodyByTwig(
        Environment $twig,
        string $twig_template,
        array $template_params = []): void
    {
        $mail_sub_and_body = $twig->render($twig_template, $template_params);
        [$subject, $body] = explode("\n==\n", $mail_sub_and_body, 2);
        $this->subject = $subject;
        $this->body = $body;
    }

    public function setTo(string $email_addr, string $name = ""): void
    {
        $this->to = $email_addr;
        $this->toName = $name;
    }

    public function setFrom(string $email_addr, string $name = ""): void
    {
        $this->from = $email_addr;
        $this->fromName = $name;
    }
}
