<?php

namespace Fc2blog\Model;

use Fc2blog\Config;
use Fc2blog\Web\Cookie;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use RuntimeException;

class CommentsModel extends Model
{

    public static $instance = null;

    public function __construct()
    {
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new CommentsModel();
        }
        return self::$instance;
    }

    public function getTableName(): string
    {
        return 'comments';
    }

    public function getAutoIncrementCompositeKey(): string
    {
        return 'blog_id';
    }

    public static function getOpenStatusList()
    {
        return array(
            Config::get('COMMENT.OPEN_STATUS.PUBLIC') => __('Published'),
            Config::get('COMMENT.OPEN_STATUS.PENDING') => __('Approval pending'),
            Config::get('COMMENT.OPEN_STATUS.PRIVATE') => __('Only exposed administrator'),
        );
    }

    public static function getOpenStatusUserList()
    {
        return array(
            Config::get('COMMENT.OPEN_STATUS.PUBLIC') => __('Public comment'),
            Config::get('COMMENT.OPEN_STATUS.PRIVATE') => __('Secret comments to the management people'),
        );
    }

    public static function getReplyStatusList()
    {
        return array(
            Config::get('COMMENT.REPLY_STATUS.UNREAD') => __('Not yet read'),
            Config::get('COMMENT.REPLY_STATUS.READ') => __('Already read'),
            Config::get('COMMENT.REPLY_STATUS.REPLY') => __('Answered'),
        );
    }

    /**
     * バリデートを設定
     */
    private function setValidate()
    {
        $this->validates = array(
            'entry_id' => array(
                'required' => true,
                'numeric' => array(),
            ),
            'name' => array(
                'maxlength' => array('max' => 100),
            ),
            'title' => array(
                'maxlength' => array('max' => 200),
            ),
            'mail' => array(
                'required' => false,
                'maxlength' => array('max' => 255),
                'email' => array(),
            ),
            'url' => array(
                'required' => false,
                'maxlength' => array('max' => 255),
                'url' => array(),
            ),
            'body' => array(
                'required' => true,
                'maxlength' => array('max' => 5000),
            ),
            'reply_body' => array(
                'required' => true,
                'maxlength' => array('max' => 5000),
            ),
            'password' => array(
                'maxlength' => array('max' => 100),
            ),
            'open_status' => array(
                'default_value' => Config::get('COMMENT.OPEN_STATUS.PUBLIC'),
                'replace' => array(
                    'on' => Config::get('COMMENT.OPEN_STATUS.PRIVATE'),
                ), // データを置き換える
                'in_array' => array('values' => array(Config::get('COMMENT.OPEN_STATUS.PUBLIC'), Config::get('COMMENT.OPEN_STATUS.PRIVATE'))),
            ),
        );
    }

    /**
     * 登録用のバリデート
     * @param $data
     * @param $valid_data
     * @param array $white_list
     * @return array
     */
    public function registerValidate($data, &$valid_data = [], $white_list = array())
    {
        // Validateの設定
        $this->setValidate();

        return $this->validate($data, $valid_data, $white_list);
    }

    /**
     * 返信用のバリデート
     * @param $data
     * @param $valid_data
     * @param array $white_list
     * @return array
     */
    public function replyValidate($data, &$valid_data = [], $white_list = array())
    {
        // Validateの設定
        $this->setValidate();

        return $this->validate($data, $valid_data, $white_list);
    }

    /**
     * 編集用のバリデート処理
     * @param $data
     * @param $valid_data
     * @param $white_list
     * @param $comment
     * @return array
     */
    public function editValidate($data, &$valid_data, $white_list, $comment)
    {
        // Validateの設定
        $this->setValidate();

        // Validateの追加設定
        $this->validates['password']['required'] = true;
        $this->validates['password']['own'] = ['method' => 'password_check', 'password' => $comment['password']];   // パスワードチェック
        return $this->validate($data, $valid_data, $white_list);
    }

    /**
     * 編集可能なコメント情報を取得
     * @param $comment_id
     * @param $blog_id
     * @return array|mixed
     */
    public function getEditableComment($comment_id, $blog_id)
    {
        $comment = $this->findByIdAndBlogId($comment_id, $blog_id);
        // パスワード未設定、管理人のみは編集できない
        if (
            empty($comment) ||
            empty($comment['password']) ||
            $comment['open_status'] == Config::get('COMMENT.OPEN_STATUS.PRIVATE')
        ) {
            return array();
        }
        return $comment;
    }

    /**
     * 編集処理時のパスワードチェック
     * @param $value
     * @param $option
     * @return bool|string
     */
    public static function password_check($value, $option)
    {
        if (empty($option['password'])) {
            return __('No password is registered');   // パスワード未登録
        }
        if (password_verify($value, $option['password'])) {
            return true;
        }
        return __('The password is incorrect!');
    }

    /**
     * ユーザーパスワード用のハッシュを作成
     * @param string $password
     * @return string
     */
    public static function passwordHash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 記事に付随する情報を削除
     * @param $blog_id
     * @param $entry_id
     * @return array|false|int|mixed
     */
    public function deleteEntryRelation($blog_id, $entry_id)
    {
        return $this->delete('blog_id=? AND entry_id=?', array($blog_id, $entry_id));
    }

    /**
     * コメントの追加処理 + 記事のコメント数増加処理
     * @param Request $request
     * @param $data
     * @param $blog_setting
     * @return false|int
     */
    public function insertByBlogSetting(Request $request, $data, $blog_setting)
    {
        // Insert処理
        $id = $this->insertByBlogSettingWithOutCookie($data, $blog_setting);

        // 入力した名前,email,urlをCookieに登録
        if ($id && $blog_setting['comment_cookie_save'] == Config::get('COMMENT.COMMENT_COOKIE_SAVE.SAVE')) {
            if (isset($data['name'])) Cookie::set($request, 'comment_name', $data['name']);
            if (isset($data['mail'])) Cookie::set($request, 'comment_mail', $data['mail']);
            if (isset($data['url'])) Cookie::set($request, 'comment_url', $data['url']);
        }

        return $id;
    }

    /**
     * コメントの追加処理 + 記事のコメント数増加処理、Cookie設定無し
     * @param $data
     * @param $blog_setting
     * @return array|false|int|mixed
     */
    public function insertByBlogSettingWithOutCookie($data, $blog_setting)
    {
        // Entry記事数増加処理
        Model::load('Entries')->increaseCommentCount($data['blog_id'], $data['entry_id']);

        // パスワードの入力が合った場合ハッシュ化
        if (isset($data['password']) && $data['password'] !== '') {
            $data['password'] = $this->passwordHash($data['password']);
        }
        // 全体公開の場合 コメントの承認が必要な場合は承認待ちに変更
        if ($data['open_status'] == Config::get('COMMENT.OPEN_STATUS.PUBLIC')) {
            if ($blog_setting['comment_confirm'] == Config::get('COMMENT.COMMENT_CONFIRM.CONFIRM')) {
                $data['open_status'] = Config::get('COMMENT.OPEN_STATUS.PENDING');
            }
        }
        // 登録日時を設定
        $data['created_at'] = $data['updated_at'] = date('Y-m-d H:i:s');

        // Insert処理
        return parent::insert($data);
    }

    public function updateByIdAndBlogIdAndBlogSetting(Request $request, $data, $comment_id, $blog_id, $blog_setting)
    {
        // パスワードは更新しないので対象から削除
        unset($data['password']);

        // 全体公開の場合 コメントの承認が必要な場合は承認待ちに変更
        if ($data['open_status'] == Config::get('COMMENT.OPEN_STATUS.PUBLIC')) {
            if ($blog_setting['comment_confirm'] == Config::get('COMMENT.COMMENT_CONFIRM.CONFIRM')) {
                $data['open_status'] = Config::get('COMMENT.OPEN_STATUS.PENDING');
            }
        }

        // 更新日時を設定
        $data['updated_at'] = date('Y-m-d H:i:s');

        // 更新処理
        $ret = $this->updateByIdAndBlogId($data, $comment_id, $blog_id);

        // 入力した名前,email,urlをCookieに登録
        if ($ret && $blog_setting['comment_cookie_save'] == Config::get('COMMENT.COMMENT_COOKIE_SAVE.SAVE')) {
            if (isset($data['name'])) Cookie::set($request, 'comment_name', $data['name']);
            if (isset($data['mail'])) Cookie::set($request, 'comment_mail', $data['mail']);
            if (isset($data['url'])) Cookie::set($request, 'comment_url', $data['url']);
        }
        return $ret;
    }

    /**
     * idとblog_idをキーとした削除 + 記事のコメント数減少処理
     * @param $comment_id
     * @param $blog_id
     * @param array $options
     * @return array|false|int|mixed
     */
    public function deleteByIdAndBlogId($comment_id, $blog_id, $options = array())
    {
        $comment = $this->findByIdAndBlogId($comment_id, $blog_id);
        if (empty($comment)) {
            return 0;
        }

        // Entry記事数増加処理
        Model::load('Entries')->decreaseCommentCount($blog_id, $comment['entry_id']);

        // 記事本体削除
        return parent::deleteByIdAndBlogId($comment_id, $blog_id, $options);
    }

    /**
     * idとblog_idをキーとした削除 + 付随情報も削除
     * @param array $ids
     * @param $blog_id
     * @param array $options
     * @return bool
     */
    public function deleteByIdsAndBlogId($ids, $blog_id, $options = array())
    {
        // 単体ID対応
        if (is_numeric($ids)) {
            $ids = array($ids);
        }
        // 数値型配列チェック
        if (!$this->is_numeric_array($ids)) {
            return false;
        }

        // 削除処理(TODO:時間ができればSQLの最適化を行う)
        $flag = true;
        foreach ($ids as $id) {
            $flag = $flag && $this->deleteByIdAndBlogId($id, $blog_id, $options);
        }
        return $flag;
    }

    /**
     * ブログの設定情報からコメント一覧の設定情報を取得
     * @param $blog_id
     * @param $entry_id
     * @param $blog_setting
     * @return array
     */
    public function getCommentListOptionsByBlogSetting($blog_id, $entry_id, $blog_setting)
    {
        $isDisplayApprovalComment = $blog_setting['comment_display_approval'] == Config::get('COMMENT.COMMENT_DISPLAY.SHOW');
        $isDisplayPrivateComment = $blog_setting['comment_display_private'] == Config::get('COMMENT.COMMENT_DISPLAY.SHOW');

        $where = 'blog_id=? AND entry_id=?';
        $params = array($blog_id, $entry_id);
        /** @noinspection PhpStatementHasEmptyBodyInspection */
        if ($isDisplayApprovalComment == true && $isDisplayPrivateComment == true) {
            // 全て表示する場合はwhere条件追加無し
        } elseif ($isDisplayApprovalComment) {
            // 承認中コメントを表示
            $where .= ' AND open_status IN (' . Config::get('COMMENT.OPEN_STATUS.PUBLIC') . ',' . Config::get('COMMENT.OPEN_STATUS.PENDING') . ')';
        } elseif ($isDisplayPrivateComment) {
            // 非公開コメントを表示
            $where .= ' AND open_status IN (' . Config::get('COMMENT.OPEN_STATUS.PUBLIC') . ',' . Config::get('COMMENT.OPEN_STATUS.PRIVATE') . ')';
        } else {
            // 承認済みコメントを表示
            $where .= ' AND open_status = ' . Config::get('COMMENT.OPEN_STATUS.PUBLIC');
        }

        // 記事のコメント取得
        return [
            'where' => $where,
            'params' => $params,
            'order' => 'id ' . ($blog_setting['comment_order'] == Config::get('COMMENT.ORDER.ASC') ? 'ASC' : 'DESC'),
        ];
    }

    /**
     * テスト用、あるエントリの全コメントを取得
     * @param $blog_id
     * @param $entry_id
     * @return array
     */
    public function forTestGetCommentListOptionsByBlogSetting($blog_id, $entry_id)
    {
        $where = 'blog_id=? AND entry_id=?';
        $params = [$blog_id, $entry_id];

        // 記事のコメント取得
        return [
            'where' => $where,
            'params' => $params,
            'order' => 'id ',
        ];
    }

    const BLOG_TEMPLATE_REPLY_TYPE_COLUMN = array(
        1 => 'template_pc_reply_type',
        4 => 'template_sp_reply_type',
    );

    /**
     * コメント一覧の情報にブログの設定情報で装飾する
     * @param Request $request
     * @param $tmp_comments
     * @param $blog_setting
     * @param bool $self_blog
     * @return array
     */
    public function decorateByBlogSetting(Request $request, $tmp_comments, $blog_setting, $self_blog = false)
    {
        $flag_pending = Config::get('COMMENT.OPEN_STATUS.PENDING');
        $flag_private = Config::get('COMMENT.OPEN_STATUS.PRIVATE');

        $blog = Model::load('Blogs')->findById($blog_setting['blog_id']);

        // コメントを追加で表示するかどうか
        $is_add_comment = $blog_setting[self::BLOG_TEMPLATE_REPLY_TYPE_COLUMN[$request->deviceType]] == Config::get('BLOG_TEMPLATE.COMMENT_TYPE.AFTER');

        $comments = array();
        foreach ($tmp_comments as $comment) {
            if ($self_blog == false) {
                if ($comment['open_status'] == $flag_pending) {
                    $comment['title'] = '承認待ちです';
                    $comment['body'] = 'このコメントは承認待ちです';
                    $comment['name'] = '';
                    $comment['mail'] = '';
                    $comment['url'] = '';
                    $comment['password'] = '';
                } elseif ($comment['open_status'] == $flag_private) {
                    $comment['title'] = '管理人のみ閲覧できます';
                    $comment['body'] = 'このコメントは管理人のみ閲覧できます';
                    $comment['name'] = '';
                    $comment['mail'] = '';
                    $comment['url'] = '';
                    $comment['password'] = '';
                }
            }
            $comments[] = $comment;

            // コメント返信の分(テンプレに返信タグがついている場合 下にコメントを追記する形で出力する
            if ($is_add_comment && $comment['reply_status'] == Config::get('COMMENT.REPLY_STATUS.REPLY')) {
                $comment['title'] = 'Re: ' . $comment['title'];
                $comment['body'] = $comment['reply_body'];
                $comment['name'] = $blog['nickname'];
                $comment['updated_at'] = $comment['reply_updated_at'];
                $comment['url'] = $comment['mail'] = $comment['password'] = '';
                $comments[] = $comment;
            }
        }

        return $comments;
    }

    /**
     * ブログの設定情報からコメント一覧取得
     * @param Request $request
     * @param $blog_id
     * @param $entry_id
     * @param $blog_setting
     * @param bool $self_blog
     * @return array
     */
    public function getCommentListByBlogSetting(Request $request, $blog_id, $entry_id, $blog_setting, $self_blog = false)
    {
        $options = $this->getCommentListOptionsByBlogSetting($blog_id, $entry_id, $blog_setting);
        $comments = $this->find('all', $options);
        return $this->decorateByBlogSetting($request, $comments, $blog_setting, $self_blog);
    }

    /**
     * 未読コメント数
     * @param $blog_id
     * @return mixed
     */
    public function getUnreadCount($blog_id)
    {
        return $this->find('count', [
            'where' => 'blog_id=? AND reply_status=?',
            'params' => [$blog_id, Config::get('COMMENT.REPLY_STATUS.UNREAD')],
        ]);
    }

    /**
     * 未承認コメント数
     * @param $blog_id
     * @return mixed
     */
    public function getUnapprovedCount($blog_id)
    {
        return $this->find('count', [
            'where' => 'blog_id=? AND open_status=?',
            'params' => [$blog_id, Config::get('COMMENT.OPEN_STATUS.PENDING')],
        ]);
    }

    /**
     * 返信用のコメントを取得する
     * ・記事のタイトル付与
     * ・未読の場合既読に更新
     * @param $blog_id
     * @param $comment_id
     * @return array|mixed
     */
    public function getReplyComment($blog_id, $comment_id)
    {
        // 表示データの取得
        if (!$comment = $this->findByIdAndBlogId($comment_id, $blog_id)) {
            return array();
        }
        $entry_id = $comment['entry_id'];
        if (!$entry = Model::load('Entries')->findByIdAndBlogId($entry_id, $blog_id)) {
            return array();
        }
        $comment['entry_title'] = $entry['title'];

        return $this->setReadCommentStatus($blog_id, $comment);
    }

    /**
     * 指定のコメントを未読の場合既読に更新する
     * @param string $blog_id
     * @param array $comment
     * @return array Comment
     */
    public function setReadCommentStatus(string $blog_id, array $comment): array
    {
        // 未読状態の場合既読に変更する
        if ($comment['reply_status'] == Config::get('COMMENT.REPLY_STATUS.UNREAD')) {
            if (false === $this->updateReplyStatus($blog_id, $comment['id'], Config::get('COMMENT.REPLY_STATUS.READ'))) {
                throw new RuntimeException("update failed");
            }
            $comment['reply_status'] = Config::get('COMMENT.REPLY_STATUS.READ');
        }
        return $comment;
    }

    /**
     * テスト用、あるエントリの全コメントを取得
     * @param $blog_id
     * @param array $comment_id_list
     * @return array
     */
    public function getCommentListByCommentIdList($blog_id, array $comment_id_list): array
    {
        $where = "blog_id=? AND id ";
        $params = [$blog_id];

        $where .= 'IN (';
        foreach ($comment_id_list as $comment_id) {
            $where .= '?,';
            $params[] = (int)$comment_id;
        }
        $where = rtrim($where, ',');
        $where .= ') ';

        // 記事のコメント取得
        return $this->find('all', [
            'where' => $where,
            'params' => $params,
            'order' => 'id ',
        ]);
    }

    /**
     * comment idリスト指定のcomment群を既読にする
     * @param array $comment_id_list
     * @param string $blog_id
     * @return bool
     */
    public function setReadByIdsAndBlogId(array $comment_id_list, string $blog_id): bool
    {
        // 数値型配列チェック
        if (!$this->is_numeric_array($comment_id_list)) {
            return false;
        }

        $comment_list = $this->getCommentListByCommentIdList($blog_id, $comment_id_list);
        $flag = true;
        foreach ($comment_list as $comment) {
            $flag = $flag && $this->setReadCommentStatus($blog_id, $comment);
        }
        return $flag;
    }

    /**
     * 承認待ちコメントを承認済みに変更する
     * @param $blog_id
     * @param null $comment_id
     * @return array|false|int|mixed
     */
    public function updateApproval($blog_id, $comment_id = null)
    {
        $params = array($blog_id);
        $sql = 'UPDATE ' . $this->getTableName() . ' SET open_status=' . Config::get('COMMENT.OPEN_STATUS.PUBLIC');
        $sql .= ' WHERE blog_id=?';
        if ($comment_id) {
            $sql .= ' AND id=?';
            $params[] = $comment_id;
        }
        $sql .= ' AND open_status=' . Config::get('COMMENT.OPEN_STATUS.PENDING');
        $options['result'] = PDOQuery::RESULT_SUCCESS;
        return $this->executeSql($sql, $params, $options);
    }

    /**
     * 返信ステータスを変更する
     * @param $blog_id
     * @param $comment_id
     * @param $reply_status
     * @return array|false|int|mixed
     */
    public function updateReplyStatus($blog_id, $comment_id, $reply_status)
    {
        $data = array('reply_status' => $reply_status);
        return parent::updateByIdAndBlogId($data, $comment_id, $blog_id);
    }

    /**
     * 返信処理
     * @param $data
     * @param $comment
     * @return array|false|int|mixed
     */
    public function updateReply($data, $comment)
    {
        // 承認待ちの場合 全体公開への変更も行う
        if ($comment['open_status'] == Config::get('COMMENT.OPEN_STATUS.PENDING')) {
            $data['open_status'] = Config::get('COMMENT.OPEN_STATUS.PUBLIC');
        }
        $data['reply_status'] = Config::get('COMMENT.REPLY_STATUS.REPLY');  // 返信済みに変更
        $data['reply_updated_at'] = date('Y-m-d H:i:s');                    // 返信更新日を更新
        return parent::updateByIdAndBlogId($data, $comment['id'], $comment['blog_id']);
    }

    /**
     * テンプレートで使用する新着コメント一覧を取得
     * @param Request $request
     * @param $blog_id
     * @param int $limit
     * @return mixed
     */
    public function getTemplateRecentCommentList(Request $request, $blog_id, $limit = 0)
    {
        if ($limit == 0) {
            $blog_setting = Model::load('BlogSettings')->findByBlogId($blog_id);
            $limit = $blog_setting['comment_display_count'];
        }

        // 記事の表示項目リスト
        $open_status_list = array(
            Config::get('ENTRY.OPEN_STATUS.OPEN'),      // 公開
            Config::get('ENTRY.OPEN_STATUS.LIMIT'),     // 期間限定
        );

        $where = 'comments.blog_id=?';
        $where .= ' AND comments.open_status=' . Config::get('COMMENT.OPEN_STATUS.PUBLIC');
        $where .= ' AND entries.blog_id=?';
        $where .= ' AND entries.open_status IN (' . implode(',', $open_status_list) . ')';
        $where .= ' AND comments.entry_id=entries.id';
        $params = array($blog_id, $blog_id);

        // 記事のコメント取得
        $options = array(
            'fields' => 'comments.*, entries.title as entry_title',
            'where' => $where,
            'params' => $params,
            'from' => 'entries',
            'order' => 'id DESC',
            'limit' => $limit,
        );

        $comments = $this->find('all', $options);
        foreach ($comments as $key => $value) {
            $comments[$key]['link'] = Html::url($request, array('controller' => 'Entries', 'action' => 'view', 'blog_id' => $value['blog_id'], 'id' => $value['entry_id']));

            list($comments[$key]['year'], $comments[$key]['month'], $comments[$key]['day'],
                $comments[$key]['hour'], $comments[$key]['minute'], $comments[$key]['second'], $comments[$key]['youbi'], $comments[$key]['month_short']
                ) = explode('/', date('Y/m/d/H/i/s/D/M', strtotime($value['updated_at'])));
            $comments[$key]['wayoubi'] = __($comments[$key]['youbi']);
        }
        return $comments;
    }
}
