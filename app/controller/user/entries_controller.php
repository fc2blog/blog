<?php

require_once(Config::get('CONTROLLER_DIR') . 'user/user_controller.php');

class EntriesController extends UserController
{

  /**
  * 記事系統の前処理
  */
  protected function beforeFilter()
  {
    parent::beforeFilter();

    // ブログ情報取得&設定
    $blog_id = $this->getBlogId();
    if (!$blog_id || !$blog=$this->getBlog($blog_id)) {
      $this->redirect(array('controller'=>'Blogs', 'action'=>'index'));
    }
    $this->set('blog', $blog);
    $this->set('blog_setting', Model::load('BlogSettings')->findByBlogId($blog_id));

    // 自身の所持しているブログ判定
    $self_blog = $this->isLoginBlog();
    $this->set('self_blog', $self_blog);

    // 非公開モードの場合はパスワード認証画面へ遷移
    if ($blog['open_status']==Config::get('BLOG.OPEN_STATUS.PRIVATE')
      && !Session::get($this->getBlogPasswordKey($blog['id']))
      && Config::get('ActionName')!='blog_password'
      && !$self_blog
    ) {
      $this->redirect(array('action'=>'blog_password', 'blog_id'=>$blog_id));
    }

    // 予約投稿と期間投稿エントリーの更新処理
    if (Config::get('CRON')===false) {
      $entries_model = Model::load('Entries');
      $entries_model->updateReservation($blog_id);
      $entries_model->updateLimited($blog_id);
    }
  }

  /**
   * 一覧表示
   */
  public function index()
  {
    $request = Request::getInstance();

    // 記事一覧データ設定
    $options = array(
      'where'  => 'blog_id=?',
      'params' => array($this->getBlogId()),
    );
    $pages = $request->get('page') ? array() : array('index_area');
    $this->setEntriesData($options, $pages);
    return $this->fc2template($this->getBlogId());
  }

  /**
  * 検索
  */
  public function search()
  {
    $request = Request::getInstance();

    $where = 'blog_id=?';
    $params = array($this->getBlogId());

    // 検索ワード取得
    if ($keyword=$request->get('q')) {
      $this->set('sub_title', $request->get('q'));
      $keyword = Model::escape_wildcard($keyword);
      $keyword = "%{$keyword}%";
      $where .= ' AND (title LIKE ? OR body LIKE ?)';
      $params = array_merge($params, array($keyword, $keyword));
    }

    $options = array(
      'where'  => $where,
      'params' => $params,
    );
    $this->setEntriesData($options, array('search_area'));
    return $this->fc2template($this->getBlogId());
  }

  /**
  * カテゴリー検索
  */
  public function category()
  {
    $request = Request::getInstance();

    $blog_id     = $this->getBlogId();
    $category_id = $request->get('cat');

    // カテゴリー名取得
    $category = Model::load('Categories')->findByIdAndBlogId($category_id, $blog_id);
    $this->set('sub_title', $category['name']);

    // 記事一覧データ設定
    $where  = 'entries.blog_id=?';
    $where .= ' AND entry_categories.blog_id=?';
    $where .= ' AND entry_categories.category_id=?';
    $where .= ' AND entries.id=entry_categories.entry_id';
    $params = array($blog_id, $blog_id, $category_id);

    $order = $category['category_order'] == Config::get('CATEGORY.ORDER.ASC') ? 'ASC' : 'DESC';

    $options = array(
      'fields' => 'entries.*',
      'where'  => $where,
      'from'   => 'entry_categories',
      'params' => $params,
      'order' => 'entries.posted_at ' . $order . ', entries.id ' . $order,
    );
    $this->setEntriesData($options, array('category_area'));
    return $this->fc2template($this->getBlogId());
  }

