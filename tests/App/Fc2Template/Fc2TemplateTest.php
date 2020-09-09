<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Fc2Template;

use ErrorException;
use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
use Fc2blog\Web\Controller\User\EntriesController;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use ParseError;
use PHPUnit\Framework\TestCase;
use TypeError;

class Fc2TemplateTest extends TestCase
{
  // TODO testとgeneratorの親子関係が逆であるべき

  public function setUp(): void
  {
    Config::read('fc2_template.php');
    parent::setUp();
  }

  /**
   * テンプレートタグを実際にPHPとして評価してみる
   * （ただし、それぞれのタグ表示には各種前提条件があり、実行時エラーを特定できるものではない。せいぜいLinter程度の効果）
   * TODO blank rtnがなくなる程度にデータを拡充する
   */
  public function testAllPrintableTagEval(): void
  {
    $printable_tags = Config::get('fc2_template_var_search');

    extract($this->generateBlogTopSampleData());

    $b = new BlogTemplatesModel();
    foreach ($printable_tags as $tag_str => $printable_tag) {
      // タグの含まれたHTML
      $input_html = "{$printable_tag}";
      // 変換されたPHP
      $converted_php = $b->convertFC2Template($input_html);
      $this->fragmentRunner(compact(array_keys(get_defined_vars())), $tag_str, $converted_php);
    }
  }

  /**
   * fc2 templateのif系タグを実行テスト
   * TODO blank rtnがなくなる程度にデータを拡充する
   */
  public function testAllIfCondEval(): void
  {
    $fc2_template_if_list = Config::get('fc2_template_if');

    extract($this->generateBlogTopSampleData());

    $b = new BlogTemplatesModel();
    foreach ($fc2_template_if_list as $tag_str => $php_code) {
      $input_html = "<!--{$tag_str}-->BODY<!--/{$tag_str}-->";
      $converted_php = $b->convertFC2Template($input_html);
      $this->fragmentRunner(compact(array_keys(get_defined_vars())), $tag_str, $converted_php);
    }
  }

  /**
   * fc2 templateのforeach系タグを実行テスト
   * TODO blank rtnがなくなる程度にデータを拡充する
   */
  public function testAllForEachCondEval(): void
  {
    $fc2_template_if_list = Config::get('fc2_template_foreach');

    extract($this->generateBlogTopSampleData());

    $b = new BlogTemplatesModel();
    foreach ($fc2_template_if_list as $tag_str => $php_code) {
      $input_html = "<!--{$tag_str}-->BODY<!--/{$tag_str}-->";
      $converted_php = $b->convertFC2Template($input_html);
      $this->fragmentRunner(compact(array_keys(get_defined_vars())), $tag_str, $converted_php);
    }
  }

  /**
   * ブログトップページ（EntriesController::index）の疑似データを生成
   * @return array
   */
  public function generateBlogTopSampleData(): array
  {
    $blog_id = "testblog2";

    // entryもここで生成すべき
    $generator = new GenerateSampleComment();
    $generator->removeAllComments($blog_id, 1);
    $generator->generateSampleComment($blog_id, 1, 2);

    // request 生成
    $request = new Request(
      "GET",
      "/{$blog_id}/",
      [],
      [],
      [],
      [],
      [],
      [],
      [
        'comment_name' => 'comment name',
        'comment_mail' => 'comment mail',
        'comment_url' => 'comment_url',
        // self_blog系のためにログイン情報？
      ]
    );

    Config::set('ControllerName', 'Entries'); // TODO 後続のテストを汚染してしまう可能性がある

    // Top Page用条件生成
    $options = [
      'where' => 'blog_id=?',
      'params' => [$blog_id],
    ];
    $options = EntriesController::getEntriesQueryOptions($blog_id, $options, 5);
    $entries = EntriesController::getEntriesArray($blog_id, $options);
    $paging = EntriesController::getPaging($options);
    extract($this->setAreaData([]));

    extract($this->fc2templateLayoutEmulator(compact(array_keys(get_defined_vars()))));

    return compact(array_keys(get_defined_vars()));
  }

  /**
   * エントリページ（EntriesController::view）の疑似データを生成
   * @return array
   */
  public function generatePermanentEntryPageSampleData(): array
  {
  }

  /**
   * PHPのフラグメントを評価
   * @param array $env
   * @param $tag_str
   * @param string $converted_php
   * @return string
   */
  public function fragmentRunner(array $env, $tag_str, string $converted_php): string
  {
    extract($env);

    $rtn = null;
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

    return $rtn;
  }

