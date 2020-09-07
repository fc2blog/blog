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
  private $data = array();            // テンプレートへ渡す変数の保存領域
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
    $this->beforeFilter($this->request);

    // アクションの実行(返り値はテンプレートファイル名、レンダリング用データは$this->data)
    $this->resolvedMethod = $method;
    $template = $this->$method($this->request);

    // 空の場合は、規約に則ってテンプレートファイルを決定する
    if (empty($template)) {
      // TODO prefixもつけて、ここでフルパスにしたほうがよくないか？（後でPrefixをわざわざつけている）
      $template = strtolower($this->request->shortControllerName) . '/' . $method . '.php';
    }

    // 出力を$this->outputで保持。後ほどemit()すること。
    // TODO PHPテンプレートとTwigテンプレートの移行中なので、テンプレートファイル名で切り分ける
    if (preg_match("/\.twig\z/u", $template)) {
      $this->renderByTwig($this->request, $template);
    } else {
      ob_start();
      $this->layout($this->request, $template);
      $this->output = ob_get_clean();
    }

    // SSI的なインクルード処理など（現状活用されていない）
    // TODO 必要になるまで削除して良いと思われる
    $this->beforeRender();
  }

  public function emit()
  {
    echo $this->output;
  }

  public function getShortClassName()
  {
  }

  protected function beforeFilter(Request $request)
  {
  }

  protected function beforeRender()
  {
  }

  public function set(string $key, $value)
  {
    $this->data[$key] = $value;
  }

  /**
   * リダイレクト
   * MEMO: Blog idが特定できないときの強制的なSchemaがさだまらない
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
      $e = new RedirectExit(__FILE__ . ":" . __LINE__ . " redirect to {$escaped_url} status code:{$status_code}");
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
   */
  private function renderByTwig(Request $request, string $twig_template): void
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

    // TODO remove me. this is test.
//    $this->setInfoMessage("test info");
//    $this->setWarnMessage("test warn");
//    $this->setErrorMessage("test error");

    $this->data['request'] = $request; // TODO Adhocに追加しているので、どこか適切な場所に移動する
    $blogs_model = new BlogsModel();
    $data = [ // 各種ベースとなるデータ // TODO 整理共通化リファクタリング
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
      $this->output = $twig->render($twig_template_path, $data);
    } catch (LoaderError $e) {
      throw new RuntimeException("Twig error: {$e->getMessage()} {$e->getFile()}:{$e->getTemplateLine()}");
    } catch (RuntimeError $e) {
      throw new RuntimeException("Twig error: {$e->getMessage()} {$e->getFile()}:{$e->getTemplateLine()}");
    } catch (SyntaxError $e) {
      throw new RuntimeException("Twig error: {$e->getMessage()} {$e->getFile()}:{$e->getTemplateLine()}");
    }
  }

  /**
   * PHPを用いたテンプレートエンジンでHTMLをレンダリング
   * @param Request $request
   * @param string|null $fw_template スコープ変数として、 include(〜)の中で利用されている
   */
  private function layout(Request $request, ?string $fw_template)
  {
    // 定義済み変数に関しては展開させない
    unset($this->data['fw_template']);

    // 設定されているdataを展開
    extract($this->data);

    // アプリプレフィックス
    $prefix = strtolower(Config::get('APP_PREFIX'));

    Log::debug_log(__FILE__ . ":" . __LINE__ . " " . 'Layout[' . $this->layout . ']');
    if ($this->layout == '') {
      // layoutが空の場合は表示処理を行わない
      return;
    }

    $fw_template_path = Config::get('VIEW_DIR') . ($prefix ? $prefix . '/' : '') . 'layouts/' . $this->layout;
    $fw_template_device_path = preg_replace('/^(.*?)\.([^\/\.]*?)$/', '$1' . Config::get('DEVICE_PREFIX.' . $request->deviceType) . '.$2', $fw_template_path);
    if (is_file($fw_template_device_path)) {
      if (defined("THIS_IS_TEST")) {
        $this->layoutFilePath = $fw_template_path; // テスト用に退避
      }
      // デバイス毎のファイルがあればデバイス毎のファイルを優先する
      /** @noinspection PhpIncludeInspection */
      include($fw_template_device_path);
    } elseif (is_file($fw_template_path)) {
      if (defined("THIS_IS_TEST")) {
        $this->layoutFilePath = $fw_template_path; // テスト用に退避
      }
      /** @noinspection PhpIncludeInspection */
      include($fw_template_path);
    } else {
      $this->layoutFilePath = ""; // テスト用に退避
      Log::debug_log(__FILE__ . ":" . __LINE__ . " " . 'Not Found Layout[' . $fw_template_path . ']');
    }
  }

  /**
   * PHPを用いたテンプレートエンジンで本文部分の画面HTMLをレンダリング
   * @param Request $request
   * @param $fw_template
   * @param array $fw_data
   * @param bool $fw_is_prefix
   */
  public function display(Request $request, $fw_template, $fw_data = array(), $fw_is_prefix = true)
  {
    $fw_template = StringCaseConverter::snakeCase($fw_template);
    // データの設定
    if (count($fw_data)) {
      // 定義済み変数に関しては展開させない
      unset($fw_data['fw_template']);
      unset($fw_data['fw_is_prefix']);

      // fw_dataがある場合は渡された値のみ展開
      extract($fw_data);
    } else {
      // 定義済み変数に関しては展開させない
      unset($this->data['fw_template']);
      unset($this->data['fw_is_prefix']);

      // displayDataが無い場合はControllerのdataを展開
      extract($this->data);
    }
    // 展開完了後fw_dataは解除
    unset($fw_data);

    // Template表示
    $fw_template_path = Config::get('VIEW_DIR') . ($fw_is_prefix ? strtolower(Config::get('APP_PREFIX')) . '/' : '') . $fw_template;
    $fw_template_device_path = preg_replace('/^(.*?)\.([^\/\.]*?)$/', '$1' . Config::get('DEVICE_PREFIX.' . $request->deviceType) . '.$2', $fw_template_path);
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
   * @param Request $request
   * @param $template
   * @param array $data
   * @param bool $isPrefix
   * @return false|string
   */
  public function fetch(Request $request, $template, $data = array(), $isPrefix = true)
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

