<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Fc2Template;

use ErrorException;
use Fc2blog\Config;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Model\CommentsModel;
use Fc2blog\Model\EntriesModel;
use Fc2blog\Model\TagsModel;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
use Fc2blog\Web\Request;
use ParseError;
use PHPUnit\Framework\TestCase;
use TypeError;

class Fc2TemplateTest extends TestCase
{

  /**
   * テンプレートタグを実際にPHPとして評価してみる
   * （ただし、それぞれのタグには大量の前提条件があり、実行時エラーを特定できるものではない、せいぜいLinter程度の効果）
   */
  public function testAllPrintableTagEval():void
  {
    Config::read('fc2_template.php');
    $printable_tags = Config::get('fc2_template_var_search');

    $generator = new GenerateSampleComment();
    $generator->removeAllComments('testblog2', 1);
    $generator->generateSampleComment('testblog2', 1, 2);

    $b = new BlogTemplatesModel();

    { // タグのEvalのために以下のダミーが必要
      $blog_id = "testblog2";
      $now_date = time();
      $prev_month_date = strtotime('last month');
      $next_month_date = strtotime('next month');
      $request = new Request(
        "GET",
        "/testblog2",
        [],
        [],
        [],
        [],
        [],
        [],
        [
          'comment_name' => 'comment name',
          'comment_mail' => 'comment mail',
          'comment_url' => 'comment_url'
        ]

      );
      Config::set('ControllerName', 'Common'); // TODO 後続のテストを汚染してしまう可能性がある

      $entry_model = new EntriesModel();
      $entry = $entry_model->findByIdAndBlogId(1, 'testblog2');
      echo "entry =>";
      var_export($entry);
      echo PHP_EOL;
      $self_blog = false; // ログインしていない前提ということで

      $tag_model = new TagsModel();
      $tag = $tag_model->findByIdAndBlogId(1, 'testblog2');
//      var_dump($tag);

      $comment_model = new CommentsModel();
      $comment = $comment_model->findByIdAndBlogId(1, 'testblog2');
//      var_dump($comment);

      $comment_error = 'something what?';

      $edit_comment = [];
      $edit_comment['id'] = "something";
      $edit_comment['name'] = "something";
      $edit_comment['title'] = "something";
      $edit_comment['mail'] = "something";
      $edit_comment['url'] = "something";
      $edit_comment['body'] = "something";
      $edit_comment['message'] = "something";

      $edit_entry = [];
      $edit_entry['id'] = "something";
      $edit_entry['title'] = "something";
    }

    foreach ($printable_tags as $tag_name => $printable_tag) {

      // タグの含まれたHTML
      $input_html = "{$printable_tag}";

      // 変換されたPHP
      $converted_php = $b->convertFC2Template($input_html);

      try {
        ob_start();
        // 評価してみる
        eval("?>" . $converted_php);
        $rtn = ob_get_contents();
        ob_end_clean();
        // 文字列がとれれば、基本的に実行はできているはず
        $this->assertIsString($rtn);
        if(true){
          if(strlen($converted_php)===0){
            echo "[blank php] {$printable_tag}: {$converted_php} ==> {$rtn}".PHP_EOL;
          }elseif(strlen($rtn)===0){
            echo "[blank rtn] {$printable_tag}: {$converted_php} ==> {$rtn}".PHP_EOL;
          }else{
            echo "[ok] {$printable_tag}: {$converted_php} ==> {$rtn}".PHP_EOL;
          }
        }
      } catch (ErrorException $e) {
        $this->fail("exec error `{$converted_php}` got {$e->getMessage()}");
      } catch (TypeError $e) {
        $this->fail("type error `{$converted_php}` got {$e->getMessage()}");
      } catch (ParseError $e) {
        $this->fail("parse error `{$converted_php}` got {$e->getMessage()}");
      }
    }
  }
}
