<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\BlogSettingsModel;
use Fc2blog\Model\CommentsModel;
use Fc2blog\Model\Model;
use Fc2blog\Web\Request;

class CommentsController extends AdminController
{

    /**
     * 一覧表示
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        $comments_model = new CommentsModel();
        $blog_id = $this->getBlogId($request);

        // 検索条件
        $where = 'comments.blog_id=?';
        $params = array($blog_id);

        if ($keyword = $request->get('keyword')) {
            $keyword = Model::escape_wildcard($keyword);
            $keyword = "%{$keyword}%";
            $where .= ' AND (comments.title LIKE ? OR comments.body LIKE ? OR comments.name LIKE ?)';
            $params = array_merge($params, array($keyword, $keyword, $keyword));
        }
        if (($open_status = $request->get('open_status')) !== null) {
            $where .= ' AND comments.open_status=?';
            $params[] = $open_status;
        }
        if ($reply_status = $request->get('reply_status')) {
            $where .= ' AND comments.reply_status=?';
            $params[] = $reply_status;
        }
        if ($entry_id = $request->get('entry_id')) {
            $where .= ' AND comments.entry_id=?';
            $params[] = $entry_id;
        }

        // 記事の結合条件追加
        $where .= ' AND entries.blog_id=? AND comments.entry_id=entries.id';
        $params[] = $blog_id;

        // 並び順
        $order = 'comments.created_at DESC, id DESC';
        switch ($request->get('order')) {
            default:
            case 'created_at_desc':
                break;
            case 'created_at_asc':
                $order = 'comments.created_at ASC, comments.id ASC';
                break;
            case 'name_desc':
                $order = 'comments.name DESC, comments.id DESC';
                break;
            case 'name_asc':
                $order = 'comments.name ASC, comments.id ASC';
                break;
            case 'body_desc':
                $order = 'comments.body DESC, comments.id DESC';
                break;
            case 'body_asc':
                $order = 'comments.body ASC, comments.id ASC';
                break;
            case 'entry_id_desc':
                $order = 'comments.entry_id DESC, comments.id DESC';
                break;
            case 'entry_id_asc':
                $order = 'comments.entry_id ASC, comments.id ASC';
                break;
        }

        $options = array(
            'fields' => array('comments.*', 'entries.title as entry_title'),
            'from' => 'entries',
            'where' => $where,
            'params' => $params,
            'limit' => $request->get('limit', Config::get('ENTRY.DEFAULT_LIMIT'), Request::VALID_POSITIVE_INT),
            'page' => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
            'order' => $order,
        );

        if ($options['limit'] > max(array_keys(Config::get('ENTRY.LIMIT_LIST')))) {
            $options['limit'] = Config::get('ENTRY.DEFAULT_LIMIT');
        }
        if (ceil(PHP_INT_MAX / $options['limit']) <= $options['page']) {
            $options['page'] = 0;
        }

        $comments = $comments_model->find('all', $options);
        $paging = $comments_model->getPaging($options);

        foreach ($comments as &$comment) {
            $comment['entry_url'] = App::userURL($request, ['controller' => 'Entries', 'action' => 'view', 'blog_id' => $comment['blog_id'], 'id' => $comment['entry_id']], false, true);
        }

        $this->set('comments', $comments);
        $this->set('paging', $paging);

        $this->set('open_status_w', ['' => __('Public state')] + CommentsModel::getOpenStatusList());
        $this->set('entry_limit_list', Config::get('ENTRY.LIMIT_LIST'));
        $this->set('entry_default_limit', Config::get('ENTRY.DEFAULT_LIMIT'));
        $this->set('page_list', Model::getPageList($paging));
        $this->set('reply_status_w', ['' => __('Reply state')] + CommentsModel::getReplyStatusList());
        $this->set('limit', Config::get('ENTRY.DEFAULT_LIMIT'));
        $this->set('reply_status_list', CommentsModel::getReplyStatusList());

        $this->setStatusDataList();

        return 'admin/comments/index.twig';
    }

    public function setStatusDataList(): void
    {
        $this->set('comment_open_status_public', Config::get('COMMENT.OPEN_STATUS.PUBLIC'));
        $this->set('comment_open_status_pending', Config::get('COMMENT.OPEN_STATUS.PENDING'));
        $this->set('comment_open_status_private', Config::get('COMMENT.OPEN_STATUS.PRIVATE'));
        $this->set('comment_reply_status_unread', Config::get('COMMENT.REPLY_STATUS.UNREAD'));
        $this->set('comment_reply_status_read', Config::get('COMMENT.REPLY_STATUS.READ'));
        $this->set('comment_reply_status_reply', Config::get('COMMENT.REPLY_STATUS.REPLY'));
    }

    /**
     * コメントの承認
     * @param Request $request
     */
    public function approval(Request $request)
    {
        $comments_model = Model::load('Comments');

        $id = $request->get('id');
        $blog_id = $this->getBlogId($request);

        // 承認データの取得
        if (!$comment = $comments_model->findByIdAndBlogId($id, $blog_id)) {
            $this->redirect($request, array('action' => 'index'));
        }

        if ($comment['open_status'] != Config::get('COMMENT.OPEN_STATUS.PENDING')) {
            // 承認待ち以外はリダイレクト
            $this->redirect($request, array('action' => 'index'));
        }

        // 承認処理
        $comments_model->updateApproval($blog_id, $id);
        $this->setInfoMessage(__('I approved a comment'));

        // 元の画面へ戻る
        $back_url = $request->get('back_url');
        if (!empty($back_url)) {
            $this->redirect($request, $back_url);
        }
        $this->redirect($request, array('action' => 'index'));
    }

