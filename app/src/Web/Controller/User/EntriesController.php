<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\User;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\Blog;
use Fc2blog\Model\BlogPluginsModel;
use Fc2blog\Model\BlogSettingsModel;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Model\CategoriesModel;
use Fc2blog\Model\CommentsModel;
use Fc2blog\Model\EntriesModel;
use Fc2blog\Model\Model;
use Fc2blog\Model\TagsModel;
use Fc2blog\Service\BlogService;
use Fc2blog\Util\Log;
use Fc2blog\Web\Fc2BlogTemplate;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use InvalidArgumentException;
use Tuupola\Base62Proxy;

class EntriesController extends UserController
{
    /**
     * 記事系統の前処理
     * @param Request $request
     */
    protected function beforeFilter(Request $request): void
    {
        parent::beforeFilter($request);

        // ブログID指定があるかチェック
        $blog_id = $request->getBlogId();
        if (!$blog_id) {
            Log::notice("missing blog_id parameter. redirect to top.");
            $this->redirect($request, ['controller' => 'Blogs', 'action' => 'index']);
        }
        $this->set('blog_id', $blog_id);

        // 指定のブログが実在するかチェック
        if (!($blog = BlogService::getById($blog_id)) || !($blog instanceof Blog)) {
            Log::notice("not found blog, redirect to top. blog_id: {$blog_id}");
            $this->redirect($request, ['controller' => 'Blogs', 'action' => 'index']);
        }

        // BlogのSSL_Enableの設定と食い違うなら強制リダイレクトする
        // URL構造そのままでリダイレクトするためにRequestUriを用いているがもっとベターな方法があるかもしれない
        if (!BlogsModel::isCorrectHttpSchemaByBlog($request, $blog)) {
            Log::debug("mismatch access schema and blog's schema. redirect to correct schema. blog_id:{$blog['id']}");
            $this->redirect($request, $request->uri, '', true, $blog['id']);
        }

        $this->set('blog', $blog);
        $blog_settings_model = new BlogSettingsModel();
        $this->set('blog_setting', $blog_settings_model->findByBlogId($blog_id));

        // 自身の所持しているブログ判定
        $self_blog = $this->isLoginBlog($request);
        $this->set('self_blog', $self_blog);

        // 非公開モードの場合はパスワード認証画面へ遷移
        if ($blog['open_status'] == Config::get('BLOG.OPEN_STATUS.PRIVATE')
            && !Session::get($this->getBlogPasswordKey($blog['id']))
            && $request->methodName != 'blog_password'
            && !$self_blog
        ) {
            Log::debug("password required. redirect to password auth page. blog_id:{$blog['id']}");
            $this->redirect($request, array('action' => 'blog_password', 'blog_id' => $blog_id));
        }

        // 予約投稿と期間投稿エントリーの更新処理
        // TODO: ここにあるのがふさわしいのか？
        if (Config::get('CRON') === false) {
            $entries_model = new EntriesModel();
            $entries_model->updateReservation($blog_id);
            $entries_model->updateLimited($blog_id);
        }
    }

    /**
     * 一覧表示 アクション
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $request->getBlogId();
        if (!$blog_id) {
            Log::notice("missing blog_id parameter. redirect to top. blog_id: {$blog_id}");
            $this->redirect($request, ['controller' => 'Blogs', 'action' => 'index']);
        }

        // 記事一覧データ設定
        $options = [
            'where' => 'blog_id=?',
            'params' => [$blog_id],
        ];
        $areas = $request->get('page') ? [] : ['index_area'];
        $this->setEntriesData($request, $options, $areas);

        return $this->getFc2TemplatePath($request->getBlogId());
    }

    /**
     * 検索
     * @param Request $request
     * @return string
     */
    public function search(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $where = 'blog_id=?';
        $params = array($request->getBlogId());

        // 検索ワード取得
        if ($keyword = $request->get('q')) {
            $this->set('sub_title', $request->get('q'));
            $keyword = Model::escape_wildcard($keyword);
            $keyword = "%{$keyword}%";
            $where .= ' AND (title LIKE ? OR body LIKE ?)';
            $params = array_merge($params, array($keyword, $keyword));
        }

        $options = array(
            'where' => $where,
            'params' => $params,
        );
        $this->setEntriesData($request, $options, array('search_area'));
        return $this->getFc2TemplatePath($request->getBlogId());
    }

    /**
     * カテゴリー検索
     * @param Request $request
     * @return string
     */
    public function category(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $request->getBlogId();
        $category_id = $request->get('cat');

        // カテゴリー名取得
        $category = Model::load('Categories')->findByIdAndBlogId($category_id, $blog_id);
        $this->set('sub_title', $category['name']);

        // 記事一覧データ設定
        $where = 'entries.blog_id=?';
        $where .= ' AND entry_categories.blog_id=?';
        $where .= ' AND entry_categories.category_id=?';
        $where .= ' AND entries.id=entry_categories.entry_id';
        $params = array($blog_id, $blog_id, $category_id);

        $order = $category['category_order'] == Config::get('CATEGORY.ORDER.ASC') ? 'ASC' : 'DESC';

        $options = array(
            'fields' => 'entries.*',
            'where' => $where,
            'from' => 'entry_categories',
            'params' => $params,
            'order' => 'entries.posted_at ' . $order . ', entries.id ' . $order,
        );
        $this->setEntriesData($request, $options, array('category_area'));
        return $this->getFc2TemplatePath($request->getBlogId());
    }

