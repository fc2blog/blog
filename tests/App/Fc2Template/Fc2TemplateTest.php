<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Fc2Template;

use ErrorException;
use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Exception\RedirectExit;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Model\EntryCategoriesModel;
use Fc2blog\Model\TagsModel;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleCategory;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleComment;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleEntry;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleTag;
use Fc2blog\Web\Controller\User\EntriesController;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use ParseError;
use PHPUnit\Framework\TestCase;
use TypeError;

class Fc2TemplateTest extends TestCase
{
  public function setUp(): void
  {
    Config::read('fc2_template.php');
    parent::setUp();
  }

  public $coverage_blank_php = [];
  public $coverage_blank_return = [];
  public $coverage_ok = [];

  public function __destruct()
  {
    // TODO 逆カバレッジのようなものを取得し、「どのケースでも」出力されていないタグを出力する。
    var_dump($this->coverage_blank_return);
//    var_dump($this->coverage_blank_php);
//    var_dump($this->coverage_ok);
  }

  public function generateTestData($blog_id): array
  {
    ## テストデータ生成

    # category生成
    $category_generator = new GenerateSampleCategory();
    $categories = $category_generator->generateSampleCategories($blog_id, 0, 1);
    $category = $categories[0];

    # entry生成
    $entry_generator = new GenerateSampleEntry();
    $entries = $entry_generator->generateSampleEntry($blog_id, 1);
    $entry = $entries[0];
    // カテゴリを追加する
    $category_generator->updateEntryCategories($blog_id, $entry['id'], [$category['id']]);

    # tag生成
    $tag_generator = new GenerateSampleTag();
    $tag_generator->generateSampleTagsToSpecifyEntry($blog_id, $entry['id'], 1);

    # comment生成
    $comment_generator = new GenerateSampleComment();
    $comment_generator->removeAllComments($blog_id, $entry['id']);
    $comment_generator->generateSampleComment($blog_id, $entry['id'], 10);

    # entry構造体を返す
    return $entry;
  }