    /**
     * コメントの承認(ajax版)
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function ajax_approval(Request $request): string
    {
        if ($this->isInvalidAjaxRequest($request) || $request->method !== 'POST' || !$request->isValidSig()) {
            return $this->error403();
        }

        $comments_model = Model::load('Comments');

        $id = $request->get('id');
        $blog_id = $this->getBlogId($request);

        // 承認データの取得
        if (!$comment = $comments_model->findByIdAndBlogId($id, $blog_id)) {
            $this->set('json', ['error' => __('Comments subject to approval does not exist')]);
            // error だが、JS側でsuccessプロパティ存在をみて判定しているので、 status codeは200を返す
            $this->setContentType("application/json; charset=utf-8");
            return "admin/common/json.twig";
        }

        // すでに許可済み
        if ($comment['open_status'] != Config::get('COMMENT.OPEN_STATUS.PENDING')) {
            $this->set('json', ['success' => 1]);
            $this->setContentType("application/json; charset=utf-8");
            return "admin/common/json.twig";
        }

        // 承認処理
        $comments_model->updateApproval($blog_id, $id);
        $this->set('json', ['success' => 1]);
        $this->setContentType("application/json; charset=utf-8");
        return "admin/common/json.twig";
    }

    /**
     * 返信
     * @param Request $request
     * @return string
     * @noinspection PhpMissingReturnTypeInspection
     */
    public function reply(Request $request)
    {
        $comments_model = new CommentsModel();

        $comment_id = $request->get('id');
        $blog_id = $this->getBlogId($request);
        $this->setStatusDataList();

        // 返信用のコメント取得
        $comment = $comments_model->getReplyComment($blog_id, $comment_id);
        if (!$comment) {
            return "admin/common/error404.twig";
        }
        $this->set('comment', $comment);

        // コメントの初期表示時入力データ設定
        if (!$request->get('comment')) {
            $blog_setting_model = new BlogSettingsModel();
            $blog_setting = $blog_setting_model->findByBlogId($blog_id);
            if ($comment['reply_status'] != Config::get('COMMENT.REPLY_STATUS.REPLY') && $blog_setting['comment_quote'] == Config::get('COMMENT.QUOTE.USE')) {
                $comment['reply_body'] = '> ' . str_replace("\n", "\n> ", $comment['body']) . "\n";
            }
            $request->set('comment', $comment);
            $back_url = $request->getReferer();
            if (!empty($back_url)) {
                $request->set('back_url', $request->getReferer());    // 戻る用のURL
            }
            return 'admin/comments/reply.twig';
        }

        // コメント投稿処理
        $errors = [];
        $errors['comment'] = $comments_model->replyValidate($request->get('comment'), $data, array('reply_body'));
        if (empty($errors['comment'])) {
            if ($comments_model->updateReply($data, $comment)) {
                $this->setInfoMessage(__('I did reply to comment '));

                // 元の画面へ戻る
                $back_url = $request->get('back_url');
                if (!empty($back_url)) {
                    $this->redirect($request, $back_url);
                }
                $this->redirectBack($request, ['action' => 'index']);
            }
        }

        // エラー情報の設定
        $this->setErrorMessage(__('Input error exists'));
        $this->set('errors', $errors);

        return 'admin/comments/reply.twig';
    }

    /**
     * ajax用の返信
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
     */
    public function ajax_reply(Request $request): string
    {
        if ($this->isInvalidAjaxRequest($request)) {
            return $this->error403();
        }

        $comments_model = new CommentsModel();

        $comment_id = $request->get('id');
        $blog_id = $this->getBlogId($request);

        $this->setStatusDataList();

        // 返信用のコメント取得
        $comment = $comments_model->getReplyComment($blog_id, $comment_id);
        if (!$comment) {
            return $this->error404();
        }
        $this->set('comment', $comment);

        // コメントの初期表示時入力データ設定
        if (!$request->get('comment')) {
            $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);
            if ($comment['reply_status'] != Config::get('COMMENT.REPLY_STATUS.REPLY') && $blog_setting['comment_quote'] == Config::get('COMMENT.QUOTE.USE')) {
                $comment['reply_body'] = '> ' . str_replace("\n", "\n> ", $comment['body']) . "\n";
            }
            $request->set('comment', $comment);
            return "admin/comments/ajax_reply.twig";
        }

        // コメント投稿処理
        if ($request->method !== 'POST' || !$request->isValidSig()) {
            return $this->error403();
        }

        $errors = $comments_model->replyValidate($request->get('comment'), $data, ['reply_body']);

        if (empty($errors)) {
            if ($comments_model->updateReply($data, $comment)) {
                $this->setContentType("application/json; charset=utf-8");
                $this->set('json', ['success' => 1]);
                return "admin/common/json.twig";
            }
        }

        // error だが、JS側でsuccessプロパティ存在をみて判定しているので、 status codeは200を返す
        $this->setContentType("application/json; charset=utf-8");
        $this->set('json', ['error' => $errors['reply_body']]);
        return "admin/common/json.twig";
    }

    /**
     * 削除
     * @param Request $request
     */
    public function delete(Request $request)
    {
        // 削除処理
        if (Model::load('Comments')->deleteByIdsAndBlogId($request->get('id'), $this->getBlogId($request))) {
            $this->setInfoMessage(__('I removed the comment'));
        } else {
            $this->setErrorMessage(__('I failed to remove'));
        }

        // 元の画面へ戻る
        $back_url = $request->get('back_url');
        if (!empty($back_url)) {
            $this->redirect($request, $back_url);
        }
        $this->redirectBack($request, array('action' => 'index'));
    }

}