    /**
     * タグ検索
     * @param Request $request
     * @return string
     */
    public function tag(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // タグ検索
        $blog_id = $request->getBlogId();
        $tag_name = $request->get('tag');

        $tag = Model::load('Tags')->findByNameAndBlogId($tag_name, $blog_id);
        $tag_id = empty($tag) ? 0 : $tag['id'];

        $this->set('sub_title', $tag_name);

        // 記事一覧データ設定
        $where = 'entries.blog_id=?';
        $where .= ' AND entry_tags.blog_id=?';
        $where .= ' AND entry_tags.tag_id=?';
        $where .= ' AND entries.id=entry_tags.entry_id';
        $params = array($blog_id, $blog_id, $tag_id);

        $options = array(
            'fields' => 'entries.*',
            'where' => $where,
            'from' => 'entry_tags',
            'params' => $params,
        );
        $this->setEntriesData($request, $options, array('tag_area'));
        return $this->getFc2TemplatePath($request->getBlogId());
    }

    /**
     * 年別,月別,日別表示
     * @param Request $request
     * @return string
     */
    public function date(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // 開始日付と終了日付の計算
        preg_match('/^([0-9]{4})([0-9]{2})?([0-9]{2})?$/', $request->get('date'), $matches);
        $dates = $matches + array('', date('Y'), 0, 0);
        list($start, $end) = App::calcStartAndEndDate((int)$dates[1], (int)$dates[2], (int)$dates[3]);

        // 記事一覧データ設定
        $where = 'blog_id=? AND ?<=posted_at AND posted_at<=?';
        $params = array($request->getBlogId(), $start, $end);

        $options = array(
            'where' => $where,
            'params' => $params,
        );
        $this->setEntriesData($request, $options, array('date_area'));
        $this->set('now_date', date('Y-m-d', strtotime($start)));
        return $this->getFc2TemplatePath($request->getBlogId());
    }

    /**
     * アーカイブ表示
     * @param Request $request
     * @return string
     */
    public function archive(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // 記事一覧データ設定
        $options = array(
            'fields' => array(
                'id', 'blog_id', 'title', 'posted_at', 'comment_count',
                Config::get('ENTRY.AUTO_LINEFEED.NONE') . ' as auto_linefeed',
                'SUBSTRING(body, 1, 20) as body'
            ),
            'where' => 'blog_id=?',
            'params' => array($request->getBlogId()),
        );
        $this->setEntriesData($request, $options, array('titlelist_area'));
        $this->set('sub_title', __("List of articles"));
        return $this->getFc2TemplatePath($request->getBlogId());
    }

    /**
     * プレビュー表示
     * @param Request $request
     * @return string
     */
    public function preview(Request $request): string
    {
        // XSS-Protection無効
        if (!headers_sent()) {
            header("X-XSS-Protection: 0");
        }

        // preview処理用
        $blog_id = $request->getBlogId();

        // 投稿者のブログIDチェック
        if ($blog_id != $this->getAdminBlogId() && !Model::load('Blogs')->isUserHaveBlogId($this->getAdminUserId(), $blog_id)) {
            return $this->error404();
        }

        // 記事のプレビュー(POST)
        if ($request->get('entry')) {
            return $this->preview_entry($request);
        }

        // FC2テンプレートのプレビュー
        if ($request->get('fc2_id') && $request->get('device_type')) {
            return $this->preview_fc2_template($request);
        }

        // テンプレートのプレビュー
        if ($request->get('blog_template') || $request->get('template_id')) {
            return $this->preview_template($request);
        }

        // プラグインのプレビュー
        if ($request->get('blog_plugin') || $request->get('plugin_id')) {
            return $this->preview_plugin($request);
        }

        // 当てはまらない場合は404画面を表示
        return $this->error404();
    }

    /**
     * FC2テンプレート用のプレビュー
     * @param Request $request
     * @return string
     */
    private function preview_fc2_template(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $request->getBlogId();

        // 記事一覧データ設定
        $options = array(
            'where' => 'blog_id=?',
            'params' => array($request->getBlogId()),
        );
        $pages = $request->get('page') ? array() : array('index_area');
        $this->setEntriesData($request, $options, $pages);

        // テンプレートのプレビュー
        $device_key = App::getDeviceFc2Key($request->get('device_type'));
        $template = Model::load('Fc2Templates')->findByIdAndDevice($request->get('fc2_id'), $device_key);
        if (empty($template)) {
            return $this->error404();
        }

        $html = $template['html'];
        $css = $template['css'];

        // テンプレートのシンタックスチェック
        $syntax = Fc2BlogTemplate::fc2TemplateSyntax($html);
        if ($syntax !== true) {
            return 'user/entries/syntax_error.twig';
        }

        // FC2用のテンプレートで表示
        return $this->getFc2TemplatePath($blog_id, $html, $css, true);
    }

