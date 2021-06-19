<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Exception\RedirectExit;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Service\BlogService;
use Fc2blog\Service\TwigService;
use Fc2blog\Util\Log;
use Fc2blog\Web\Controller\Admin\AdminController;
use Fc2blog\Web\Controller\User\UserController;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use InvalidArgumentException;
use LogicException;
use RuntimeException;
use Twig\Error\Error;

abstract class Controller
{
    /** @var array<string, mixed> */
    protected $data = [ // テンプレートへ渡す変数の保存領域
        'http_status_code' => 200
    ];
    protected $layout = '';  // 表示ページのレイアウトテンプレート
    protected $output = '';  // 送信するデータ、HTML等
    private $resolvedMethod;
    protected $request;
    protected $responseHeaders = [
        'X-Frame-Options' => 'DENY',
        'Content-Type' => 'text/html; charset=UTF-8',
    ];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function execute($method): void
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

        $this->resolvedMethod = $method;

        // アクションの実行(返り値はテンプレートファイルパスまたは空文字、レンダリング用データは$this->data)
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
        } elseif ($this->layout === 'fc2_template.php' && $this instanceof UserController) {
            $this->output = $this->renderByFc2Template($this->request, $template_path);
        }
        // $this->layout === '' の場合は、空ボディか、$this->outputにすでになにか入れられているという想定
    }

    /**
     * ヘッダーおよび$this->outputの送信
     * TODO Output Bufferによりエラー表示を隠蔽する
     */
    public function emit(): void
    {
        http_response_code($this->getStatusCode());

        if (!headers_sent()) {
            foreach ($this->responseHeaders as $header_name => $header_value) {
                header("{$header_name}: {$header_value}");
            }
        }

        if ( # show DEBUG MODE warnings.
            strpos("text/html", $this->getContentType()) > 0 &&
            defined("ERROR_ON_DISPLAY") && ERROR_ON_DISPLAY === "1"
        ) {
            echo "<hr><span style='color:red'>THE BLOG CONFIGURATION IS DANGER. PLEASE REMOVE `ERROR_ON_DISPLAY` in production.</span><hr>";
        }

        echo $this->output;
    }

    protected function isInvalidAjaxRequest(Request $request): bool
    {
        # HTTP_X_REQUESTED_WITHを検証
        if (
            !isset($request->server['HTTP_X_REQUESTED_WITH']) ||
            $request->server['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest'
        ) {
            return true;
        }

        # ORIGINを検証
        if (
            isset($request->server['HTTP_ORIGIN']) &&
            $request->server['HTTP_ORIGIN'] !== $request->getHostUrl()
        ) {
            return true;
        }

        return false;
    }

    protected function beforeFilter(Request $request)
    {
    }

    public function set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $this->data[$key];
    }

    public function getStatusCode(): int
    {
        return (int)$this->data['http_status_code'];
    }

    public function setStatusCode(int $code = 200): void
    {
        $this->data['http_status_code'] = $code;
    }

    public function setContentType(string $mime_type = 'text/html; charset=UTF-8'): void
    {
        $this->responseHeaders['Content-Type'] = $mime_type;
    }

    public function getContentType(): string
    {
        return $this->responseHeaders['Content-Type'];
    }

    /**
     * リダイレクト
     * MEMO: Blog idが特定できないときの強制的なSchemaがさだまらない
     * TODO: この時点でリダイレクトせず、 emit時にヘッダー送信する形にリファクタリングすべき
     * @param Request $request
     * @param $url
     * @param string $hash
     * @param bool $full_url BlogIdが特定できるとき、http(s)://〜からのフルURLを出力する、 HTTP<>HTTPS強制リダイレクト時に必要
     * @param string|null $blog_id
     * @throws RedirectExit
     */
    protected function redirect(Request $request, $url, string $hash = '', bool $full_url = false, string $blog_id = null)
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
    protected function redirectBack(Request $request, $url, string $hash = '')
    {
        // 元のURLに戻す
        if (!empty($request->server['HTTP_REFERER'])) {
            $this->redirect($request, $request->server['HTTP_REFERER']);
        }
        // リファラーが取れなければメインへ飛ばす
        $this->redirect($request, $url, $hash);
    }

    /**
     * TwigテンプレートエンジンでHTMLをレンダリング
     * 結果は $this->output に保管される
     * @param Request $request
     * @param string $twig_template
     * @return string
     */
    private function renderByTwig(Request $request, string $twig_template): string
    {
        $twig = TwigService::getTwigInstance();

        $twig_template_path = $twig_template;
        $twig_template_device_path = preg_replace("/\.twig\z/u", '_' . App::getDeviceTypeStr($request) . '.twig', $twig_template_path);
        if (is_file(TwigService::getTwigBasePath() . $twig_template_device_path)) { // デバイス用ファイルがある
            $twig_template_path = $twig_template_device_path;
        }

        if (!is_file(TwigService::getTwigBasePath() . $twig_template_path)) {
            $base_path = TwigService::getTwigBasePath();
            throw new InvalidArgumentException("Twig error: missing template: {$base_path}{$twig_template_path}");
        }

        // テンプレートエンジンに引き渡すデータを生成
        $this->data['request'] = $request;
        if ($this instanceof AdminController) {
            // Admin系データの生成
            $blogs_model = new BlogsModel();
            $data = [
                'req' => $request,
                'sig' => Session::get('sig'),
                'lang' => $request->lang,
                'debug' => Config::get('APP_DEBUG') != 0,
                'preview_active_blog_url' => App::userURL($request, ['controller' => 'entries', 'action' => 'index', 'blog_id' => $this->getBlogIdFromSession()]), // 代用できそう
                'is_register_able' => (Config::get('USER.REGIST_SETTING.FREE') == Config::get('USER.REGIST_STATUS')), // TODO 意図する解釈確認
                'active_menu' => App::getActiveMenu($request),
                'isLogin' => $this->isLogin(),
                'nick_name' => $this->getNickName(),
                'blog_list' => $blogs_model->getSelectList($this->getUserId()),
                'is_selected_blog' => $this->isSelectedBlog(),
                'flash_messages' => $this->removeMessage(),
                'js_common' => [
                    'isURLRewrite' => $request->urlRewrite,
                    'baseDirectory' => $request->baseDirectory,
                    'deviceType' => $request->deviceType,
                    'deviceArgs' => App::getArgsDevice($request)
                ],
                'cookie_common' => [
                    'expire' => Config::get('COOKIE_EXPIRE'),
                ]
            ];
            // リクエストからログインblogを特定し、保存
            if (BlogService::getById($this->getBlogIdFromSession()) !== false && is_string($this->getBlogIdFromSession())) {
                $data['blog'] = BlogService::getById($this->getBlogIdFromSession());
                $data['blog']['url'] = BlogsModel::getFullHostUrlByBlogId($this->getBlogIdFromSession(), Config::get('DOMAIN_USER')) . "/" . $this->getBlogIdFromSession() . "/";
            }
        } else {
            // User系画面のデータ生成
            $data = [
                'req' => $request,
                'lang' => $request->lang,
            ];
        }

        $data = array_merge($data, $this->data);

        try {
            return $twig->render($twig_template_path, $data);
        } catch (Error $e) {
            throw new RuntimeException("Twig error: {$e->getMessage()} {$e->getFile()}:{$e->getTemplateLine()}");
        }
    }

    // 404 NotFound Action
    protected function error404(): string
    {
        $this->setStatusCode(404);
        return 'user/common/error404.twig';
    }

    // 403 Forbidden
    protected function error403(): string
    {
        $this->setStatusCode(403);
        return 'user/common/error403.twig';
    }

    // 400 BadRequest
    protected function error400(): string
    {
        $this->setStatusCode(400);
        return 'user/common/error400.twig';
    }

    // 500 BadRequest
    protected function error500(): string
    {
        $this->setStatusCode(500);
        return 'user/common/error500.twig';
    }

    public function getOutput(): string
    {
        if (!defined("THIS_IS_TEST")) {
            throw new LogicException("the method is only for testing.");
        }
        return $this->output;
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
