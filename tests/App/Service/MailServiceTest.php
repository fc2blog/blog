<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Service;

use Fc2blog\Model\Email;
use Fc2blog\Service\MailService;
use Fc2blog\Service\TwigService;
use PHPUnit\Framework\TestCase;

class MailServiceTest extends TestCase
{
    public function testSendMail(): void
    {
        $email = new Email();
        $email->setFrom("test_from@example.jp", "テスト送信主");
        $email->setTo("test_to@example.jp", "テスト送信先");
        $email->setSubjectAndBodyByTwig(
            TwigService::getTwigInstance(),
            'mail/password_recovery_request.twig',
            ['url' => 'http://example.jp']
        );
        $this->assertStringContainsString('テスト送信主', print_r($email, true));
        $this->assertStringContainsString('テスト送信先', print_r($email, true));
        $this->assertStringContainsString('test_from@example.jp', print_r($email, true));
        $this->assertStringContainsString('test_to@example.jp', print_r($email, true));
        $this->assertStringContainsString('http://example.jp', print_r($email, true));

        MailService::send($email);

        $this->assertTrue(true); // 到達できればOK
    }
}
