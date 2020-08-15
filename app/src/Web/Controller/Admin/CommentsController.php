<?php

namespace Fc2blog\Web\Controller\Admin;

class CommentsController extends AdminController
{

  /**
   * 一覧表示
   */
  public function index()
  {
    $request = \Fc2blog\Web\Request::getInstance();
    $comments_model = \Fc2blog\Model\Model::load('Comments');

    $blog_id = $this->getBlogId();

    // 検索条件
    $where = 'comments.blog_id=?';
    $params = array($blog_id);

    if ($keyword=$request->get('keyword')) {
      $keyword = \Fc2blog\Model\Model::escape_wildcard($keyword);
      $keyword = "%{$keyword}%";
      $where .= ' AND (comments.title LIKE ? OR comments.body LIKE ? OR comments.name LIKE ?)';
      $params = array_merge($params, array($keyword, $keyword, $keyword));
    }
    if (($open_status=$request->get('open_status'))!==null) {
      $where .= ' AND comments.open_status=?';
      $params[] = $open_status;
    }
    if ($reply_status=$request->get('reply_status')) {
      $where .= ' AND comments.reply_status=?';
      $params[] = $reply_status;
    }
    if ($entry_id=$request->get('entry_id')) {
      $where .= ' AND comments.entry_id=?';
      $params[] = $entry_id;
    }

    // 記事の結合条件追加
    $where .= ' AND entries.blog_id=? AND comments.entry_id=entries.id';
    $params[] = $blog_id;

    // 並び順
    $order = 'comments.created_at DESC, id DESC';
    switch ($request->get('order')) {
      default: case 'created_at_desc': break;
      case 'created_at_asc': $order = 'comments.created_at ASC, comments.id ASC'; break;
      case 'name_desc':      $order = 'comments.name DESC, comments.id DESC';     break;
      case 'name_asc':       $order = 'comments.name ASC, comments.id ASC';       break;
      case 'body_desc':      $order = 'comments.body DESC, comments.id DESC';     break;
      case 'body_asc':       $order = 'comments.body ASC, comments.id ASC';       break;
      case 'entry_id_desc':  $order = 'comments.entry_id DESC, comments.id DESC'; break;
      case 'entry_id_asc':   $order = 'comments.entry_id ASC, comments.id ASC';   break;
    }

    $options = array(
      'fields' => array('comments.*', 'entries.title as entry_title'),
      'from'   => 'entries',
      'where'  => $where,
      'params' => $params,
      'limit'  => $request->get('limit', \Fc2blog\Config::get('ENTRY.DEFAULT_LIMIT'), \Fc2blog\Web\Request::VALID_POSITIVE_INT),
      'page'   => $request->get('page', 0, \Fc2blog\Web\Request::VALID_UNSIGNED_INT),
      'order'  => $order,
    );

    if ($options['limit'] > max(array_keys(\Fc2blog\Config::get('ENTRY.LIMIT_LIST')))) {
      $options['limit'] = \Fc2blog\Config::get('ENTRY.DEFAULT_LIMIT');
    }
    if (ceil(PHP_INT_MAX / $options['limit']) <= $options['page']) {
      $options['page'] = 0;
    }

    $comments = $comments_model->find('all', $options);
    $paging = $comments_model->getPaging($options);

    $this->set('comments', $comments);
    $this->set('paging', $paging);
  }

  /**
  * コメントの承認
  */
  public function approval()
  {
    $request = \Fc2blog\Web\Request::getInstance();
    $comments_model = \Fc2blog\Model\Model::load('Comments');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 承認データの取得
    if (!$comment=$comments_model->findByIdAndBlogId($id, $blog_id)) {
      $this->redirect(array('action'=>'index'));
    }

    if ($comment['open_status']!=\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PENDING')) {
      // 承認待ち以外はリダイレクト
      $this->redirect(array('action'=>'index'));
    }

    // 承認処理
    $comments_model->updateApproval($blog_id, $id);
    $this->setInfoMessage(__('I approved a comment'));

    // 元の画面へ戻る
    $back_url = $request->get('back_url');
    if (!empty($back_url)) {
      $this->redirect($back_url);
    }
    $this->redirect(array('action'=>'index'));
  }