  /**
  * タグ検索
  */
  public function tag()
  {
    $request = Request::getInstance();

    // タグ検索
    $blog_id = $this->getBlogId();
    $tag_name = $request->get('tag');

    $tag = Model::load('Tags')->findByNameAndBlogId($tag_name, $blog_id);
    $tag_id = empty($tag) ? 0 : $tag['id'];

    $this->set('sub_title', $tag_name);

    // 記事一覧データ設定
    $where  = 'entries.blog_id=?';
    $where .= ' AND entry_tags.blog_id=?';
    $where .= ' AND entry_tags.tag_id=?';
    $where .= ' AND entries.id=entry_tags.entry_id';
    $params = array($blog_id, $blog_id, $tag_id);

    $options = array(
      'fields' => 'entries.*',
      'where'  => $where,
      'from'   => 'entry_tags',
      'params' => $params,
    );
    $this->setEntriesData($options, array('tag_area'));
    return $this->fc2template($this->getBlogId());
  }

  /**
  * 年別,月別,日別表示
  */
  public function date()
  {
    $request = Request::getInstance();

    // 開始日付と終了日付の計算
    preg_match('/^([0-9]{4})([0-9]{2})?([0-9]{2})?$/', $request->get('date'), $matches);
    $dates = $matches + array('', date('Y'), 0, 0);
    list($start, $end) = App::calcStartAndEndDate($dates[1], $dates[2], $dates[3]);

    // 記事一覧データ設定
    $where = 'blog_id=? AND ?<=posted_at AND posted_at<=?';
    $params = array($this->getBlogId(), $start, $end);

    $options = array(
      'where'  => $where,
      'params' => $params,
    );
    $this->setEntriesData($options, array('date_area'));
    $this->set('now_date', date('Y-m-d', strtotime($start)));
    return $this->fc2template($this->getBlogId());
  }

  /**
  * アーカイブ表示
  */
  public function archive()
  {
    // 記事一覧データ設定
    $options = array(
      'fields' => array(
        'id', 'blog_id', 'title', 'posted_at', 'comment_count',
        Config::get('ENTRY.AUTO_LINEFEED.NONE') . ' as auto_linefeed',
        'SUBSTRING(body, 1, 20) as body'
      ),
      'where'  => 'blog_id=?',
      'params' => array($this->getBlogId()),
    );
    $this->setEntriesData($options, array('titlelist_area'));
    return $this->fc2template($this->getBlogId());
  }

  /**
   * プレビュー表示
   */
  public function preview()
  {
    // XSS-Protection無効
    header("X-XSS-Protection: 0");

    // preview処理用
    $request = Request::getInstance();
    $blog_id = $this->getBlogId();

    // 投稿者のブログIDチェック
    if ($blog_id!=$this->getAdminBlogId() && !Model::load('Blogs')->isUserHaveBlogId($this->getAdminUserId(), $blog_id)) {
      return $this->error404();
    }

    // 記事のプレビュー
    if ($request->get('entry')) {
      return $this->preview_entry();
    }

    // FC2テンプレートのプレビュー
    if ($request->get('fc2_id') && $request->get('device_type')) {
      return $this->preview_fc2_template();
    }

    // テンプレートのプレビュー
    if ($request->get('blog_template') || $request->get('template_id')) {
      return $this->preview_template();
    }

    // プラグインのプレビュー
    if ($request->get('blog_plugin') || $request->get('plugin_id')) {
      return $this->preview_plugin();
    }

    // 当てはまらない場合は404画面を表示
    return $this->error404();
  }