    /**
     * テンプレート用のプレビュー
     * @param Request $request
     * @return string
     */
    private function preview_template(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $request->getBlogId();

        // 記事一覧データ設定
        $options = array(
            'where' => 'blog_id=?',
            'params' => array($request->getBlogId()),
        );
        $pages = $request->get('page') ? array() : array('index_area');
        $this->setEntriesData($request, $options, $pages);

        // テンプレートのプレビュー
        if ($request->get('template_id')) {
            $blog_template = Model::load('BlogTemplates')->findByIdAndBlogId($request->get('template_id'), $blog_id);
            $html = $blog_template['html'];
            $css = $blog_template['css'];
        } else {
            $html = $request->get('blog_template.html');
            $css = $request->get('blog_template.css');
        }

        // テンプレートのシンタックスチェック
        $syntax = Fc2BlogTemplate::fc2TemplateSyntax($html);
        if ($syntax !== true) {
            return 'user/entries/syntax_error.twig';
        }

        // FC2用のテンプレートで表示
        return $this->getFc2TemplatePath($blog_id, $html, $css, true);
    }

    /**
     * プラグイン用のプレビュー
     * @param Request $request
     * @return string
     */
    private function preview_plugin(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_id = $request->getBlogId();

        // プラグインのプレビュー情報取得
        if ($request->get('plugin_id')) {
            // DBからプレビュー情報取得
            $preview_plugin = Model::load('Plugins')->findById($request->get('plugin_id'));
            $preview_plugin['category'] = $request->get('category');
        } else {
            // リクエストパラメータからプレビュー情報取得
            $preview_plugin = $request->get('blog_plugin');
            $preview_plugin['list'] = '';      // TODO:リストスタイルは未作成
            $preview_plugin['attribute'] = ''; // TODO:属性は未作成
        }
        $contents = $preview_plugin['contents'];

        // テンプレートのシンタックスチェック
        $syntax = BlogPluginsModel::fc2PluginSyntax($contents);
        if ($syntax !== true) {
            return 'user/entries/syntax_error.twig';
        }

        // プラグインのPHPファイル作成
        BlogPluginsModel::createPlugin($contents, $blog_id);

        // 入力データからデータを作成
        $category = $preview_plugin['category'];
        $device_type = $preview_plugin['device_type'];
        $plugin = array(
            'id' => 'preview',
            'blog_id' => $blog_id,
            'title' => $preview_plugin['title'],
            'title_align' => $preview_plugin['title_align'],
            'title_color' => $preview_plugin['title_color'],
            'list' => $preview_plugin['list'],
            'contents' => $preview_plugin['contents'],
            'contents_align' => $preview_plugin['contents_align'],
            'contents_color' => $preview_plugin['contents_color'],
            'attribute' => $preview_plugin['attribute'],
            'device_type' => $device_type,
            'category' => $category,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );

        // スマフォ版のプラグインのプレビュー表示
        if ($device_type == App::DEVICE_SP) {
            $this->set('s_plugin', $plugin);
            $this->setAreaData(array('spplugin_area'));
            return $this->getFc2TemplatePath($blog_id);
        }

        // 記事一覧データ設定(スマフォ版以外のプレビュー表示)
        $options = array(
            'where' => 'blog_id=?',
            'params' => array($request->getBlogId()),
        );
        $pages = $request->get('page') ? array() : array('index_area');
        $this->setEntriesData($request, $options, $pages);

        // 通常のプラグインリストに追加する
        $plugins = (new BlogPluginsModel())->findByDeviceTypeAndCategory($this->request->deviceType, $category, $blog_id);
        $id = $request->get('id');
        if (empty($id)) {
            // 新規プラグインは最後尾に追加する
            $plugins[] = $plugin;
        } else {
            // 編集の場合は上書きする
            foreach ($plugins as $key => $value) {
                if ($value['id'] == $id) {
                    $plugins[$key] = $plugin;
                }
            }
        }
        $this->set('t_plugins_' . $category, $plugins);

        // FC2用のテンプレートで表示
        return $this->getFc2TemplatePath($blog_id);
    }