  /**
  * コメントの承認(ajax版)
  */
  public function ajax_approval () {
    \Fc2blog\Config::set('DEBUG', 0);
    $this->layout = 'json.html';

    $request = \Fc2blog\Web\Request::getInstance();
    $comments_model = \Fc2blog\Model\Model::load('Comments');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 承認データの取得
    if (!$comment=$comments_model->findByIdAndBlogId($id, $blog_id)) {
      $this->set('json', array('error'=>__('Comments subject to approval does not exist')));
      return ;
    }

    if ($comment['open_status']!=\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PENDING')) {
      $this->set('json', array('success'=>1));
      return ;
    }

    // 承認処理
    $comments_model->updateApproval($blog_id, $id);
    $this->set('json', array('success'=>1));
  }

  /**
  * 返信
  */
  public function reply()
  {
    $request = \Fc2blog\Web\Request::getInstance();
    $comments_model = \Fc2blog\Model\Model::load('Comments');

    $comment_id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 返信用のコメント取得
    $comment = $comments_model->getReplyComment($blog_id, $comment_id);
    if (!$comment) {
      return $this->error404();
    }
    $this->set('comment', $comment);

    // コメントの初期表示時入力データ設定
    if (!$request->get('comment')){
      $blog_setting = \Fc2blog\Model\Model::load('BlogSettings')->findByBlogId($blog_id);
      if ($comment['reply_status']!=\Fc2blog\Config::get('COMMENT.REPLY_STATUS.REPLY') && $blog_setting['comment_quote']==\Fc2blog\Config::get('COMMENT.QUOTE.USE')) {
        $comment['reply_body'] = '> ' . str_replace("\n", "\n> ",$comment['body']) . "\n";
      }
      $request->set('comment', $comment);
      $back_url = $request->getReferer();
      if (!empty($back_url)) {
        $request->set('back_url', $request->getReferer());    // 戻る用のURL
      }
      return ;
    }

    // コメント投稿処理
    $errors = array();
    $errors['comment'] = $comments_model->replyValidate($request->get('comment'), $data, array('reply_body'));
    if (empty($errors['comment'])) {
      if ($comments_model->updateReply($data, $comment)) {
        $this->setInfoMessage(__('I did reply to comment '));

        // 元の画面へ戻る
        $back_url = $request->get('back_url');
        if (!empty($back_url)) {
          $this->redirect($back_url);
        }
        $this->redirectBack(array('action'=>'index'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
  * ajax用の返信
  */
  public function ajax_reply(){
    \Fc2blog\Config::set('DEBUG', 0);
    $this->layout = 'ajax.html';

    $request = \Fc2blog\Web\Request::getInstance();
    $comments_model = \Fc2blog\Model\Model::load('Comments');

    $comment_id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 返信用のコメント取得
    $comment = $comments_model->getReplyComment($blog_id, $comment_id);
    if (!$comment) {
      return $this->error404();
    }
    $this->set('comment', $comment);

    // コメントの初期表示時入力データ設定
    if (!$request->get('comment')){
      $blog_setting = \Fc2blog\Model\Model::load('BlogSettings')->findByBlogId($blog_id);
      if ($comment['reply_status']!=\Fc2blog\Config::get('COMMENT.REPLY_STATUS.REPLY') && $blog_setting['comment_quote']==\Fc2blog\Config::get('COMMENT.QUOTE.USE')) {
        $comment['reply_body'] = '> ' . str_replace("\n", "\n> ",$comment['body']) . "\n";
      }
      $request->set('comment', $comment);
      return ;
    }

    // 下記の入力チェック処理以降はjsonで返却
    $this->layout = 'json.html';

    // コメント投稿処理
    $errors = array();
    $errors = $comments_model->replyValidate($request->get('comment'), $data, array('reply_body'));
    if (empty($errors)) {
      if ($comments_model->updateReply($data, $comment)) {
        $this->set('json', array('success'=>1));
        return ;
      }
    }

    $this->set('json', array('error'=>$errors['reply_body']));
  }

  /**
   * 削除
   */
  public function delete()
  {
    $request = \Fc2blog\Web\Request::getInstance();

    // 削除処理
    if (\Fc2blog\Model\Model::load('Comments')->deleteByIdsAndBlogId($request->get('id'), $this->getBlogId())) {
      $this->setInfoMessage(__('I removed the comment'));
    } else {
      $this->setErrorMessage(__('I failed to remove'));
    }

    // 元の画面へ戻る
    $back_url = $request->get('back_url');
    if (!empty($back_url)) {
      $this->redirect($back_url);
    }
    $this->redirectBack(array('action'=>'index'));
  }

}