  /**
  * FC2テンプレート用のプレビュー
  */
  private function preview_fc2_template()
  {
    $request = Request::getInstance();
    $blog_id = $this->getBlogId();

    // 記事一覧データ設定
    $options = array(
      'where'  => 'blog_id=?',
      'params' => array($this->getBlogId()),
    );
    $pages = $request->get('page') ? array() : array('index_area');
    $this->setEntriesData($options, $pages);

    // テンプレートのプレビュー
    $device_key = Config::get('DEVICE_FC2_KEY.' . $request->get('device_type'));
    $template = Model::load('Fc2Templates')->findByIdAndDevice($request->get('fc2_id'), $device_key);
    if (empty($template)) {
      return $this->error404();
    }

    $html = $template['html'];
    $css  = $template['css'];

    // テンプレートのシンタックスチェック
    Model::load('BlogTemplates');
    $syntax = BlogTemplatesModel::fc2TemplateSyntax($html);
    if ($syntax !== true) {
      return 'Entries/syntax.html';
    }

    // FC2用のテンプレートで表示
    $preview_path = BlogTemplatesModel::getTemplateFilePath($blog_id, $request->get('device_type'), $html);
    is_file($preview_path) && unlink($preview_path);
    return $this->fc2template($blog_id, $html, $css);
  }

  /**
  * テンプレート用のプレビュー
  */
  private function preview_template()
  {
    $request = Request::getInstance();
    $blog_id = $this->getBlogId();

    // 記事一覧データ設定
    $options = array(
      'where'  => 'blog_id=?',
      'params' => array($this->getBlogId()),
    );
    $pages = $request->get('page') ? array() : array('index_area');
    $this->setEntriesData($options, $pages);

    // テンプレートのプレビュー
    $html = $css = null;
    if ($request->get('template_id')) {
      $blog_template = Model::load('BlogTemplates')->findByIdAndBlogId($request->get('template_id'), $blog_id);
      $html = $blog_template['html'];
      $css = $blog_template['css'];
    } else {
      $html = $request->get('blog_template.html');
      $css  = $request->get('blog_template.css');
    }

    // テンプレートのシンタックスチェック
    Model::load('BlogTemplates');
    $syntax = BlogTemplatesModel::fc2TemplateSyntax($html);
    if ($syntax !== true) {
      return 'Entries/syntax.html';
    }

    // FC2用のテンプレートで表示
    $device_type = $this->getDeviceType();
    $preview_path = BlogTemplatesModel::getTemplateFilePath($blog_id, $device_type, $html);
    is_file($preview_path) && unlink($preview_path);
    return $this->fc2template($blog_id, $html, $css);
  }

  /**
  * プラグイン用のプレビュー
  */
  private function preview_plugin()
  {
    $request = Request::getInstance();
    $blog_id = $this->getBlogId();

    // プラグインのプレビュー情報取得
    $preview_plugin = null;
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
    Model::load('BlogPlugins');
    $syntax = BlogPluginsModel::fc2PluginSyntax($contents);
    if ($syntax !== true) {
      return 'Entries/syntax.html';
    }

    // プラグインのPHPファイル作成
    BlogPluginsModel::createPlugin($contents, $blog_id);

    // 入力データからデータを作成
    $category = $preview_plugin['category'];
    $device_type = $preview_plugin['device_type'];
    $plugin = array(
      'id'             => 'preview',
      'blog_id'        => $blog_id,
      'title'          => $preview_plugin['title'],
      'title_align'    => $preview_plugin['title_align'],
      'title_color'    => $preview_plugin['title_color'],
      'list'           => $preview_plugin['list'],
      'contents'       => $preview_plugin['contents'],
      'contents_align' => $preview_plugin['contents_align'],
      'contents_color' => $preview_plugin['contents_color'],
      'attribute'      => $preview_plugin['attribute'],
      'device_type'    => $device_type,
      'category'       => $category,
      'created_at'     => date('Y-m-d H:i:s'),
      'updated_at'     => date('Y-m-d H:i:s'),
    );

    // スマフォ版のプラグインのプレビュー表示
    if ($device_type==Config::get('DEVICE_SP')) {
      $this->set('s_plugin', $plugin);
      $this->setPageData(array('spplugin_area'));
      return $this->fc2template($blog_id);
    }

    // 記事一覧データ設定(スマフォ版以外のプレビュー表示)
    $options = array(
      'where'  => 'blog_id=?',
      'params' => array($this->getBlogId()),
    );
    $pages = $request->get('page') ? array() : array('index_area');
    $this->setEntriesData($options, $pages);

    // 通常のプラグインリストに追加する
    $plugins = Model::load('BlogPlugins')->findByDeviceTypeAndCategory($this->getDeviceType(), $category, $blog_id);
    $id = $request->get('id');
    if (empty($id)) {
      // 新規プラグインは最後尾に追加する
      $plugins[] = $plugin;
    } else {
      // 編集の場合は上書きする
      foreach ($plugins as $key => $value) {
        if ($value['id']==$id) {
          $plugins[$key] = $plugin;
        }
      }
    }
    $this->set('t_plugins_' . $category, $plugins);

    // FC2用のテンプレートで表示
    return $this->fc2template($blog_id);
  }