    /**
     * 記事用のプレビュー
     * @param Request $request
     * @return string
     */
    private function preview_entry(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_id = $request->getBlogId();

        // DBの代わりにリクエストから取得
        $entry = array(
            'id' => 0,
            'blog_id' => $blog_id,
            'title' => $request->get('entry.title'),
            'body' => $request->get('entry.body'),
            'extend' => $request->get('entry.extend'),
            'posted_at' => $request->get('entry.posted_at', date('Y-m-d H:i:s')),
            'auto_linefeed' => $request->get('entry.auto_linefeed'),
            'open_status' => Config::get('ENTRY.OPEN_STATUS.OPEN'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        );
        $entry['categories'] = Model::load('Categories')->findByIdsAndBlogId($request->get('entry_categories.category_id'), $blog_id);
        foreach ($entry['categories'] as $key => $value) {
            $entry['categories'][$key]['entry_id'] = 0;
        }
        $entry['tags'] = array();
        $tags = $request->get('entry_tags');
        if (is_countable($tags) && count($tags)) {
            foreach ($tags as $tag) {
                $entry['tags'][] = array(
                    'id' => 0,
                    'blog_id' => $blog_id,
                    'name' => $tag,
                    'count' => 0,
                );
            }
        }
        $this->set('entry', $entry);
        $this->set('comments', array());

        $this->set('sub_title', $entry['title']);

        // FC2用のテンプレートで表示
        $areas = array('permanent_area');
        if (App::isPC($request)) {
            $areas[] = 'comment_area';
        }
        $this->setAreaData($areas);
        return $this->getFc2TemplatePath($entry['blog_id']);
    }

    /**
     * 詳細（１エントリ）表示 アクション
     * @param Request $request
     * @return string
     */
    public function view(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $request->getBlogId();
        $entry_id = (int)$request->get('id');

        // 記事詳細取得
        $entries_model = new EntriesModel();
        $entry = $entries_model->getEntry($entry_id, $blog_id);
        if (!$entry) return $this->error404();

        $this->set('entry', $entry);
        $this->set('sub_title', $entry['title']); // HTMLのサブタイトル

        // 表示エリア、データセット分岐処理
        // （スマホはコメントと、コメント投稿画面が別々）
        $sp_comment_view_mode = $request->get('m2'); // スマホ用、フォーム、返信画面判定パラメタ
        $areas = [];
        switch ($sp_comment_view_mode) {
            case 'form': // コメント投稿表示(スマフォ)
                $areas[] = 'form_area';
                break;

            case 'res': // コメント一覧表示(スマフォ)
                $this->setCommentsData($request, $blog_id, $entry_id, $entry, true);
                $areas[] = 'comment_area';
                break;

            default: // PCや上記以外
                // PC場合、記事のコメント必要
                if (App::isPC($request)) {
                    $is_comment_settled = $this->setCommentsData($request, $blog_id, $entry_id, $entry, false);
                    if ($is_comment_settled) {
                        $areas[] = 'comment_area';
                    }
                }
                $this->setNextPrevEntryData($blog_id, $entry);
                $areas[] = 'permanent_area';
                break;
        }

        $this->setAreaData($areas);

        // FC2用のテンプレートで表示
        return $this->getFc2TemplatePath($entry['blog_id']);
    }

    /**
     * 記事の「次の記事」「前の記事」情報のセット
     * @param string $blog_id
     * @param array $entry
     */
    private function setNextPrevEntryData(string $blog_id, array $entry): void
    {
        $entries_model = new EntriesModel();
        $blog_settings_model = new BlogSettingsModel();

        // ブログの設定情報取得
        $blog_setting = $blog_settings_model->findByBlogId($blog_id);

        // 前後の記事取得
        $is_asc = $blog_setting['entry_order'] == Config::get('ENTRY.ORDER.ASC');
        $this->set('next_entry', $is_asc ? $entries_model->nextEntry($entry) : $entries_model->prevEntry($entry));
        $this->set('prev_entry', $is_asc ? $entries_model->prevEntry($entry) : $entries_model->nextEntry($entry));
    }

    /**
     * 記事のコメント情報のセット(パスワード制限時はセットしない)
     * @param Request $request
     * @param string $blog_id
     * @param int $entry_id
     * @param array $entry
     * @param bool $is_need_paging
     * @return bool セットしたか（パスワード制限でブロックされなかったか）
     */
    private function setCommentsData(Request $request, string $blog_id, int $entry_id, array $entry, bool $is_need_paging): bool
    {
        if ($this->isEntryNeedAuth($entry) && !$this->isUserAuthedToEntry($request, $entry)) {
            return false;
        }

        $comments_model = new CommentsModel();
        $blog_settings_model = new BlogSettingsModel();

        // ブログの設定情報取得
        // TODO あるエントリのcommentsを取得するこれらはモデルに移動したほうがよいのではないか？
        $blog_setting = $blog_settings_model->findByBlogId($blog_id);

        $options = $comments_model->getCommentListOptionsByBlogSetting($blog_id, $entry_id, $blog_setting);
        if ($is_need_paging) {
            // コメント一覧を取得(ページング用)
            $options['page'] = $request->get('page', 0, Request::VALID_UNSIGNED_INT);
            $this->set('paging', $comments_model->getPaging($options));
        }
        $comments = $comments_model->find('all', $options);
        $comments = $comments_model->decorateByBlogSetting($request, $comments, $blog_setting, $this->isLoginBlog($request));
        $this->set('comments', $comments);

        return true;
    }

    /**
     * アクセスユーザーが該当エントリのアクセス権があるか？
     * @param $request
     * @param $entry
     * @return bool
     */
    private function isUserAuthedToEntry($request, $entry): bool
    {
        return (
            $this->isLoginBlog($request) || // 管理者であるか
            Session::get($this->getEntryPasswordKey($entry['blog_id'], $entry['id']), false) // パスワードアクセスで許可されているか
        );
    }

    /**
     * 指定のエントリはアクセス権が必要か？
     * @param $entry
     * @return bool
     */
    private function isEntryNeedAuth($entry): bool
    {
        if (!isset($entry['open_status'])) {
            throw new InvalidArgumentException('missing open_status');
        }
        return $entry['open_status'] == Config::get('ENTRY.OPEN_STATUS.PASSWORD');
    }

    /**
     * プラグインページの表示
     * @param Request $request
     * @return string
     */
    public function plugin(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $request->getBlogId();
        $id = $request->get('id');

        // プラグイン取得
        $plugin = Model::load('BlogPlugins')->findByIdAndBlogId($id, $blog_id);
        $this->set('s_plugin', $plugin);

        // FC2用のテンプレートで表示
        $this->setAreaData(array('spplugin_area'));
        return $this->getFc2TemplatePath($blog_id);
    }

    /**
     * 記事のパスワード認証
     * @param Request $request
     * @return string
     * TODO POST化
     */
    public function password(Request $request): string
    {
        $blog_id = $request->getBlogId();
        $id = $request->get('id');

        // 記事詳細取得
        $entry = Model::load('Entries')->findByIdAndBlogId($id, $blog_id);
        if (!$entry) {
            $this->redirect($request, array('action' => 'index', 'blog_id' => $blog_id));
        }

        // パスワード入力チェック
        if ($entry['password'] === '') {
            // パスワード未設定の場合は全体のパスワードを設定
            $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);
            $entry['password'] = $blog_setting['entry_password'];
        }
        if ($entry['password'] === $request->get('password', '')) {
            // パスワードが合致すればセッションに記録
            Session::set($this->getEntryPasswordKey($entry['blog_id'], $entry['id']), true);
        }

        $this->redirect($request, array('action' => 'view', 'blog_id' => $blog_id, 'id' => $id));
        return "";
    }