  /**
   * EntriesController::setAreaDataとの互換性ツール
   * @param array $allows
   * @return array
   */
  public function setAreaData(array $allows): array
  {
    $areas = [
      'index_area',     // トップページ
      'titlelist_area', // インデックス
      'date_area',      // 日付別
      'category_area',  // カテゴリ別
      'tag_area',       // タグエリア
      'search_area',    // 検索結果一覧
      'comment_area',   // コメントエリア
      'form_area',      // 携帯、スマフォのコメントエリア
      'edit_area',      // コメント編集エリア
      'permanent_area', // 固定ページ別
      'spplugin_area',  // スマフォのプラグインエリア
    ];

    $return_array = [];
    foreach ($areas as $area) {
      $return_array[$area] = in_array($area, $allows);
    }
    return $return_array;
  }

  /**
   * fc2_template.php のlayoutにて各種の変換ロジックが入っており、それを再現するもの
   * @param $array
   * @return array
   */
  public function fc2templateLayoutEmulator($array): array
  {
    extract($array);

    $blogs_model = new BlogsModel();
    /** @noinspection PhpUndefinedVariableInspection */
    $blog = $blogs_model->findById($blog_id);

// FC2のテンプレート用にデータを置き換える

    if (!empty($entry)) {
      $entries = array($entry);
    }
    if (!empty($entries)) {
      foreach ($entries as $key => $value) {
        // topentry系変数のデータ設定
        $entries[$key]['title_w_img'] = $value['title'];
        $entries[$key]['title'] = strip_tags($value['title']);
        /** @noinspection PhpUndefinedVariableInspection */
        $entries[$key]['link'] = App::userURL($request, array('controller' => 'Entries', 'action' => 'view', 'blog_id' => $value['blog_id'], 'id' => $value['id']));

        list($entries[$key]['year'], $entries[$key]['month'], $entries[$key]['day'],
          $entries[$key]['hour'], $entries[$key]['minute'], $entries[$key]['second'], $entries[$key]['youbi'], $entries[$key]['month_short']
          ) = explode('/', date('Y/m/d/H/i/s/D/M', strtotime($value['posted_at'])));
        $entries[$key]['wayoubi'] = __($entries[$key]['youbi']);

        // 自動改行処理
        if ($value['auto_linefeed'] == Config::get('ENTRY.AUTO_LINEFEED.USE')) {
          $entries[$key]['body'] = nl2br($value['body']);
          $entries[$key]['extend'] = nl2br($value['extend']);
        }
      }
    }

// コメント一覧の情報
    if (!empty($comments)) {
      foreach ($comments as $key => $value) {
        $comments[$key]['edit_link'] = Html::url($request, array('controller' => 'Entries', 'action' => 'comment_edit', 'blog_id' => $value['blog_id'], 'id' => $value['id']));

        list($comments[$key]['year'], $comments[$key]['month'], $comments[$key]['day'],
          $comments[$key]['hour'], $comments[$key]['minute'], $comments[$key]['second'], $comments[$key]['youbi']
          ) = explode('/', date('Y/m/d/H/i/s/D', strtotime($value['updated_at'])));
        $comments[$key]['wayoubi'] = __($comments[$key]['youbi']);
        $comments[$key]['body'] = $value['body'];

        list($comments[$key]['reply_year'], $comments[$key]['reply_month'], $comments[$key]['reply_day'],
          $comments[$key]['reply_hour'], $comments[$key]['reply_minute'], $comments[$key]['reply_second'], $comments[$key]['reply_youbi']
          ) = explode('/', date('Y/m/d/H/i/s/D', strtotime($value['reply_updated_at'])));
        $comments[$key]['reply_wayoubi'] = __($comments[$key]['reply_youbi']);
        $comments[$key]['reply_body'] = nl2br($value['reply_body']);
      }
    }

// FC2用のどこでも有効な単変数
    $url = '/' . $blog['id'] . '/';
//    $blog_id = $this->getBlogId($request); // 外部で定義しているので、不要

// 年月日系
    // get from app/src/Web/Controller/User/EntriesController.php::date() 経由だと定義される
    /** @noinspection PhpUndefinedVariableInspection */
    $now_date = $date_area ? $now_date : date('Y-m-d');
    $now_month_date = date('Y-m-1', strtotime($now_date));
    $prev_month_date = date('Y-m-1', strtotime($now_month_date . ' -1 month'));
    $next_month_date = date('Y-m-1', strtotime($now_month_date . ' +1 month'));

    return compact(array_keys(get_defined_vars()));
  }

}