  /**
  * 記事用のプレビュー
  */
  private function preview_entry()
  {
    $request = Request::getInstance();
    $blog_id = $this->getBlogId();

    // DBの代わりにリクエストから取得
    $entry = array(
      'id'            => 0,
      'blog_id'       => $blog_id,
      'title'         => $request->get('entry.title'),
      'body'          => $request->get('entry.body'),
      'extend'        => $request->get('entry.extend'),
      'posted_at'     => $request->get('entry.posted_at', date('Y-m-d H:i:s')),
      'auto_linefeed' => $request->get('entry.auto_linefeed'),
      'open_status'   => Config::get('ENTRY.OPEN_STATUS.OPEN'),
      'created_at'    => date('Y-m-d H:i:s'),
      'updated_at'    => date('Y-m-d H:i:s'),
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
          'id'      => 0,
          'blog_id' => $blog_id,
          'name'    => $tag,
          'count'   => 0,
        );
      }
    }
    $this->set('entry', $entry);
    $this->set('comments', array());

    $this->set('sub_title', $entry['title']);

    // FC2用のテンプレートで表示
    $areas = array('permanent_area');
    if (App::isPC()) {
      $areas[] = 'comment_area';
    }
    $this->setPageData($areas);
    return $this->fc2template($entry['blog_id']);
  }

  /**
   * 詳細表示
   */
  public function view()
  {
    $request = Request::getInstance();
    $entries_model = Model::load('Entries');

    $blog_id = $this->getBlogId();
    $id = $request->get('id');
    $comment_view = $request->get('m2');    // スマフォ用のコメント投稿＆閲覧判定

    // 記事詳細取得
    $entry = $entries_model->getEntry($id, $blog_id);
    if (!$entry) {
      return $this->error404();
    }
    $this->set('entry', $entry);
    $this->set('sub_title', $entry['title']);

    $self_blog = $this->isLoginBlog();

    // スマフォのコメント投稿、閲覧分岐処理
    switch ($comment_view) {
      // コメント一覧表示(スマフォ)
      case 'res':
        // ブログの設定情報取得
        $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);

        // 記事のコメント取得(パスワード制限時はコメントを取得しない)
        if ($self_blog || $entry['open_status']!=Config::get('ENTRY.OPEN_STATUS.PASSWORD') || Session::get($this->getEntryPasswordKey($entry['blog_id'], $entry['id']))) {
          // コメント一覧を取得(ページング用)
          $comments_model = Model::load('Comments');
          $options = $comments_model->getCommentListOptionsByBlogSetting($blog_id, $id, $blog_setting);
          $options['page'] = $request->get('page', 0, Request::VALID_UNSIGNED_INT);
          $comments = $comments_model->find('all', $options);
          $this->set('comments', $comments_model->decorateByBlogSetting($comments, $blog_setting, $self_blog));
          $this->set('paging', $comments_model->getPaging($options));
        }

        // FC2用のテンプレートで表示
        $this->setPageData(array('comment_area'));
        return $this->fc2template($entry['blog_id']);
        break;

      // コメント投稿表示(スマフォ)
      case 'form':
        // FC2用のテンプレートで表示
        $this->setPageData(array('form_area'));
        return $this->fc2template($entry['blog_id']);
        break;

      // 上記以外は通常の詳細表示として扱う
      default: break;
    }

    // 表示画面判定
    $areas = array('permanent_area');

    // ブログの設定情報取得
    $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);

    // 記事のコメント取得(パスワード制限時はコメントを取得しない)
    if ($self_blog || $entry['open_status']!=Config::get('ENTRY.OPEN_STATUS.PASSWORD') || Session::get($this->getEntryPasswordKey($entry['blog_id'], $entry['id']))) {
      if (App::isPC()) {
        $areas[] = 'comment_area';
        $this->set('comments', Model::load('Comments')->getCommentListByBlogSetting($blog_id, $id, $blog_setting, $self_blog));
      }
    }

    // 前後の記事取得
    $is_asc = $blog_setting['entry_order'] == Config::get('ENTRY.ORDER.ASC');
    $this->set('next_entry', $is_asc ? $entries_model->nextEntry($entry) : $entries_model->prevEntry($entry));
    $this->set('prev_entry', $is_asc ? $entries_model->prevEntry($entry) : $entries_model->nextEntry($entry));

    // FC2用のテンプレートで表示
    $this->setPageData($areas);
    return $this->fc2template($entry['blog_id']);
  }

  /**
  * プラグインページの表示
  */
  public function plugin()
  {
    $request = Request::getInstance();

    $blog_id = $this->getBlogId();
    $id = $request->get('id');

    // プラグイン取得
    $plugin = Model::load('BlogPlugins')->findByIdAndBlogId($id, $blog_id);
    $this->set('s_plugin', $plugin);

    // FC2用のテンプレートで表示
    $this->setPageData(array('spplugin_area'));
    return $this->fc2template($blog_id);
  }

  /**
  * 記事のパスワード認証
  */
  public function password()
  {
    $request = Request::getInstance();

    $blog_id = $this->getBlogId();
    $id = $request->get('id');

    // 記事詳細取得
    $entry = Model::load('Entries')->findByIdAndBlogId($id, $blog_id);
    if (!$entry) {
      $this->redirect(array('action'=>'index', 'blog_id'=>$blog_id));
    }

    // パスワード入力チェック
    if ($entry['password']==='') {
      // パスワード未設定の場合は全体のパスワードを設定
      $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);
      $entry['password'] = $blog_setting['entry_password'];
    }
    if ($entry['password']===$request->get('password', '')) {
      // パスワードが合致すればセッションに記録
      Session::set($this->getEntryPasswordKey($entry['blog_id'], $entry['id']), true);
    }

    $this->redirect(array('action'=>'view', 'blog_id'=>$blog_id, 'id'=>$id));
  }

  /**
   * ブログのパスワード認証
   */
  public function blog_password()
  {
    $request = Request::getInstance();

    $blog_id = $this->getBlogId();
    $blog = $this->getBlog($blog_id);

    if ($blog['open_status']!=Config::get('BLOG.OPEN_STATUS.PRIVATE') || Session::get($this->getBlogPasswordKey($blog['id'])) || $this->isLoginBlog()) {
      $this->redirect(array('action'=>'index', 'blog_id'=>$blog_id));
    }

    if ($request->get('blog')) {
      if ($request->get('blog.password')==$blog['blog_password']) {
        Session::set($this->getBlogPasswordKey($blog['id']), true);
        $this->redirect(array('action'=>'index', 'blog_id'=>$blog_id));
      }
      $this->set('errors', array('password'=>__('The password is incorrect!')));
    }

    $this->set('blog', $blog);
  }

  /**
   * コメント投稿
   */
  public function comment_regist()
  {
    $blog_id  = $this->getBlogId();

    // ブログの設定情報取得(captchaの使用可否で画面切り替え)
    $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);
    $is_captcha = $blog_setting['comment_captcha']==Config::get('COMMENT.COMMENT_CAPTCHA.USE');

    // FC2テンプレートにリクエスト情報を合わせる
    $request = Request::getInstance();
    if (!$is_captcha || !$request->isArgs('token')) {
      Config::read('fc2_request.php');
      $request->combine(Config::get('request_combine.comment_register'));    // 引数のキーを入れ替える
      if ($request->get('comment.open_status')=='on') {
        $request->set('comment.open_status', Config::get('COMMENT.OPEN_STATUS.PRIVATE'));
      }
    }

    $entry_id = $request->get('comment.entry_id');

    // 記事詳細取得
    $entry = Model::load('Entries')->getCommentAcceptedEntry($entry_id, $blog_id);
    if (!$entry) {
      $this->redirect(array('action'=>'view', 'blog_id'=>$blog_id, 'id'=>$entry_id));
    }

    // CAPTCHA用に確認画面を挟む
    if ($is_captcha && !$request->isArgs('token')) {
      return ;
    }

    // 記事のカテゴリ一覧を取得 TODO:後でcacheを使用する形に
    $entry['categories'] = Model::load('Categories')->getEntryCategories($blog_id, $entry_id);
    $entry['tags'] = Model::load('Tags')->getEntryTags($blog_id, $entry_id);
    $this->set('entry', $entry);

    // 入力チェック処理
    $comments_model = Model::load('Comments');
    $errors = array();
    $white_list = array('entry_id', 'name', 'title', 'mail', 'url', 'body', 'password', 'open_status');
    $errors['comment'] = $comments_model->registerValidate($request->get('comment'), $data, $white_list);
    $errors['token'] = $is_captcha ? $this->tokenValidate() : array();   // Token用のバリデート
    if (empty($errors['comment']) && empty($errors['token'])) {
      $data['blog_id'] = $blog_id;  // ブログIDの設定
      if ($id=$comments_model->insertByBlogSetting($data, $blog_setting)) {
        $this->redirect(array('action'=>'view', 'blog_id'=>$blog_id, 'id'=>$entry_id), '#comment' . $id);
      }
    }

    // Captcha使用時のエラー画面
    if ($is_captcha) {
      $this->set('errors', $errors);
      return ;
    }

    // コメント投稿エラー
    $this->fc2CommentError('comment', $errors['comment'], $data);

    // FC2用のテンプレートで表示
    $this->setPageData(array(App::isPC() ? 'comment_area' : 'form_area'));
    return $this->fc2template($entry['blog_id']);
  }

  /**
  * コメント編集画面
  */
  public function comment_edit()
  {
    $blog_id = $this->getBlogId();

    // ブログの設定情報を取得
    $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);
    $is_captcha = $blog_setting['comment_captcha']==Config::get('COMMENT.COMMENT_CAPTCHA.USE');

    // FC2テンプレートの引数を受け側で合わせる
    $request = Request::getInstance();
    if (!$is_captcha || !$request->isArgs('token')) {
      Config::read('fc2_request.php');
      $request->combine(Config::get('request_combine.comment_edit'));
      if ($request->get('comment.open_status')=='on') {
        $request->set('comment.open_status', Config::get('COMMENT.OPEN_STATUS.PRIVATE'));
      }
    }

    $comment_id = $request->get('id', $request->get('comment.id'));

    // 編集対象のコメント取得
    $comments_model = Model::load('Comments');
    $comment = $comments_model->getEditableComment($comment_id, $blog_id);
    if (empty($comment)) {
      $this->redirect(array('action'=>'index', 'blog_id'=>$blog_id));
    }

    // 編集対象の親記事
    $entry_id = $comment['entry_id'];
    if (!($entry=Model::load('Entries')->getCommentAcceptedEntry($entry_id, $blog_id))) {
      $this->redirect(array('action'=>'view', 'blog_id'=>$blog_id, 'id'=>$entry_id));
    }
    $this->set('edit_entry', $entry);

    // 初期表示処理
    if(!$request->get('comment.id')){
      $this->set('edit_comment', $comment);

      // FC2用のテンプレートで表示
      $this->setPageData(array('edit_area'));
      return $this->fc2template($blog_id);
    }

    // Captcha画面の初期表示処理
    if ($is_captcha && !$request->isArgs('token')) {
      return ;
    }

    // FC2テンプレート編集時
    if (!$is_captcha) {
      $this->set('edit_comment', $request->get('comment'));
    }

    // 削除ボタンを押された場合の処理
    if ($request->get('comment.delete')) {
      return $this->comment_delete();
    }

    // コメント投稿処理
    $errors = array();
    $white_list = array('name', 'title', 'mail', 'url', 'body', 'password', 'open_status');
    $errors['comment'] = $comments_model->editValidate($request->get('comment'), $data, $white_list, $comment);
    $errors['token'] = $is_captcha ? $this->tokenValidate() : array();   // Token用のバリデート
    if (empty($errors['comment']) && empty($errors['token'])) {
      if ($comments_model->updateByIdAndBlogIdAndBlogSetting($data, $comment_id, $blog_id, $blog_setting)) {
        $this->redirect(array('action'=>'view', 'blog_id'=>$blog_id, 'id'=>$entry_id), '#comment' . $comment_id);
      }
    }

    // Captcha使用時のエラー画面
    if ($is_captcha) {
      $this->set('errors', $errors);
      return ;
    }

    // コメント投稿エラー
    $this->fc2CommentError('edit', $errors['comment'], array('open_status'=>$data['open_status']));

    // FC2用のテンプレートで表示
    $this->setPageData(array('edit_area'));
    return $this->fc2template($blog_id);
  }

  /**
  * コメントの削除処理
  */
  public function comment_delete()
  {
    $request = Request::getInstance();
    $comments_model = Model::load('Comments');

    $blog_id = $this->getBlogId();
    $comment_id = $request->get('comment.id');
    if (!$comment_id || !($comment=$comments_model->findByIdAndBlogId($comment_id, $blog_id)) || empty($comment['password'])) {
      $this->redirect(array('controller'=>'Entries', 'action'=>'index', 'blog_id'=>$blog_id));
    }

    // コメント削除処理
    $errors = array();
    $errors['comment'] = $comments_model->editValidate($request->get('comment'), $data, array('password'), $comment);
    if (empty($errors['comment'])) {
      $comments_model->deleteByIdAndBlogId($comment['id'], $comment['blog_id']);
      $this->redirect(array('action'=>'view', 'blog_id'=>$comment['blog_id'], 'id'=>$comment['entry_id']));
    }

    // コメント投稿エラー
    $this->fc2CommentError('edit', $errors['comment'], array('open_status'=>$comment['open_status']));

    // FC2用のテンプレートで表示
    $this->setPageData(array('edit_area'));
    return $this->fc2template($blog_id);
  }

  /**
  * 一覧情報設定
  */
  private function setEntriesData($options=array(), $areas=array())
  {
    $request = Request::getInstance();
    $entries_model = Model::load('Entries');

    $blog_id = $this->getBlogId();

    $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);
    $order = $blog_setting['entry_order'] == Config::get('ENTRY.ORDER.ASC') ? 'ASC' : 'DESC';

    $options = array_merge(array(
      'limit' => $blog_setting['entry_display_count'],
      'page'  => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
      'order' => 'entries.posted_at ' . $order . ', entries.id ' . $order,
    ), $options);

    // 表示項目リスト
    $open_status_list = array(
      Config::get('ENTRY.OPEN_STATUS.OPEN'),      // 公開
      Config::get('ENTRY.OPEN_STATUS.PASSWORD'),  // パスワード保護
      Config::get('ENTRY.OPEN_STATUS.LIMIT'),     // 期間限定
    );
    $options['where'] .= ' AND entries.open_status IN (' . implode(',', $open_status_list) . ')';

    // 記事一覧取得
    $entries = $entries_model->find('all', $options);
    $paging = $entries_model->getPaging($options);

    // 記事のカテゴリ一覧を取得 TODO:後でcacheを使用する形に
    $categories_model = Model::load('Categories');
    $tags_model = Model::load('Tags');

    // 記事のカテゴリーとタグを一括で取得＆振り分け
    $entry_ids = array();
    foreach($entries as $key => $entry){
      $entry_ids[] = $entry['id'];
    }
    $entries_categories = $categories_model->getEntriesCategories($blog_id, $entry_ids);
    $entries_tags = $tags_model->getEntriesTags($blog_id, $entry_ids);
    foreach($entries as $key => $entry){
      $entries[$key]['categories'] = $entries_categories[$entry['id']];
      $entries[$key]['tags'] = $entries_tags[$entry['id']];
    }

    $this->set('entries', $entries);
    $this->set('paging', $paging);

    // page引数がない場合 TOPページ判定
    $this->setPageData($areas);
  }

  /**
  * ページの表示可否設定を設定する
  */
  private function setPageData($allows=array())
  {
    $areas = array(
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
    );
    foreach ($areas as $area) {
      $this->set($area, in_array($area, $allows));
    }
  }

  /**
  * FC2用のテンプレートで表示処理を行う
  */
  private function fc2template($blog_id, $html=null, $css=null)
  {
    $device_type = $this->getDeviceType();

    Model::load('BlogTemplates');
    $templateFilePath = BlogTemplatesModel::getTemplateFilePath($blog_id, $device_type, $html);
    Debug::log('Blog Template[' . $templateFilePath . ']', false, 'log', __FILE__, __LINE__);

    if (!is_file($templateFilePath)) {
      // テンプレートファイルが生成されていなければ作成(CSSも同時に)
      Debug::log('Template does not exist! Create', false, 'log', __FILE__, __LINE__);

      $blog = $this->getBlog($blog_id);
      $templateId = $blog[Config::get('BLOG_TEMPLATE_COLUMN.' . $device_type)];
      BlogTemplatesModel::createTemplate($templateId, $blog_id, $device_type, $html, $css);

      Debug::log('Template generation completion', false, 'log', __FILE__, __LINE__);
    }

    // CSSのURL
    $this->set('css_link', BlogTemplatesModel::getCssUrl($blog_id, $device_type, $html));

    $this->layout = 'fc2_template.html';
    return $templateFilePath;
  }

  /**
  * FC2用のコメントエラーとデータ設定
  */
  private function fc2CommentError($name, $errors, $data=array())
  {
    // FC2テンプレートとDB側の違い吸収
    $conbine = array('password'=>'pass', 'open_status'=>'himitu');
    foreach ($conbine as $key => $value) {
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
  var html = document.createElement('p');
  html.style.cssText = "background-color: #fdd; color: #f00; border-radius: 3px; border: solid 2px #f44;padding: 3px; margin: 5px 3px;";
  html.innerHTML = message;
  var target = document.getElementsByName(name)[0];
  if (!target) {
    return ;
  }
  var parent = target.parentNode;
  parent.insertBefore(html, target.nextSibling);
}
function setCommentData(name, value){
  var target = document.getElementsByName(name)[0];
  if (!target) {
    return ;
  }
  if (target.type=='checkbox') {
    if (value==$open_status_private) {
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
}else if( window.attachEvent ){
    window.attachEvent( 'onload', displayCommentErrorMessage );
}else{
    window.onload = displayCommentErrorMessage;
}
</script>
HTML;
    $this->set('comment_error', $comment_error);
  }

}