    /**
     * ブログのパスワード認証
     * @param Request $request
     * @return string
     */
    public function blog_password(Request $request): string
    {
        $blog_id = $request->getBlogId();
        if (is_null($blog = BlogService::getById($blog_id))) {
            return $this->error404();
        }

        // プライベートブログではない、あるいは認証済み、ログイン済みならリダイレクト
        if ($blog['open_status'] != Config::get('BLOG.OPEN_STATUS.PRIVATE') || Session::get($this->getBlogPasswordKey($blog->id)) || $this->isLoginBlog($request)) {
            $this->redirect($request, ['action' => 'index', 'blog_id' => $blog_id]);
        }

        // 認証処理
        // TODO Sigがない
        if ($request->get('blog') && $request->isPost()) {
            if (password_verify($request->get('blog.password'), $blog->blog_password)) {
                Session::set($this->getBlogPasswordKey($blog->id), true);
                $this->set('auth_success', true); // for testing.
                $this->redirect($request, ['action' => 'index', 'blog_id' => $blog_id]);
            }
            $this->set('errors', ['password' => __('The password is incorrect!')]);
        }

        $this->set('blog', $blog);
        return "user/entries/blog_password.twig";
    }

    /**
     * コメント投稿
     * @param Request $request
     * @return string
     * TODO regist -> registration しかし互換性が壊れる
     */
    public function comment_regist(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $blog_id = $request->getBlogId();

        // ブログの設定情報取得(captchaの使用可否で画面切り替え)
        $blog_setting = (new BlogSettingsModel())->findByBlogId($blog_id);
        $blog = (new BlogsModel())->findById($blog_id);
        $is_captcha = $blog_setting['comment_captcha'] == Config::get('COMMENT.COMMENT_CAPTCHA.USE');

        // FC2テンプレートにリクエスト情報を合わせる
        if (!$is_captcha || !$request->isArgs('token')) {
            $pattern = [
                'comment.no' => 'comment.entry_id',
                'comment.pass' => 'comment.password',
                'comment.himitu' => 'comment.open_status',
            ];
            $request->combine($pattern); // 引数のキーを入れ替える
            if ($request->get('comment.open_status') == 'on') {
                $request->set('comment.open_status', Config::get('COMMENT.OPEN_STATUS.PRIVATE'));
            }
        }

        $entry_id = $request->get('comment.entry_id');

        // 記事詳細取得
        $entries_model = new EntriesModel();
        $entry = $entries_model->getCommentAcceptedEntry($entry_id, $blog_id);
        if (!$entry) {
            $this->redirect($request, ['action' => 'view', 'blog_id' => $blog_id, 'id' => $entry_id]);
        }

        // 公開非公開のプルダウン用バリエーション（固定）
        $this->set('open_status_user_list', CommentsModel::getOpenStatusUserList());

        // CAPTCHA用に確認画面を挟む
        if ($is_captcha && !$request->isArgs('token')) {
            return "user/entries/register_comment_form.twig";
        }

        // 記事のカテゴリ一覧を取得 TODO:後でcacheを使用する形に
        $entry['categories'] = (new CategoriesModel)->getEntryCategories($blog_id, $entry_id);
        $entry['tags'] = (new TagsModel())->getEntryTags($blog_id, $entry_id);
        $this->set('entry', $entry);

        // 入力チェック処理
        $comments_model = new CommentsModel();
        $errors = array();
        $white_list = array('entry_id', 'name', 'title', 'mail', 'url', 'body', 'password', 'open_status');
        $errors['comment'] = $comments_model->registerValidate($request->get('comment'), $data, $white_list);
        $errors['token'] = $is_captcha && CommonController::isValidCaptcha($request) ? "" : __('Token authentication is invalid');
        if (empty($errors['comment']) && empty($errors['token'])) {
            $data['blog_id'] = $blog_id;  // ブログIDの設定
            // trip_hashの生成
            if (isset($data['password']) && strlen($data['password']) > 0) {
                $stretch_num = strlen($data['password']) % 10 + 1;
                $trip_salt = $blog['trip_salt'];
                $trip_hash = $trip_salt . $data['password'];
                for ($i = 0; $i < $stretch_num; $i++) {
                    $trip_hash = Base62Proxy::encode(hash('sha256', $trip_hash, true));
                }
                $trip_hash_length = 8;
                $data['trip_hash'] = substr($trip_hash, 0, $trip_hash_length);
            } else {
                $data['trip_hash'] = '';
            }
            if ($id = $comments_model->insertByBlogSetting($request, $data, $blog_setting)) {
                $this->redirect($request, array('action' => 'view', 'blog_id' => $blog_id, 'id' => $entry_id), '#comment' . $id);
            }
        }

        // Captcha使用時のエラー画面
        if ($is_captcha) {
            $this->set('errors', $errors);
            return "user/entries/register_comment_form.twig";
        }

        // コメント投稿エラー
        $this->fc2CommentError('comment', $errors['comment'], $data);

        // FC2用のテンプレートで表示
        $this->setAreaData(array(App::isPC($request) ? 'comment_area' : 'form_area'));
        return $this->getFc2TemplatePath($entry['blog_id']);
    }

