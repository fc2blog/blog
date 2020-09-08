<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Fc2Template;

use ErrorException;
use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Model\CommentsModel;
use Fc2blog\Model\EntriesModel;
use Fc2blog\Model\TagsModel;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use ParseError;
use PHPUnit\Framework\TestCase;
use TypeError;

class Fc2TemplateTest extends TestCase
{

  /**
   * テンプレートタグを実際にPHPとして評価してみる
   * （ただし、それぞれのタグ表示には各種前提条件があり、実行時エラーを特定できるものではない。せいぜいLinter程度の効果）
   * TODO blank rtnがなくなる程度にデータを拡充する
   */
  public function testAllPrintableTagEval(): void
  {
    Config::read('fc2_template.php');
    $printable_tags = Config::get('fc2_template_var_search');

    $b = new BlogTemplatesModel();

    extract($this->generateSampleData());

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
        if (true) {
          if (strlen($converted_php) === 0) {
            // echo "[blank php] {$printable_tag}: {$converted_php} ==> {$rtn}" . PHP_EOL;
          } elseif (strlen($rtn) === 0) {
            echo "[blank rtn] {$printable_tag}: {$converted_php} ==> {$rtn}" . PHP_EOL;
          } else {
            // echo "[ok] {$printable_tag}: {$converted_php} ==> {$rtn}".PHP_EOL;
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

  /**
   * fc2 templateのif系タグを実行テスト
   * TODO blank rtnがなくなる程度にデータを拡充する
   */
  public function testAllIfCondEval(): void
  {
    Config::read('fc2_template.php');
    $fc2_template_if_list = Config::get('fc2_template_if');

    $b = new BlogTemplatesModel();

    extract($this->generateSampleData());

    foreach ($fc2_template_if_list as $tag_str => $php_code) {
      $input_html = "<!--{$tag_str}-->BODY<!--/{$tag_str}-->";

      $converted_php = $b->convertFC2Template($input_html);

      try {
        ob_start();
        // 評価してみる
        eval("?>" . $converted_php);
        $rtn = ob_get_contents();
        ob_end_clean();
        // 文字列がとれれば、基本的に実行はできているはず
        $this->assertIsString($rtn);
        if (true) {
          if (strlen($converted_php) === 0) {
            // echo "[blank php] {$tag_str}: {$converted_php} ==> {$rtn}" . PHP_EOL;
          } elseif (strlen($rtn) === 0) {
            echo "[blank rtn] {$tag_str}: {$converted_php} ==> {$rtn}" . PHP_EOL;
          } else {
            // echo "[ok] {$tag_str}: {$converted_php} ==> {$rtn}" . PHP_EOL;
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

  /**
   * fc2 templateのforeach系タグを実行テスト
   * TODO blank rtnがなくなる程度にデータを拡充する
   */
  public function testAllForEachCondEval(): void
  {
    Config::read('fc2_template.php');
    $fc2_template_if_list = Config::get('fc2_template_foreach');

    $b = new BlogTemplatesModel();

    extract($this->generateSampleData());

    foreach ($fc2_template_if_list as $tag_str => $php_code) {
      $input_html = "<!--{$tag_str}-->BODY<!--/{$tag_str}-->";

      $converted_php = $b->convertFC2Template($input_html);

      try {
        ob_start();
        // 評価してみる
        eval("?>" . $converted_php);
        $rtn = ob_get_contents();
        ob_end_clean();
        // 文字列がとれれば、基本的に実行はできているはず
        $this->assertIsString($rtn);
        if (true) {
          if (strlen($converted_php) === 0) {
            // echo "[blank php] {$tag_str}: {$converted_php} ==> {$rtn}" . PHP_EOL;
          } elseif (strlen($rtn) === 0) {
            echo "[blank rtn] {$tag_str}: {$converted_php} ==> {$rtn}" . PHP_EOL;
          } else {
            // echo "[ok] {$tag_str}: {$converted_php} ==> {$rtn}" . PHP_EOL;
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

  /**
   * タグ等のEvalのために以下の疑似データを生成
   * @return array
   */
  public function generateSampleData(): array
  {
    // TODO Paging

    $blog_id = "testblog2";
    $url = '/testblog2/';
    $now_date = date('Y-m-d');
    $now_month_date = date('Y-m-1', strtotime($now_date));
    $prev_month_date = date('Y-m-1', strtotime($now_month_date . ' -1 month'));
    $next_month_date = date('Y-m-1', strtotime($now_month_date . ' +1 month'));

    $generator = new GenerateSampleComment();
    $generator->removeAllComments('testblog2', 1);
    $generator->generateSampleComment('testblog2', 1, 2);

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

    // TODO エントリにカテゴリ
    // TODO エントリにタグ？
    $entry_model = new EntriesModel();
    $entry = $entry_model->findByIdAndBlogId(1, 'testblog2');
    { // FC2のテンプレート用にデータを置き換える borrow from layouts/fc2_template.php
      // topentry系変数のデータ設定
      $entry['title_w_img'] = $entry['title'];
      $entry['title'] = strip_tags($entry['title']);
      $entry['link'] = App::userURL($request, ['controller' => 'Entries', 'action' => 'view', 'blog_id' => $entry['blog_id'], 'id' => $entry['id']]);
      [
        $entry['year'],
        $entry['month'],
        $entry['day'],
        $entry['hour'],
        $entry['minute'],
        $entry['second'],
        $entry['youbi'],
        $entry['month_short']
      ] = explode('/', date('Y/m/d/H/i/s/D/M', strtotime($entry['posted_at'])));
      $entry['wayoubi'] = __($entry['youbi']);

      // 自動改行処理
      if ($entry['auto_linefeed'] == Config::get('ENTRY.AUTO_LINEFEED.USE')) {
        $entry['body'] = nl2br($entry['body']);
        $entry['extend'] = nl2br($entry['extend']);
      }
    }
//    echo "entry =>";
//    var_export($entry);
//    echo PHP_EOL;
    $self_blog = false; // ログインしていない前提ということで

    $tag_model = new TagsModel();
    $tag = $tag_model->findByIdAndBlogId(1, 'testblog2');
//      var_dump($tag);

    $comment_model = new CommentsModel();
    $comment = $comment_model->findByIdAndBlogId(1, 'testblog2');
//      var_dump($comment);
    {
      $comment['edit_link'] = Html::url($request, ['controller' => 'Entries', 'action' => 'comment_edit', 'blog_id' => $comment['blog_id'], 'id' => $comment['id']]);

      [
        $comment['year'],
        $comment['month'],
        $comment['day'],
        $comment['hour'],
        $comment['minute'],
        $comment['second'],
        $comment['youbi']
      ] = explode('/', date('Y/m/d/H/i/s/D', strtotime($comment['updated_at'])));
      $comment['wayoubi'] = __($comment['youbi']);

      if (isset($comment['reply_updated_at'])) {
        [
          $comment['reply_year'],
          $comment['reply_month'],
          $comment['reply_day'],
          $comment['reply_hour'],
          $comment['reply_minute'],
          $comment['reply_second'],
          $comment['reply_youbi']
        ] = explode('/', date('Y/m/d/H/i/s/D', strtotime($comment['reply_updated_at'])));
        $comment['reply_wayoubi'] = __($comment['reply_youbi']);
        $comment['reply_body'] = nl2br($comment['reply_body']);
      }
    }

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

    return compact(array_keys(get_defined_vars()));
  }

}
