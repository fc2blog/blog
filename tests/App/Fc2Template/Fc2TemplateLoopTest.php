<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Fc2Template;

use ErrorException;
use Fc2blog\Config;
use Fc2blog\Model\BlogTemplatesModel;
use ParseError;
use PHPUnit\Framework\TestCase;
use TypeError;

class Fc2TemplateLoopTest extends TestCase
{
  // TODO 全 fc2_template_ifをチェックできているか担保する仕組み

  public function setUp(): void
  {
    Config::read('fc2_template.php');
    parent::setUp();
  }

  public function test_topentry()
  {
    $this->loopStateTester("topentry", "okokok", ['titlelist_area' => false, 'entries' => [1, 2, 3]]);
    $this->loopStateTester("topentry", "", ['titlelist_area' => true, 'entries' => [1, 2, 3]]);
  }

  public function test_titlelist()
  {
    $this->loopStateTester("titlelist", "", ['titlelist_area' => false, 'entries' => [1, 2, 3]]);
    $this->loopStateTester("titlelist", "okokok", ['titlelist_area' => true, 'entries' => [1, 2, 3]]);
  }

  public function test_comment()
  {
    $this->loopStateTester("comment", "okokok", ['comments' => [1, 2, 3]]);
    $this->loopStateTester("comment_list", "okokok", ['comments' => [1, 2, 3]]);
    $this->loopStateTester("comment", "", ['comments' => []]);
    $this->loopStateTester("comment_list", "", ['comments' => []]);
  }

  public function test_category_list()
  {
    $this->loopStateTester("category_list", "okokok", ['entry' => ['categories' => [1, 2, 3]]]);
    $this->loopStateTester("category_list", "", ['entry' => ['categories' => []]]);
  }

  public function loopStateTester($tag, $expected, $env)
  {
    $input_template = "<!--{$tag}-->ok<!--/{$tag}-->";
    $php_template = $this->convertFc2TemplateToPhpTemplate($input_template);
    $res = $this->evalPhpTemplate($php_template, $env);
//    var_dump($res);
    $this->assertEquals($expected, $res);
  }

  public function convertFc2TemplateToPhpTemplate(string $input_template): string
  {
    $b = new BlogTemplatesModel();
    return $b->convertFC2Template($input_template);
  }

  /**
   * PHPのフラグメントをPHPとして評価してみる
   * @param string $php_template
   * @param array $env
   * @return string
   */
  public function evalPhpTemplate(string $php_template, array $env): string
  {
    extract($env);

    $rtn = null;
    try {
      ob_start();
      eval("?>" . $php_template);
      $rtn = ob_get_contents();
      ob_end_clean();
      $this->assertIsString($rtn);
    } /** @noinspection PhpRedundantCatchClauseInspection eval時に発生する可能性がある */ catch (ErrorException $e) {
      $this->fail("exec error `{$php_template}` got {$e->getMessage()}");
    } catch (TypeError $e) {
      $this->fail("type error `{$php_template}` got {$e->getMessage()}");
    } catch (ParseError $e) {
      $this->fail("parse error `{$php_template}` got {$e->getMessage()}");
    }

    return $rtn;
  }

}