    /**
     * コメント編集画面
     * @param Request $request
     * @return string
     */
    public function comment_edit(Request $request): string
    {
        $blog_id = $request->getBlogId();

        // ブログの設定情報を取得
        $blog_setting = (new BlogSettingsModel())->findByBlogId($blog_id);
        $is_captcha = $blog_setting['comment_captcha'] == Config::get('COMMENT.COMMENT_CAPTCHA.USE');

        // FC2テンプレートの引数を受け側で合わせる
        if (!$is_captcha || !$request->isArgs('token')) {
            $pattern = [
                'edit.rno' => 'comment.id',
                'edit.name' => 'comment.name',
                'edit.title' => 'comment.title',
                'edit.mail' => 'comment.mail',
                'edit.url' => 'comment.url',
                'edit.body' => 'comment.body',
                'edit.pass' => 'comment.password',
                'edit.himitu' => 'comment.open_status',
                'edit.delete' => 'comment.delete',
            ];
            $request->combine($pattern);
            if ($request->get('comment.open_status') == 'on') {
                $request->set('comment.open_status', Config::get('COMMENT.OPEN_STATUS.PRIVATE'));
            }
        }

        $comment_id = $request->get('id', $request->get('comment.id'));

        // 編集対象のコメント取得
        $comments_model = new CommentsModel();
        $comment = $comments_model->getEditableComment($comment_id, $blog_id);
        if (empty($comment)) {
            $this->redirect($request, array('action' => 'index', 'blog_id' => $blog_id));
        }

        // 編集対象の親記事
        $entry_id = $comment['entry_id'];
        if (!($entry = (new EntriesModel())->getCommentAcceptedEntry($entry_id, $blog_id))) {
            $this->redirect($request, ['action' => 'view', 'blog_id' => $blog_id, 'id' => $entry_id]);
        }
        $this->set('edit_entry', $entry);

        // 初期表示処理
        if (!$request->get('comment.id') && $request->isGet()) {
            $this->set('edit_comment', $comment);

            // FC2用のテンプレートで表示
            $this->setAreaData(['edit_area']);
            $this->set('sub_title', __("Edit a comment"));
            return $this->getFc2TemplatePath($blog_id);
        }

        // これ以後はPOSTでのみ処理を許可する
        if (!$request->isPost()) return $this->error400();

        // 削除ボタンを押された場合の処理(comment_deleteに処理を移譲)
        // TODO sig
        if ($request->get('comment.delete')) {
            return $this->comment_delete($request);
        }

        // 公開非公開のプルダウン用バリエーション（固定）
        $this->set('open_status_user_list', CommentsModel::getOpenStatusUserList());

        // Captcha画面の初期表示処理
        if ($is_captcha && !$request->isArgs('token')) {
            return "user/entries/edit_comment_form.twig";
        }

        // FC2テンプレート編集時
        if (!$is_captcha) {
            $this->set('edit_comment', $request->get('comment'));
        }

        // コメント投稿処理
        $errors = [];
        $white_list = ['name', 'title', 'mail', 'url', 'body', 'password', 'open_status'];
        $errors['comment'] = $comments_model->editValidate($request->get('comment'), $data, $white_list, $comment);
        $errors['token'] = $is_captcha && CommonController::isValidCaptcha($request) ? "" : __('Token authentication is invalid');
        if (empty($errors['comment']) && empty($errors['token'])) {
            if ($comments_model->updateByIdAndBlogIdAndBlogSetting($request, $data, $comment_id, $blog_id, $blog_setting)) {
                $this->redirect($request, ['action' => 'view', 'blog_id' => $blog_id, 'id' => $entry_id], '#comment' . $comment_id);
            }
        }

        // Captcha使用時のエラー画面
        if ($is_captcha) {
            $this->set('errors', $errors);
            return "user/entries/edit_comment_form.twig";
        }

        // コメント投稿エラー
        $this->fc2CommentError('edit', $errors['comment'], ['open_status' => $data['open_status']]);

        // FC2用のテンプレートで表示
        $this->setAreaData(['edit_area']);
        return $this->getFc2TemplatePath($blog_id);
    }

