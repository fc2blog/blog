<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\CategoriesModel;
use Fc2blog\Model\EntriesModel;
use Fc2blog\Model\EntryCategoriesModel;
use Fc2blog\Model\EntryTagsModel;
use Fc2blog\Model\FilesModel;
use Fc2blog\Model\Model;
use Fc2blog\Model\TagsModel;
use Fc2blog\Web\Request;

class EntriesController extends AdminController
{
    const LANG_ELRTE = [
        'ja' => 'jp',
        'en' => 'en',
    ];

    /**
     * 一覧表示
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $entries_model = new EntriesModel();
        $blog_id = $this->getBlogIdFromSession();

        // 検索条件
        $where = 'entries.blog_id=?';
        $params = [$blog_id];
        $from = [];

        // クエリの生成
        if ($keyword = $request->get('keyword')) {
            $keyword = Model::escape_wildcard($keyword);
            $keyword = "%{$keyword}%";
            $where .= ' AND (entries.title LIKE ? OR entries.body LIKE ? OR entries.extend LIKE ?)';
            $params = array_merge($params, [$keyword, $keyword, $keyword]);
        }
        if ($open_status = $request->get('open_status')) {
            $where .= ' AND entries.open_status=?';
            $params[] = $open_status;
        }
        if ($category_id = $request->get('category_id')) {
            $where .= ' AND entry_categories.blog_id=? AND entry_categories.category_id=? AND entries.id=entry_categories.entry_id';
            $params = array_merge($params, [$blog_id, $category_id]);
            $from[] = 'entry_categories';
        }
        if ($tag_id = $request->get('tag_id')) {
            $where .= ' AND entry_tags.blog_id=? AND entry_tags.tag_id=? AND entries.id=entry_tags.entry_id';
            $params = array_merge($params, [$blog_id, $tag_id]);
            $from[] = 'entry_tags';
        }

        // 並び順
        $order = 'entries.posted_at DESC, entries.id DESC';
        switch ($request->get('order')) {
            default:
            case 'posted_at_desc':
                break;
            case 'posted_at_asc':
                $order = 'entries.posted_at ASC, entries.id ASC';
                break;
            case 'title_desc':
                $order = 'entries.title DESC, entries.id DESC';
                break;
            case 'title_asc':
                $order = 'entries.title ASC, entries.id ASC';
                break;
            case 'comment_desc':
                $order = 'entries.comment_count DESC, entries.id DESC';
                break;
            case 'comment_asc':
                $order = 'entries.comment_count ASC, entries.id ASC';
                break;
            case 'body_desc':
                $order = 'entries.body DESC, entries.id DESC';
                break;
            case 'body_asc':
                $order = 'entries.body ASC, entries.id ASC';
                break;
        }

        // オプション設定
        $options = [
            'fields' => 'entries.*',
            'where' => $where,
            'params' => $params,
            'from' => $from,
            'limit' => $request->get('limit', Config::get('ENTRY.DEFAULT_LIMIT'), Request::VALID_POSITIVE_INT),
            'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
            'order' => $order,
        ];

        $entries = $entries_model->find('all', $options);
        $paging = $entries_model->getPaging($options);
        $this->set('entries', $entries);
        $this->set('paging', $paging);

        $this->set('entry_limit_list', Config::get('ENTRY.LIMIT_LIST'));
        $this->set('entry_default_limit', Config::get('ENTRY.DEFAULT_LIMIT'));
        $this->set('page_list', Model::getPageList($paging));

        $categories_model = new CategoriesModel();
        $tags_model = new TagsModel();
        $entries_model = new EntriesModel();
        $this->set('category_list_w', ['' => __('Category name')] + $categories_model->getSearchList($this->getBlogIdFromSession()));
        $this->set('tag_list_w', ['' => _('Tag name')] + $tags_model->getSearchList($this->getBlogIdFromSession()));
        $this->set('open_status_list_w', ['' => __('Public state')] + $entries_model::getOpenStatusList());
        $this->set('open_status_list', $entries_model::getOpenStatusList());
        return "admin/entries/index.twig";
    }

    /**
     * 新規作成
     * @param Request $request
     * @return string
     */
    public function create(Request $request): string
    {
        $entries_model = new EntriesModel();
        $entry_categories_model = new EntryCategoriesModel();
        $entry_tags_model = new EntryTagsModel();
        $tags_model = new TagsModel();
        $categories_model = new CategoriesModel();
        $blog_id = $this->getBlogIdFromSession();

        // data load
        if (is_null($request->get('entry_tags'))) {
            $this->set('entry_tags', $tags_model->getWellUsedTags($blog_id));
        } else {
            $this->set('entry_tags', $request->get('entry_tags'));
        }
        $this->set('well_use_entry_tags', $tags_model->getWellUsedTags($blog_id));
        $this->set('open_status_list', EntriesModel::getOpenStatusList());
        $this->set('open_status_open', Config::get('ENTRY.OPEN_STATUS.OPEN'));
        $this->set('auto_line_feed_list', EntriesModel::getAutoLinefeedList());
        $this->set('auto_line_feed_use', Config::get('ENTRY.AUTO_LINEFEED.USE'));
        $this->set('comment_accepted_list', EntriesModel::getCommentAcceptedList());
        $this->set('comment_accepted_accepted', Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED'));
        $this->set('open_status_password', Config::get('ENTRY.OPEN_STATUS.PASSWORD'));
        $this->set('lang_elrte', self::LANG_ELRTE[App::$lang]);
        $this->set('entry_categories', $request->get('entry_categories', array('category_id' => array())));
        $this->set('categories', $categories_model->getList($blog_id));
        // 以下はSPテンプレ用で追加
        $now = strtotime($request->get('entry.posted_at', ""));
        $now = $now === false ? time() : $now;
        $date_list = explode('/', date('Y/m/d/H/i/s', $now));
        $this->set('entry_date_list', $date_list);
        $this->set('entry_open_status_draft', Config::get('ENTRY.OPEN_STATUS.DRAFT'));
        $this->set('entry_open_status_limit', Config::get('ENTRY.OPEN_STATUS.LIMIT'));
        $this->set('entry_open_status_reservation', Config::get('ENTRY.OPEN_STATUS.RESERVATION'));
        $this->set('domain_user', App::DOMAIN_USER);
        $this->set('user_url', App::userURL($request, ['controller' => 'Entries', 'action' => 'preview', 'blog_id' => $this->getBlogIdFromSession()], false, true));

        // 初期表示時
        if (!$request->get('entry') || !$request->isValidSig()) {
            return "admin/entries/create.twig";
        }

        // 新規登録処理
        if (!$request->isPost()) return $this->error400();
        $errors = [];
        $whitelist_entry = ['title', 'body', 'extend', 'open_status', 'password', 'auto_linefeed', 'comment_accepted', 'posted_at'];
        $errors['entry'] = $entries_model->validate($request->get('entry'), $entry_data, $whitelist_entry);
        $errors['entry_categories'] = $entry_categories_model->validate($request->get('entry_categories', []), $entry_categories_data, ['category_id']);
        if (empty($errors['entry']) && empty($errors['entry_categories'])) {
            $entry_data['blog_id'] = $blog_id;
            if ($id = $entries_model->insert($entry_data)) {
                // カテゴリと紐付
                $entry_categories_model->save($blog_id, $id, $entry_categories_data);
                // タグと紐付
                $entry_tags_model->save($blog_id, $id, $request->get('entry_tags'));
                // 一覧ページへ遷移
                $this->setInfoMessage(__('I created a entry'));
                $this->redirect($request, array('action' => 'index')); // 保存成功
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);
        return "admin/entries/create.twig";
    }

    /**
     * 編集
     * @param Request $request
     * @return string
     */
    public function edit(Request $request): string
    {
        $entries_model = new EntriesModel();
        $entry_categories_model = new EntryCategoriesModel();
        $entry_tags_model = new EntryTagsModel();
        $tags_model = new TagsModel();
        $categories_model = new CategoriesModel();

        $blog_id = $this->getBlogIdFromSession();
        $id = $request->get('id');

        if (!$entry = $entries_model->findByIdAndBlogId($id, $blog_id)) {
            $this->redirect($request, array('action' => 'index'));
        }

        // 編集画面の表示
        if (!$request->get('entry') || !$request->isValidSig()) {
            // data load
            $request->set('entry', $entry);
            $request->set('entry_categories', array('category_id' => $entry_categories_model->getCategoryIds($blog_id, $id)));
            $request->set('entry_tags', $tags_model->getEntryTagNames($blog_id, $id));   // タグの文字列をテーブルから取得

            $this->set('entry_tags', $tags_model->getEntryTagNames($blog_id, $id));
            $this->set('well_use_entry_tags', $tags_model->getWellUsedTags($blog_id));
            $this->set('open_status_list', EntriesModel::getOpenStatusList());
            $this->set('open_status_open', Config::get('ENTRY.OPEN_STATUS.OPEN'));
            $this->set('auto_line_feed_list', EntriesModel::getAutoLinefeedList());
            $this->set('auto_line_feed_use', Config::get('ENTRY.AUTO_LINEFEED.USE'));
            $this->set('comment_accepted_list', EntriesModel::getCommentAcceptedList());
            $this->set('comment_accepted_accepted', Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED'));
            $this->set('open_status_password', Config::get('ENTRY.OPEN_STATUS.PASSWORD'));
            $this->set('lang_elrte', self::LANG_ELRTE[App::$lang]);
            $this->set('entry_categories', $request->get('entry_categories', array('category_id' => array())));
            $this->set('categories', $categories_model->getList($blog_id));
            // 以下はSPテンプレ用で追加
            $this->set('entry', $entry);
            $now = strtotime($request->get('entry.posted_at'));
            $now = $now === false ? time() : $now;
            $date_list = explode('/', date('Y/m/d/H/i/s', $now));
            $this->set('entry_date_list', $date_list);
            $this->set('entry_open_status_draft', Config::get('ENTRY.OPEN_STATUS.DRAFT'));
            $this->set('entry_open_status_limit', Config::get('ENTRY.OPEN_STATUS.LIMIT'));
            $this->set('entry_open_status_reservation', Config::get('ENTRY.OPEN_STATUS.RESERVATION'));
            $this->set('domain_user', App::DOMAIN_USER);
            $this->set('user_url', App::userURL($request, ['controller' => 'Entries', 'action' => 'preview', 'blog_id' => $this->getBlogIdFromSession()], false, true));

            return "admin/entries/edit.twig";
        }

        // 更新処理
        if (!$request->isPost()) return $this->error400();
        $errors = [];
        $whitelist_entry = ['title', 'body', 'extend', 'open_status', 'password', 'auto_linefeed', 'comment_accepted', 'posted_at'];
        $errors['entry'] = $entries_model->validate($request->get('entry'), $entry_data, $whitelist_entry);
        $errors['entry_categories'] = $entry_categories_model->validate($request->get('entry_categories'), $entry_categories_data, ['category_id']);
        if (empty($errors['entry']) && empty($errors['entry_categories'])) {
            if ($entries_model->updateByIdAndBlogId($entry_data, $id, $blog_id)) {
                // カテゴリと紐付
                $entry_categories_model->save($blog_id, $id, $entry_categories_data);
                // タグと紐付
                $entry_tags_model->save($blog_id, $id, $request->get('entry_tags'));
                // 一覧ページへ遷移
                $this->setInfoMessage(__('I have updated the entry'));
                $this->redirect($request, ['action' => 'index']);
            }
        }

        // フォームレンダリング用のデータロード
        $this->set('open_status_list', EntriesModel::getOpenStatusList());
        $this->set('open_status_open', Config::get('ENTRY.OPEN_STATUS.OPEN'));
        $this->set('auto_line_feed_list', EntriesModel::getAutoLinefeedList());
        $this->set('auto_line_feed_use', Config::get('ENTRY.AUTO_LINEFEED.USE'));
        $this->set('comment_accepted_list', EntriesModel::getCommentAcceptedList());
        $this->set('comment_accepted_accepted', Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED'));
        $this->set('open_status_password', Config::get('ENTRY.OPEN_STATUS.PASSWORD'));
        $this->set('lang_elrte', self::LANG_ELRTE[App::$lang]);
        $this->set('well_use_entry_tags', $tags_model->getWellUsedTags($blog_id));
        $this->set('entry_tags', $request->get('entry_tags'));
        $this->set('entry_categories', $request->get('entry_categories', array('category_id' => array())));
        $this->set('categories', $categories_model->getList($blog_id));
        // 以下はSPテンプレ用で追加
        $now = strtotime($request->get('entry.posted_at'));
        $now = $now === false ? time() : $now;
        $date_list = explode('/', date('Y/m/d/H/i/s', $now));
        $this->set('entry_date_list', $date_list);
        $this->set('entry_open_status_draft', Config::get('ENTRY.OPEN_STATUS.DRAFT'));
        $this->set('entry_open_status_limit', Config::get('ENTRY.OPEN_STATUS.LIMIT'));
        $this->set('entry_open_status_reservation', Config::get('ENTRY.OPEN_STATUS.RESERVATION'));
        $this->set('domain_user', App::DOMAIN_USER);
        $this->set('user_url', App::userURL($request, ['controller' => 'Entries', 'action' => 'preview', 'blog_id' => $this->getBlogIdFromSession()], false, true));

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);
        return "admin/entries/edit.twig";
    }

    /**
     * 削除
     * @param Request $request
     */
    public function delete(Request $request)
    {
        if ($request->isValidPost()) {
            // 削除処理
            if (Model::load('Entries')->deleteByIdsAndBlogId($request->get('id'), $this->getBlogIdFromSession()))
                $this->setInfoMessage(__('I removed the entry'));
        }
        $this->redirect($request, array('action' => 'index'));
    }

    /**
     * ajaxでメディアを表示する画面
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function ajax_media_load(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        if ($this->isInvalidAjaxRequest($request)) {
            return $this->error403();
        }

        $files_model = new FilesModel();
        $blog_id = $this->getBlogIdFromSession();

        // 検索条件
        $where = 'blog_id=?';
        $params = [$blog_id];
        if ($request->get('keyword')) {
            $where .= ' AND name like ?';
            $params[] = '%' . $request->get('keyword') . '%';
        }

        $options = [
            'where' => $where,
            'params' => $params,
            'limit' => Config::get('PAGE.FILE.LIMIT', App::getPageLimit($request, 'FILE_AJAX')),
            'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
            'order' => 'id DESC',
        ];
        $files = $files_model->find('all', $options);
        $paging = $files_model->getPaging($options);

        foreach ($files as &$file) {
            $file['path'] = App::getUserFilePath($file);
        }

        $this->set('files', $files);
        $this->set('paging', $paging);

        return 'admin/entries/ajax_media_load.twig';
    }
}
