<?php

declare(strict_types=1);

namespace Fc2blog\Tests\App\Lib;

use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class HtmlText extends TestCase
{
    public function testText(): void
    {
        $req = new Request("POST", "/", [], ['blog_plugin' => ['device_type' => "sp"]]);
        $html = Html::input($req, 'blog_plugin[device_type]', 'text');
        $this->assertEquals('<input type="text" name="blog_plugin[device_type]" value="sp"/>', $html);
    }

    public function testHidden(): void
    {
        $req = new Request("POST", "/", [], ['blog_plugin' => ['device_type' => "sp"]]);
        $html = Html::input($req, 'blog_plugin[device_type]', 'hidden');
        $this->assertEquals('<input type="hidden" name="blog_plugin[device_type]" value="sp"/>', $html);
    }

    public function testSelect(): void
    {
        $req = new Request("POST", "/", null, ['test' => ['name' => "B"]]);
        $html = Html::input($req, 'test[name]', 'select', ['options' => ["A" => 'a', "B" => 'b', "C" => 'c']]);
        $this->assertEquals(
            '<select name="test[name]"><option value="A" >a</option><option value="B" selected="selected">b</option><option value="C" >c</option></select>',
            $html
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