    /**
     * コメントの削除処理
     * @param Request $request
     * @return string
     */
    public function comment_delete(Request $request): string
    {
        if (!$request->isPost()) return $this->error400();

        $comments_model = new CommentsModel();

        $blog_id = $request->getBlogId();
        $comment_id = $request->get('comment.id');
        $comment = "";
        if (!$comment_id || !($comment = $comments_model->findByIdAndBlogId($comment_id, $blog_id)) || empty($comment['password'])) {
            $this->redirect($request, ['controller' => 'Entries', 'action' => 'index', 'blog_id' => $blog_id]);
        }

        // コメント削除処理
        $errors = [];
        $errors['comment'] = $comments_model->editValidate($request->get('comment'), $data, array('password'), $comment);
        if (empty($errors['comment'])) {
            $comments_model->deleteByIdAndBlogId($comment['id'], $comment['blog_id']);
            $this->redirect($request, ['action' => 'view', 'blog_id' => $comment['blog_id'], 'id' => $comment['entry_id']]);
        }

        $this->set('edit_comment', $request->get('comment'));

        // コメント投稿エラー
        $this->fc2CommentError('edit', $errors['comment'], ['open_status' => $comment['open_status']]);

        // FC2用のテンプレートで表示
        $this->setAreaData(['edit_area']);
        return $this->getFc2TemplatePath($blog_id);
    }

    /**
     * 一覧情報設定
     * @param Request $request
     * @param array $options
     * @param array $areas
     */
    private function setEntriesData(Request $request, array $options = [], array $areas = []): void
    {
        $blog_id = $request->getBlogId();
        $page_num = $request->get('page', 0, Request::VALID_UNSIGNED_INT);

        // 検索条件生成
        $options = $this->getEntriesQueryOptions($blog_id, $options, $page_num);

        // 記事一覧取得
        $this->set('entries', $this->getEntriesArray($blog_id, $options));

        // paging取得
        $this->set('paging', (new EntriesModel())->getPaging($options));

        // area引数がない場合 TOPページ判定
        $this->setAreaData($areas);
    }

    /**
     * @param string $blog_id
     * @param array $override_options
     * @param int $page_num
     * @return array
     */
    private static function getEntriesQueryOptions(
        string $blog_id,
        array $override_options,
        int $page_num
    ): array
    {
        $blog_settings_model = new BlogSettingsModel();
        $blog_setting = $blog_settings_model->findByBlogId($blog_id);
        if ($blog_setting == false) {
            Log::error("couldn't get a blog settings. blog_id: {$blog_id}");
            throw new InvalidArgumentException("couldn't get a blog settings. blog_id: {$blog_id}");
        }

        // ブログのデフォルト設定を取得
        $order = $blog_setting['entry_order'] == Config::get('ENTRY.ORDER.ASC') ? 'ASC' : 'DESC';
        $display_count = $blog_setting['entry_display_count'];

        // ブログデフォルト設定を上書きオプションで上書き
        $options = array_merge([
            'limit' => $display_count,
            'page' => $page_num,
            'order' => 'entries.posted_at ' . $order . ', entries.id ' . $order,
        ], $override_options);

        // 表示項目リスト
        $open_status_list = [
            Config::get('ENTRY.OPEN_STATUS.OPEN'),      // 公開
            Config::get('ENTRY.OPEN_STATUS.PASSWORD'),  // パスワード保護
            Config::get('ENTRY.OPEN_STATUS.LIMIT'),     // 期間限定
        ];
        $options['where'] .= ' AND entries.open_status IN (' . implode(',', $open_status_list) . ')';

        return $options;
    }

