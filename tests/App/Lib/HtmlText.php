<?php

declare(strict_types=1);

namespace Fc2blog\Tests\App\Lib;

use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class HtmlText extends TestCase
{
    public function testUndefined(): void
    {
        $req = new Request("POST", "/", [], ['test' => ['some' => "value"]]);
        $html = Html::input($req, 'test[some]', 'UNDEFINED');
        $this->assertEquals('<span>UNDEFINEDは未実装です</span>', $html);
    }

    public function testText(): void
    {
        $req = new Request("POST", "/", [], ['blog_plugin' => ['device_type' => "sp"]]);
        $html = Html::input($req, 'blog_plugin[device_type]', 'text');
        $this->assertEquals('<input type="text" name="blog_plugin[device_type]" value="sp"/>', $html);
    }

    public function testPassowrd(): void
    {
        $req = new Request("POST", "/", [], ['test' => ['pass' => "PASS"]]);
        $html = Html::input($req, 'test[pass]', 'password');
        $this->assertEquals('<input type="password" name="test[pass]" value="PASS"/>', $html);
    }

    public function testBlankPassowrd(): void
    {
        $req = new Request("POST", "/", [], ['test' => ['pass' => "WILL_BE_IGNORE"]]);
        $html = Html::input($req, 'test[pass]', 'blank_password');
        $this->assertEquals('<input type="password" name="test[pass]" value=""/>', $html);
    }

    public function testFile(): void
    {
        $req = new Request("POST", "/");
        $html = Html::input($req, 'test[some]', 'file');
        $this->assertEquals('<input type="file" name="test[some]" />', $html);
    }

    public function testHidden(): void
    {
        $req = new Request("POST", "/", [], ['blog_plugin' => ['device_type' => "sp"]]);
        $html = Html::input($req, 'blog_plugin[device_type]', 'hidden');
        $this->assertEquals('<input type="hidden" name="blog_plugin[device_type]" value="sp"/>', $html);
    }

    public function testToken(): void
    {
        $req = new Request("POST", "/");
        $html = Html::input($req, 'test[pass]', 'token');
        $this->assertEquals('<input type="hidden" name="test[pass]" value=""/>', $html);
    }

    public function testCaptcha(): void
    {
        $req = new Request("POST", "/");
        $html = Html::input($req, 'test[captcha]', 'captcha');
        $this->assertEquals('<input type="text" name="test[captcha]" value=""/>', $html);
    }

    public function testSelect(): void
    {
        $req = new Request("POST", "/", null, ['test' => ['name' => "B"]]);
        $html = Html::input($req, 'test[name]', 'select', ['options' => ["A" => 'a', "B" => 'b', "C" => 'c']]);
        $this->assertTrue(
            $this->isEqualStringsIgnoringVariousOfWhiteSpaces(
                '<select name="test[name]"><option value="A" >a</option><option value="B" selected="selected">b</option><option value="C" >c</option></select>',
                $html
            )
        );

        $req = new Request("POST", "/", null, ['test' => ['name' => "B"]]);
        $html = Html::input($req, 'test[name]', 'select', [
            'options' => array(
                0 => '',
                1 => array(
                    'value' => '未分類',
                    'level' => 1,
                    'disabled' => true,
                ),
                2 => array(
                    'value' => 'テストカテゴリ',
                    'level' => 1,
                ),
                3 => array(
                    'value' => 'テストカテゴリ-2',
                    'level' => 2,
                ),
                4 => array(
                    'value' => 'テストカテゴリ-3',
                    'level' => 3,
                ),
                6 => array(
                    'value' => 'テストカテゴリ-4',
                    'level' => 3,
                ),
                5 => array(
                    'value' => 'テストカテゴリ-5',
                    'level' => 1,
                ),
            )
        ]);
        $this->assertTrue(
            $this->isEqualStringsIgnoringVariousOfWhiteSpaces(
                '<select name="test[name]"><option value="0" ></option><option value="1"  disabled="disabled" >未分類</option><option value="2" >テストカテゴリ</option><option value="3" >&nbsp;&nbsp;&nbsp;テストカテゴリ-2</option><option value="4" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;テストカテゴリ-3</option><option value="6" >&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;テストカテゴリ-4</option><option value="5" >テストカテゴリ-5</option></select>',
                $html
            )
        );
    }

    public function isEqualStringsIgnoringVariousOfWhiteSpaces($str1, $str2): bool
    {
//        echo PHP_EOL;
//        echo preg_replace("/[ ]+/u", "", preg_replace("/[ \t\r\n]+/u", " ", $str1));
//        echo PHP_EOL;
//        echo preg_replace("/[ ]+/u", "", preg_replace("/[ \t\r\n]+/u", " ", $str2));
//        echo PHP_EOL;

        return (
            preg_replace("/[ \t\r\n]+/u", "", $str1)
            ==
            preg_replace("/[ \t\r\n]+/u", "", $str2)
        );
    }

    public function testCheckbox(): void
    {
        $req = new Request("POST", "/", null, ['test' => ['name' => ["A", "B"]]]);
        $html = Html::input($req, 'test[name]', 'checkbox', ['options' => ["A" => 'a', "B" => 'b', "C" => 'c']]);
        $this->assertEquals(
            '<input type="checkbox" value="A" checked="checked" name="test[name][]" id="sys-checkbox-test-name-A" /><label for="sys-checkbox-test-name-A">a</label><input type="checkbox" value="B" checked="checked" name="test[name][]" id="sys-checkbox-test-name-B" /><label for="sys-checkbox-test-name-B">b</label><input type="checkbox" value="C"  name="test[name][]" id="sys-checkbox-test-name-C" /><label for="sys-checkbox-test-name-C">c</label>',
            $html
        );

        $req = new Request("POST", "/", null, []);
        $html = Html::input($req, 'test[name]', 'checkbox', ['options' => ["A" => 'a']]);
        $this->assertEquals(
            '<input type="checkbox" value="A"  name="test[name][]" id="sys-checkbox-test-name-A" /><label for="sys-checkbox-test-name-A">a</label>',
            $html
        );
    }

}