  /**
   * ブログトップページ（EntriesController::index）にて全タグを擬似実行
   * NOTE: compact(array_keys(get_defined_vars())) にて、スコープ内変数が（わかりづらく）外部にて利用されているので、削除時には注意すること
   */
  public function testTagsInEntriesIndex(): void
  {
    $blog_id = "testblog2";
    $this->generateTestData($blog_id);

    ## 「状態」生成
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
      []
    );
    $entry_controller = new EntriesController($request);
    $entry_controller->prepare('index');

    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());
  }

  /**
   * エントリページ（EntriesController::view）の疑似データを生成
   */
  public function testTagsInEntriesView(): void
  {
    $blog_id = "testblog2";
    $entry = $this->generateTestData($blog_id);

    ## 「状態」生成
    // request 生成
    $request = new Request(
      "GET",
      "/{$blog_id}/no={$entry['id']}",
      [],
      [],
      ['no' => 1],
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
    $entry_controller = new EntriesController($request);
    $entry_controller->prepare('view');

    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());
  }

  /**
   * カテゴリページ（EntriesController::category）の疑似データを生成
   */
  public function testTagsInEntriesCategory(): void
  {
    $blog_id = "testblog2";
    $entry = $this->generateTestData($blog_id);
    $entry_category_model = new EntryCategoriesModel();
    $entry_categories = $entry_category_model->getCategoryIds($blog_id, $entry['id']);
    $entry_category = $entry_category_model->findByCategoryIdAndBlogId($entry_categories[0], $blog_id);

    ## 「状態」生成
    // request 生成
    $request = new Request(
      "GET",
      "/{$blog_id}/cat={$entry_category['id']}",
      [],
      [],
      ['cat' => $entry_category['id']],
      [],
      [],
      [],
      []
    );
    $entry_controller = new EntriesController($request);
    $entry_controller->prepare('category');

    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());
  }

  /**
   * タグ検索ページ（EntriesController::tag）の疑似データを生成
   */
  public function testTagsInEntriesTag(): void
  {
    $blog_id = "testblog2";
    $entry = $this->generateTestData($blog_id);

    $tag_model = new TagsModel();
    $tags = $tag_model->getEntryTags($blog_id, $entry['id']);
//    var_dump($tags);
    $tag_str = $tags[0]['name'];


    ## 「状態」生成
    // request 生成
    $request = new Request(
      "GET",
      "/{$blog_id}/tag={$tag_str}",
      [],
      [],
      ['tag' => $tag_str],
      [],
      [],
      [],
      []
    );
    $entry_controller = new EntriesController($request);
    $entry_controller->prepare('tag');

    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());
  }

  /**
   * アーカイブページ（EntriesController::archive）の疑似データを生成
   */
  public function testTagsInEntriesArchive(): void
  {
    $blog_id = "testblog2";
    $this->generateTestData($blog_id);

    ## 「状態」生成
    // request 生成
    $request = new Request(
      "GET",
      "/{$blog_id}/archives.html",
      [],
      [],
      [],
      [],
      [],
      [],
      []
    );
    $entry_controller = new EntriesController($request);
    $entry_controller->prepare('archives');

    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());
  }

  /**
   * ブログパスワードページ（EntriesController::blog_password）の疑似データを生成
   */
  public function testTagsInEntriesBlogPassword(): void
  {
    $blog_id = "testblog2";
    $this->generateTestData($blog_id);

    // TODO 一時的にBlogをパスワード付きに
    $blogs_model = new BlogsModel();
    $blog = $blogs_model->findById($blog_id);
//    var_dump($blog);
    $blog['open_status'] = Config::get('BLOG.OPEN_STATUS.PRIVATE');
    $blog['blog_password'] = "password";
    $blogs_model->updateById($blog, $blog['id']);

    ## 「状態」生成
    // request 生成
    // 認証前
    try {
      $request = new Request(
        "GET",
        "/{$blog_id}/",
        [],
        [],
        [],
        [],
        [],
        [],
        []
      );
      $entry_controller = new EntriesController($request);
      $entry_controller->prepare('blog_password');
      $this->fail('password protect not worked');
    } catch (RedirectExit $e) {
      $this->assertEquals("/testblog2/index.php?mode=entries&process=blog_password", $e->redirectUrl);
    }

    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());

    // 認証のトライ
    $request = new Request(
      "GET",
      "/{$blog_id}/index.php?mode=entries&process=blog_password",
      [],
      [],
      [],
      [],
      [],
      [],
      []
    );
    $entry_controller = new EntriesController($request);
    $entry_controller->prepare('blog_password');
    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());

    // 認証のトライ
    try {
      $request = new Request(
        "POST",
        "/{$blog_id}/index.php?mode=entries&process=blog_password",
        [],
        [
          'blog' => [
            'password' => 'password'
          ]
        ],
        [],
        [],
        [],
        [],
        []
      );
      $entry_controller = new EntriesController($request);
      $entry_controller->prepare('blog_password');
      $this->fail('password auth not worked');
    } catch (RedirectExit $e) {
      $this->assertEquals("/testblog2/index.php?mode=entries&process=index", $e->redirectUrl);
      $this->assertTrue($entry_controller->get('auth_success'));
    }

    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());

    // 認証OK
    $request = new Request(
      "POST",
      "/{$blog_id}/",
      [
        "blog_password.{$blog_id}" => true
      ],
      [],
      [],
      [],
      [],
      [],
      []
    );
    $entry_controller = new EntriesController($request);
    $entry_controller->prepare('index');

    ## 疑似実行
    $this->evalAll($request, $entry_controller->getData());
  }

  // == support

  /**
   * 各タグのテストを実施
   * @param Request $request
   * @param array $data
   */
  public function evalAll(Request $request, array $data): void
  {
    $this->getAllPrintableTagEval($request, $data);
    $this->getAllIfCondEval($request, $data);
    $this->getAllForEachCondEval($request, $data);
  }

  /**
   * テンプレートタグを変換し、実行テスト
   * （ただし、それぞれのタグ表示には各種前提条件があり、実行時エラーを特定できるものではない。せいぜいLinter程度の効果）
   * @param Request $request
   * @param array $env
   */
  public function getAllPrintableTagEval(Request $request, array $env): void
  {
    extract($env);
    extract($this->fc2templateLayoutEmulator($request, $env));

    $printable_tags = Config::get('fc2_template_var_search');
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
   * fc2 templateのif系タグを変換し、実行テスト
   * @param Request $request
   * @param array $env
   */
  public function getAllIfCondEval(Request $request, array $env): void
  {
    extract($env);

    $fc2_template_if_list = Config::get('fc2_template_if');
    $b = new BlogTemplatesModel();
    foreach ($fc2_template_if_list as $tag_str => $php_code) {
      $input_html = "<!--{$tag_str}-->BODY<!--/{$tag_str}-->";
      $converted_php = $b->convertFC2Template($input_html);
      $this->fragmentRunner(compact(array_keys(get_defined_vars())), $tag_str, $converted_php);
    }
  }

  /**
   * fc2 templateのforeach系タグを変換し、実行テスト
   * @param Request $request
   * @param array $env
   */
  public function getAllForEachCondEval(Request $request, array $env): void
  {
    extract($env);
    extract($this->fc2templateLayoutEmulator($request, $env));

    $fc2_template_if_list = Config::get('fc2_template_foreach');
    $b = new BlogTemplatesModel();
    foreach ($fc2_template_if_list as $tag_str => $php_code) {
      $input_html = "<!--{$tag_str}-->BODY<!--/{$tag_str}-->";
      $converted_php = $b->convertFC2Template($input_html);
      $this->fragmentRunner(compact(array_keys(get_defined_vars())), $tag_str, $converted_php);
    }
  }

  /**
   * PHPのフラグメントをPHPとして評価してみる
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

      // 一度でもOKを通過していればOKとする。
      if (strlen($converted_php) === 0) {
        if (!isset($this->coverage_ok[$tag_str])) $this->coverage_blank_php[$tag_str] = "";
      } elseif (strlen($rtn) === 0) {
        if (!isset($this->coverage_ok[$tag_str])) $this->coverage_blank_return[$tag_str] = $converted_php;
      } else {
        unset($this->coverage_blank_return[$tag_str]);
        unset($this->coverage_blank_php[$tag_str]);
        $this->coverage_ok[$tag_str] = "{$converted_php} ==> {$rtn}";
      }
    } /** @noinspection PhpRedundantCatchClauseInspection eval時に発生する可能性がある */ catch (ErrorException $e) {
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
   * @param Request $request
   * @param $array
   * @return array
   */
  public function fc2templateLayoutEmulator(Request $request, $array): array
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
    $now_date = isset($date_area) && $date_area ? $now_date : date('Y-m-d');
    $now_month_date = date('Y-m-1', strtotime($now_date));
    $prev_month_date = date('Y-m-1', strtotime($now_month_date . ' -1 month'));
    $next_month_date = date('Y-m-1', strtotime($now_month_date . ' +1 month'));

    return compact(array_keys(get_defined_vars()));
  }

}
