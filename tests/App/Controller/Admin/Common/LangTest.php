<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\Common;

use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class LangTest extends TestCase
{
    use ClientTrait;

    public function testChangeLanguageByRequest(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();

        // Ja表記を確認
        $c = $this->reqGet("/admin/session/login");
        $this->assertStringContainsString("管理画面へログイン", $c->getOutput());

        // enに切り替えをリクエスト
        $this->reqGetBeRedirect("/admin/common/lang", ["lang" => "en"]);
        $this->assertEquals('en', $this->clientTraitCookie['lang']);

        // enか確認
        $c = $this->reqGet("/admin/session/login");
        $this->assertStringContainsString("Login to Administration page", $c->getOutput());

        // jaに切り替えをリクエスト
        $this->reqGetBeRedirect("/admin/common/lang", ["lang" => "ja"]);
        $this->assertEquals('ja', $this->clientTraitCookie['lang']);

        // jaか確認
        $c = $this->reqGet("/admin/session/login");
        $this->assertStringContainsString("管理画面へログイン", $c->getOutput());
    }

    public function testChangeLanguageByCookie(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();

        // jaに設定
        $this->reqGetBeRedirect("/admin/common/lang", ["lang" => "ja"]);
        $this->assertEquals('ja', $this->clientTraitCookie['lang']);

        // jaか確認
        $c = $this->reqGet("/admin/session/login");
        $this->assertStringContainsString("管理画面へログイン", $c->getOutput());

        // enに設定
        $this->clientTraitCookie['lang'] = "en";
        $c = $this->reqGet("/admin/session/login");
        $this->assertStringContainsString("Login to Administration page", $c->getOutput());

        // enか確認
        $c = $this->reqGet("/admin/session/login");
        $this->assertStringContainsString("Login to Administration page", $c->getOutput());
    }
}
