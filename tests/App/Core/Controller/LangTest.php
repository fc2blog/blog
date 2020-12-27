<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Core\Controller;

use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class LangTest extends TestCase
{
  public function testLanguage(): void
  {
    $request = new Request("GET","/",[],[],[],[],[] );
    $this->assertEquals('en',$request->lang);

    $request = new Request("GET","/?lang=en",[],[],[],[],[] );
    $this->assertEquals('en',$request->lang);

    $request = new Request("GET","/",[],['lang'=>'en'],[],[],[] );
    $this->assertEquals('en',$request->lang);

    $request = new Request("GET","/",[],[],[],[],['HTTP_ACCEPT_LANGUAGE'=>'en'] );
    $this->assertEquals('en',$request->lang);

    $request = new Request("GET","/",[],[],[],[],[],[],['lang'=>'en'] );
    $this->assertEquals('en',$request->lang);
  }

  public function testJaLanguage(): void
  {
    $request = new Request("GET","/?lang=ja",[],[],[],[],[],[],[] );
    $this->assertEquals('ja',$request->lang);

    $request = new Request("GET","/",[],['lang'=>'ja'],[],[],[] );
    $this->assertEquals('ja',$request->lang);

    $request = new Request("GET","/",[],[],[],[],['HTTP_ACCEPT_LANGUAGE'=>'ja'] );
    $this->assertEquals('ja',$request->lang);

    $request = new Request("GET","/",[],[],[],[],[],[],['lang'=>'ja'] );
    $this->assertEquals('ja',$request->lang);
  }

  public function testInvalidLanguage(): void
  {
    $request = new Request("GET","/?lang=invalid",[],[],[],[],[],[],[] );
    $this->assertEquals('en',$request->lang);

    $request = new Request("GET","/",[],['lang'=>'invalid'],[],[],[] );
    $this->assertEquals('en',$request->lang);

    $request = new Request("GET","/",[],[],[],[],['HTTP_ACCEPT_LANGUAGE'=>'invalid'] );
    $this->assertEquals('en',$request->lang);

    $request = new Request("GET","/",[],[],[],[],[],[],['lang'=>'invalid'] );
    $this->assertEquals('en',$request->lang);
  }
}