    /**
     * @param string $blog_id
     * @param array $options
     * @return array
     */
    private static function getEntriesArray(string $blog_id, array $options): array
    {
        $entries_model = new EntriesModel();
        $entries = $entries_model->find('all', $options);

        // 記事のカテゴリ一覧を取得 TODO:後でcacheを使用する形に
        // 記事のカテゴリーとタグを一括で取得＆振り分け
        $entry_ids = [];
        foreach ($entries as $entry) {
            $entry_ids[] = $entry['id'];
        }
        $categories_model = new CategoriesModel();
        $tags_model = new TagsModel();
        $entries_categories = $categories_model->getEntriesCategories($blog_id, $entry_ids);
        $entries_tags = $tags_model->getEntriesTags($blog_id, $entry_ids);
        foreach ($entries as $key => $entry) {
            $entries[$key]['categories'] = $entries_categories[$entry['id']];
            $entries[$key]['tags'] = $entries_tags[$entry['id']];
        }

        return $entries;
    }

    /**
     * ページの表示可否設定を設定する
     * @param array $allows
     */
    private function setAreaData(array $allows = []): void
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
        foreach ($areas as $area) {
            $this->set($area, in_array($area, $allows));
        }
    }

    /**
     * fc2タグ処理変換済みテンプレートPHPのファイルパスを返す、なければ生成する
     * @param $blog_id
     * @param string|null $html
     * @param string|null $css
     * @param bool $is_preview
     * @return string
     */
    private function getFc2TemplatePath($blog_id, string $html = null, string $css = null, bool $is_preview = false): string
    {
        $device_type = $this->request->deviceType;

        $templateFilePath = BlogTemplatesModel::getTemplateFilePath($blog_id, $device_type, $is_preview);

        // テンプレートファイルが生成されていなければ作成、CSSも同時に生成する（CSSのみの更新はない）
        // is_previewの場合は必ず更新する
        if (!is_file($templateFilePath) || $is_preview) {
            Log::debug_log(__FILE__ . ":" . __LINE__ . " generate Fc2Template. :{$templateFilePath}");

            $blog = BlogService::getById($blog_id);
            $templateId = $blog[Config::get('BLOG_TEMPLATE_COLUMN.' . $device_type)];

            // HTMLとCSSの実行PHPを生成
            BlogTemplatesModel::createTemplate($templateId, $blog_id, $device_type, $html, $css);
        } else {
            Log::debug_log(__FILE__ . ":" . __LINE__ . " found exists Fc2Template. :{$templateFilePath}");
        }

        // CSSのURL
        $this->set('css_link', BlogTemplatesModel::getCssUrl($blog_id, $device_type, $html));
        $this->layout = 'fc2_template.php';

        return $templateFilePath;
    }

    /**
     * FC2用のコメントエラーとデータ設定
     * @param $name
     * @param $errors
     * @param array $data
     */
    private function fc2CommentError($name, $errors, array $data = []): void
    {
        // FC2テンプレートとDB側の違い吸収
        $combine = array('password' => 'pass', 'open_status' => 'himitu');
        foreach ($combine as $key => $value) {
            if (isset($errors[$key]) && $errors[$key]) {
                $errors[$value] = $errors[$key];
                unset($errors[$key]);
            }
            if (isset($data[$key]) && $data[$key]) {
                $data[$value] = $data[$key];
                unset($data[$key]);
            }
        }

        // jsで値の代入とエラーメッセージの表示
        $js = '';
        foreach ($data as $key => $value) {
            $js .= 'setCommentData("' . $name . '[' . $key . ']", "' . $value . '");' . "\n";
        }
        foreach ($errors as $key => $value) {
            $js .= 'insertCommentErrorMessage("' . $name . '[' . $key . ']", "' . $value . '");' . "\n";
        }
        $open_status_private = Config::get('COMMENT.OPEN_STATUS.PRIVATE');
        $comment_error = <<<HTML
          <script>
          function insertCommentErrorMessage(name, message){
            let html = document.createElement('p');
            html.style.cssText = "background-color: #fdd; color: #f00; border-radius: 3px; border: solid 2px #f44;padding: 3px; margin: 5px 3px;";
            html.innerHTML = message;
            let target = document.getElementsByName(name)[0];
            if (!target) {
              return ;
            }
            let parent = target.parentNode;
            parent.insertBefore(html, target.nextSibling);
          }
          function setCommentData(name, value){
            let target = document.getElementsByName(name)[0];
            if (!target) {
              return ;
            }
            if (target.type==='checkbox') {
              if (value==={$open_status_private}) {
                target.checked = 'checked';
              }
            } else {
              target.value = value;
            }
          }
          function displayCommentErrorMessage(){
            {$js}
          }
          if( window.addEventListener ){
              window.addEventListener( 'load', displayCommentErrorMessage, false );
          }else { // noinspection JSUnresolvedVariable
            if( window.attachEvent ){
              window.attachEvent( 'onload', displayCommentErrorMessage );
          }else{
              window.onload = displayCommentErrorMessage;
          } }
          </script>
        HTML;
        $this->set('comment_error', $comment_error);
    }
}

