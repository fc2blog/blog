<?php
/**
 * Controllerの親クラス
 */

namespace Fc2blog\Web\Controller;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Exception\RedirectExit;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Util\Log;
use Fc2blog\Util\StringCaseConverter;
use Fc2blog\Util\Twig\GetTextHelper;
use Fc2blog\Util\Twig\HtmlHelper;
use Fc2blog\Web\Controller\User\UserController;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;

abstract class Controller
{
  protected $data = [];            // テンプレートへ渡す変数の保存領域
  protected $layout = 'default.php';  // 表示ページのレイアウトテンプレート
  protected $output = '';             // 出力タグ
  private $templateFilePath = "";
  private $layoutFilePath = "";
  private $resolvedMethod;
  protected $request;

  public function __construct(Request $request)
  {
    $this->request = $request;
  }

  public function execute($method)
  {
    $template = $this->prepare($method);
    $this->render($template);
  }

  /**
   * render前のアクション実行処理群
   * @param string $method
   * @return string template file path
   */
  public function prepare(string $method): string
  {
    $this->beforeFilter($this->request);

    // アクションの実行(返り値はテンプレートファイルパスまたは空文字、レンダリング用データは$this->data)
    $this->resolvedMethod = $method;
    $template_path = $this->$method($this->request);

    // 空の場合は、規約に則ってテンプレートファイルを決定する
    if (empty($template_path)) {
      $template_path = strtolower($this->request->shortControllerName) . '/' . $method . '.php';
    }

    return $template_path;
  }

  /**
   * HTML(等)をレンダリングし、$this->outputに保存
   * @param string $template_path
   * @return void
   */
  public function render(string $template_path): void
  {
    // 出力を$this->outputで保持。後ほどemit()すること。
    // テンプレートファイル拡張子で、PHPテンプレートとTwigテンプレートを切り分ける
    if (preg_match("/\.twig\z/u", $template_path)) {
      $this->output = $this->renderByTwig($this->request, $template_path);
    } else {
      $this->output = $this->renderByPhpTemplate($this->request, $template_path);
      $this->output = $this->afterFilter($this->output);
    }
  }

  /**
   * ヘッダーおよび$this->outputの送信
   * TODO Output Bufferによりエラー表示を隠蔽する
   */
  public function emit(): void
  {
    if (isset($this->data['http_status_code']) && is_int($this->data['http_status_code'])) {
      http_response_code($this->data['http_status_code']);
    }

    if (!headers_sent()) {
      // Content typeの送信
      if (isset($this->data['http_content_type']) && strlen(isset($this->data['http_content_type'])) > 0) {
        header("Content-Type: {$this->data['http_content_type']}");
      } else {
        header("Content-Type: text/html; charset=UTF-8");
      }
    }

    echo $this->output;
  }

  public function getShortClassName()
  {
  }

  protected function beforeFilter(Request $request)
  {
  }

  protected function afterFilter(string $str): string
  {
    return $str;
  }

  public function set(string $key, $value)
  {
    $this->data[$key] = $value;
  }

  /**
   * リダイレクト
   * MEMO: Blog idが特定できないときの強制的なSchemaがさだまらない
   * TODO: この時点でリダイレクトせず、emit時にヘッダー送信する形にリファクタリングすべき
   * @param Request $request
   * @param $url
   * @param string $hash
   * @param bool $full_url BlogIdが特定できるとき、http(s)://〜からのフルURLを出力する、HTTP<>HTTPS強制リダイレクト時に必要
   * @param string|null $blog_id
   * @throws RedirectExit
   */
  protected function redirect(Request $request, $url, $hash = '', bool $full_url = false, string $blog_id = null)
  {
    if (is_array($url)) {
      $url = Html::url($request, $url, false, $full_url);

    } else if ($full_url && is_string($blog_id) && strlen($blog_id) > 0) {
      $url = BlogsModel::getFullHostUrlByBlogId($blog_id) . $url;

    } else if ($full_url && preg_match("|\A/([^/]+)/|u", $url, $match)) {
      // Blog idをURLから抜き出して利用
      $url = BlogsModel::getFullHostUrlByBlogId($match[1]) . $url;
      $blog_id = $match[1];
    }
    $url .= $hash;

    // デバッグ時にSessionにログを保存
    Log::debug_log(__FILE__ . ":" . __LINE__ . " " . 'Redirect[' . $url . ']');

    if (!is_null($blog_id) && $full_url) {
      $status_code = BlogsModel::getRedirectStatusCodeByBlogId($blog_id);
    } else {
      $status_code = 302;
    }
    // TODO Twig化が完了したら、Redirectをここで行わずに上位で行えるようにしたい（途中でのexitをなくしたい）
    if (!headers_sent()) {
      // full url指定時のリダイレクトは、Blogの設定がもつステータスコードを利用する
      header('Location: ' . $url, true, $status_code);
    }
    $escaped_url = h($url);
    if (defined("THIS_IS_TEST")) {
      $e = new RedirectExit(__FILE__ . ":" . __LINE__ . " redirect to {$escaped_url} status code:{$status_code} stack trace:" . print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true));
      $e->redirectUrl = $url;
      $e->statusCode = $status_code;
      throw $e;
    } else {
      exit;
    }
  }

  /**
   * 前のURLに戻す リファラーが取れなければ引数のURLに飛ばす
   * @param Request $request
   * @param $url
   * @param string $hash
   */
  protected function redirectBack(Request $request, $url, $hash = '')
  {
    // 元のURLに戻す
    if (!empty($_SERVER['HTTP_REFERER'])) {
      $this->redirect($request, $_SERVER['HTTP_REFERER']);
    }
    // リファラーが取れなければメインへ飛ばす
    $this->redirect($request, $url, $hash);
  }

  /**
   * TwigテンプレートエンジンでHTMLをレンダリング
   * 結果は $this->output に保管される
   * 現状、Admin用画面のみでの利用を想定
   * @param Request $request
   * @param string $twig_template
   * @return string
   */
  private function renderByTwig(Request $request, string $twig_template): string
  {
    $base_path = realpath(__DIR__ . "/../../../twig_templates/") . "/";
    $loader = new FilesystemLoader($base_path);
    $twig = new Environment($loader);

    foreach (
      array_merge(
        (new GetTextHelper())->getFunctions(),
        (new HtmlHelper())->getFunctions(),
      ) as $function) {
      $twig->addFunction($function);
    }

    $twig_template_path = $twig_template;
    $twig_template_device_path = preg_replace("/\.twig\z/u", '_' . App::getDeviceTypeStr($request) . '.twig', $twig_template_path);

    if (is_file($base_path . $twig_template_device_path)) { // デバイス用ファイルがある
      $twig_template_path = $twig_template_device_path;
    }

    if (!is_file($base_path . $twig_template_path)) {
      throw new InvalidArgumentException("Twig error: missing template: {$base_path}{$twig_template_path}");
    }

    $this->data['request'] = $request; // TODO Adhocに追加しているので、どこか適切な場所に移動する
    $blogs_model = new BlogsModel();

    // TODO 整理共通化リファクタリング, $thisが一意でない（管理画面用にデータを構築している）ので、User系のTwig化時に整理が必要
    $data = [ // 各種ベースとなるデータ
      'req' => $request,
      'sig' => Session::get('sig'),
      'lang' => $request->lang,
      'debug' => Config::get('APP_DEBUG') != 0,
      'preview_active_blog_url' => App::userURL($request, ['controller' => 'entries', 'action' => 'index', 'blog_id' => $this->getBlogId($request)]), // 代用できそう
      'is_register_able' => (Config::get('USER.REGIST_SETTING.FREE') == Config::get('USER.REGIST_STATUS')), // TODO 意図する解釈確認
      'active_menu' => App::getActiveMenu($request),
      'isLogin' => $this->isLogin(), // TODO admin 以外ではどうするか
      'nick_name' => $this->getNickName(), // TODO admin 以外ではどうするか
      'blog_list' => $blogs_model->getSelectList($this->getUserId()),  // TODO admin 以外ではどうするか
      'is_selected_blog' => $this->isSelectedBlog(), // TODO admin以外ではどうするか
      'flash_messages' => $this->removeMessage(), // TODO admin 以外ではどうするか
      'js_common' => [
        'isURLRewrite' => Config::get('URL_REWRITE'),
        'baseDirectory' => Config::get('BASE_DIRECTORY'),
        'deviceType' => $request->deviceType,
        'deviceArgs' => App::getArgsDevice($request)
      ],
      'cookie_common' => [
        'expire' => Config::get('COOKIE_EXPIRE'),
        'domain' => Config::get('COOKIE_DEFAULT_DOMAIN')
      ]
    ];
    // ログインしていないと確定しない変数
    if ($this->getBlog($this->getBlogId($request)) !== false && is_string($this->getBlogId($request))) {
      $data['blog'] = $this->getBlog($this->getBlogId($request));
      $data['blog']['url'] = $blogs_model::getFullHostUrlByBlogId($this->getBlogId($request), Config::get('DOMAIN_USER')) . "/" . $this->getBlogId($request) . "/";
    }
    $data = array_merge($data, $this->data);

    try {
      return $twig->render($twig_template_path, $data);
    } catch (LoaderError $e) {
      throw new RuntimeException("Twig error: {$e->getMessage()} {$e->getFile()}:{$e->getTemplateLine()}");
    } catch (RuntimeError $e) {
      throw new RuntimeException("Twig error: {$e->getMessage()} {$e->getFile()}:{$e->getTemplateLine()}");
    } catch (SyntaxError $e) {
      throw new RuntimeException("Twig error: {$e->getMessage()} {$e->getFile()}:{$e->getTemplateLine()}");
    }
  }

  /**
   * fc2blog形式のPHP Viewテンプレート内で利用する各種データを生成・変換
   * @param Request $request
   * @param array $data
   * @return array
   * TODO User系のみで使われるので、後日UserControllerへ移動
   */
  static public function preprocessingDataForFc2Template(Request $request, array $data):array
  {
    $data['request'] = $request;

    // FC2のテンプレート用にデータを置き換える
    if (!empty($data['entry'])) {
      $data['entries'] = [$data['entry']];
    }
    if (!empty($data['entries'])) {
      foreach ($data['entries'] as $key => $value) {
        // topentry系変数のデータ設定
        $data['entries'][$key]['title_w_img'] = $value['title'];
        $data['entries'][$key]['title'] = strip_tags($value['title']);
        $data['entries'][$key]['link'] = App::userURL($request, ['controller' => 'Entries', 'action' => 'view', 'blog_id' => $value['blog_id'], 'id' => $value['id']]);

        [
          $data['entries'][$key]['year'],
          $data['entries'][$key]['month'],
          $data['entries'][$key]['day'],
          $data['entries'][$key]['hour'],
          $data['entries'][$key]['minute'],
          $data['entries'][$key]['second'],
          $data['entries'][$key]['youbi'],
          $data['entries'][$key]['month_short']
        ] = explode('/', date('Y/m/d/H/i/s/D/M', strtotime($value['posted_at'])));
        $data['entries'][$key]['wayoubi'] = __($data['entries'][$key]['youbi']);

        // 自動改行処理
        if ($value['auto_linefeed'] == Config::get('ENTRY.AUTO_LINEFEED.USE')) {
          $data['entries'][$key]['body'] = nl2br($value['body']);
          $data['entries'][$key]['extend'] = nl2br($value['extend']);
        }
      }
    }

    // コメント一覧の情報
    if (!empty($data['comments'])) {
      foreach ($data['comments'] as $key => $value) {
        $data['comments'][$key]['edit_link'] = Html::url($request, ['controller' => 'Entries', 'action' => 'comment_edit', 'blog_id' => $value['blog_id'], 'id' => $value['id']]);

        [
          $data['comments'][$key]['year'],
          $data['comments'][$key]['month'],
          $data['comments'][$key]['day'],
          $data['comments'][$key]['hour'],
          $data['comments'][$key]['minute'],
          $data['comments'][$key]['second'],
          $data['comments'][$key]['youbi']
        ] = explode('/', date('Y/m/d/H/i/s/D', strtotime($value['updated_at'])));
        $data['comments'][$key]['wayoubi'] = __($data['comments'][$key]['youbi']);
        $data['comments'][$key]['body'] = $value['body']; // TODO nl2brされていないのは正しいのか？

        [
          $data['comments'][$key]['reply_year'],
          $data['comments'][$key]['reply_month'],
          $data['comments'][$key]['reply_day'],
          $data['comments'][$key]['reply_hour'],
          $data['comments'][$key]['reply_minute'],
          $data['comments'][$key]['reply_second'],
          $data['comments'][$key]['reply_youbi']
        ] = explode('/', date('Y/m/d/H/i/s/D', strtotime($value['reply_updated_at'])));
        $data['comments'][$key]['reply_wayoubi'] = __($data['comments'][$key]['reply_youbi']);
        $data['comments'][$key]['reply_body'] = nl2br($value['reply_body']);
      }
    }

    // FC2用のどこでも有効な単変数
    $data['url'] = '/' . $data['blog']['id'] . '/';
    $data['blog_id'] = UserController::getBlogId($request); // TODO User系でしかこのメソッドは呼ばれないはずなので

    // 年月日系
    $data['now_date'] = (isset($data['date_area']) && $data['date_area']) ? $data['now_date'] : date('Y-m-d');
    $data['now_month_date'] = date('Y-m-1', strtotime($data['now_date']));
    $data['prev_month_date'] = date('Y-m-1', strtotime($data['now_month_date'] . ' -1 month'));
    $data['next_month_date'] = date('Y-m-1', strtotime($data['now_month_date'] . ' +1 month'));

    return $data;
  }

  /**
   * PHPを用いたテンプレートエンジンでHTMLをレンダリング
   * @param Request $request
   * @param string $template_file_path
   * @return string
   * TODO User系のみで用いられるので、後日UserControllerへ移動
   */
  private function renderByPhpTemplate(Request $request, string $template_file_path)
  {
    // アプリプレフィックス
    $prefix = strtolower(Config::get('APP_PREFIX'));

    Log::debug_log(__FILE__ . ":" . __LINE__ . " " . 'Layout[' . $this->layout . ']');
    if ($this->layout == '') {
      // layoutが空の場合は表示処理を行わない
      return "";
    }

    $layout_file_path = Config::get('VIEW_DIR') . ($prefix ? $prefix . '/' : '') . 'layouts/' . $this->layout;
    $device_layout_path = preg_replace('/^(.*?)\.([^\/.]*?)$/', '$1' . Config::get('DEVICE_PREFIX.' . $request->deviceType) . '.$2', $layout_file_path);

    if (defined("THIS_IS_TEST")) {
      $this->layoutFilePath = $layout_file_path; // テスト用に退避
    }

    // 各種テンプレート種類によって分岐
    if (is_file($device_layout_path)) {
      // view/user系 デバイス毎に対応があるテンプレート（現状SPのみ）

      extract($this->data);
      // デバイス毎のファイルがあればデバイス毎のファイルを優先する
      ob_start();
      /** @noinspection PhpIncludeInspection */
      include($device_layout_path);
      return ob_get_clean();

    } elseif (is_file($layout_file_path) && $this->layout !== "fc2_template.php") {
      // view/user系 PCテンプレート

      extract($this->data);
      ob_start();
      /** @noinspection PhpIncludeInspection */
      include($layout_file_path);
      return ob_get_clean();

    } elseif ($this->layout === "fc2_template.php") {
      // FC2タグを用いたユーザーテンプレート

      if (is_null($template_file_path)) {
        throw new InvalidArgumentException("undefined template");
      }
      if (!is_file($template_file_path)) {
        throw new InvalidArgumentException("missing template");
      }

      $this->data = static::preprocessingDataForFc2Template($request, $this->data);

      // 設定されているdataを展開
      extract($this->data);

      // テンプレート表示
      ob_start();
      /** @noinspection PhpIncludeInspection */
      include($template_file_path);
      return ob_get_clean();
    } else {
      $this->layoutFilePath = ""; // テスト用に退避
      Log::debug_log(__FILE__ . ":" . __LINE__ . " " . 'Not Found Layout[' . $layout_file_path . ']');
      return "";
    }
  }

  /**
   * PHPを用いたテンプレートエンジンで本文部分の画面HTMLをレンダリング
   * @param Request $request
   * @param $fw_template
   * @param array $fw_data
   * @param bool $fw_is_prefix // TODO 削除できる想定
   */
  public function display(Request $request, $fw_template, $fw_data = [], $fw_is_prefix = true)
  {
    $fw_template = StringCaseConverter::snakeCase($fw_template);

    // データの設定
    if (count($fw_data)>0) {
      // fw_dataの指定がある場合はそちらを利用
      extract($fw_data);
    } else {
      // fw_dataが無い場合はControllerのdataを展開
      extract($this->data);
    }
    // 展開完了後fw_dataはunset
    unset($fw_data);

    // Template表示
    $fw_template_path = Config::get('VIEW_DIR') . ($fw_is_prefix ? strtolower(Config::get('APP_PREFIX')) . '/' : '') . $fw_template;
    $fw_template_device_path = preg_replace('/^(.*?)\.([^\/.]*?)$/', '$1' . Config::get('DEVICE_PREFIX.' . $request->deviceType) . '.$2', $fw_template_path);
    if (is_file($fw_template_device_path)) {
      if (defined("THIS_IS_TEST")) {
        $this->templateFilePath = $fw_template_device_path; // テスト用に退避
      }
      // デバイス毎のファイルがあればデバイス毎のファイルを優先する
      /** @noinspection PhpIncludeInspection */
      include($fw_template_device_path);
    } elseif (is_file($fw_template_path)) {
      if (defined("THIS_IS_TEST")) {
        $this->templateFilePath = $fw_template_path; // テスト用に退避
      }
      // デバイス毎のファイルがあればデバイス毎のファイルを優先する
      /** @noinspection PhpIncludeInspection */
      include($fw_template_path);
      Log::debug_log(__FILE__ . ":" . __LINE__ . " " . 'Template[' . $fw_template_path . ']');
    } else {
      $this->templateFilePath = "";
      Log::debug_log(__FILE__ . ":" . __LINE__ . " " . 'Not Found Template[' . $fw_template_path . ']');
    }
  }

  /**
   * 表示処理データを取得
   * TODO DELME 利用されている箇所が無い？
   * @param Request $request
   * @param $template
   * @param array $data
   * @param bool $isPrefix
   * @return false|string
   */
  public function fetch(Request $request, $template, $data = [], $isPrefix = true)
  {
    ob_start();
    $this->display($request, $template, $data, $isPrefix);
    return ob_get_clean();
  }

  // 存在しないアクションは404へ
  public function __call($name, $arguments)
  {
    return $this->error404();
  }

  // 404 NotFound Action
  public function error404()
  {
    $this->data['http_status_code'] = 404;
    return 'Common/error404.php';
  }

  public function get(string $key)
  {
    return $this->data[$key];
  }

  public function getOutput(): string
  {
    if (!defined("THIS_IS_TEST")) {
      throw new LogicException("the method is only for testing.");
    }
    return $this->output;
  }

  public function getLayoutFilePath(): string
  {
    if (!defined("THIS_IS_TEST")) {
      throw new LogicException("the method is only for testing.");
    }
    return substr($this->layoutFilePath, strlen(Config::get('VIEW_DIR')));
  }

  public function getTemplateFilePath(): string
  {
    if (!defined("THIS_IS_TEST")) {
      throw new LogicException("the method is only for testing.");
    }
    return substr($this->templateFilePath, strlen(Config::get('VIEW_DIR')));
  }

  public function getResolvedMethod(): string
  {
    if (!defined("THIS_IS_TEST")) {
      throw new LogicException("the method is only for testing.");
    }
    return $this->resolvedMethod;
  }

  public function getRequest(): Request
  {
    if (!defined("THIS_IS_TEST")) {
      throw new LogicException("the method is only for testing.");
    }
    return $this->request;
  }

  public function getData(): array
  {
    if (!defined("THIS_IS_TEST")) {
      throw new LogicException("the method is only for testing.");
    }
    return $this->data;
  }
}
