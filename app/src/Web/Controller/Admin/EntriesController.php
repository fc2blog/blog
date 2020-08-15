<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Model\EntriesModel;
use Fc2blog\Model\EntryCategoriesModel;
use Fc2blog\Model\Model;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

class EntriesController extends AdminController
{

  /**
   * 一覧表示
   */
  public function index()
  {
    $request = Request::getInstance();
    $entries_model = Model::load('Entries');

    $blog_id = $this->getBlogId();

    // 検索条件
    $where = 'entries.blog_id=?';
    $params = array($blog_id);
    $from = array();

    if ($keyword=$request->get('keyword')) {
      $keyword = Model::escape_wildcard($keyword);
      $keyword = "%{$keyword}%";
      $where .= ' AND (entries.title LIKE ? OR entries.body LIKE ? OR entries.extend LIKE ?)';
      $params = array_merge($params, array($keyword, $keyword, $keyword));
    }
    if ($open_status=$request->get('open_status')) {
      $where .= ' AND entries.open_status=?';
      $params[] = $open_status;
    }
    if ($category_id=$request->get('category_id')) {
      $where .= ' AND entry_categories.blog_id=? AND entry_categories.category_id=? AND entries.id=entry_categories.entry_id';
      $params = array_merge($params, array($blog_id, $category_id));
      $from[] = 'entry_categories';
    }
    if ($tag_id=$request->get('tag_id')) {
      $where .= ' AND entry_tags.blog_id=? AND entry_tags.tag_id=? AND entries.id=entry_tags.entry_id';
      $params = array_merge($params, array($blog_id, $tag_id));
      $from[] = 'entry_tags';
    }

    // 並び順
    $order = 'entries.posted_at DESC, entries.id DESC';
    switch ($request->get('order')) {
      default: case 'posted_at_desc': break;
      case 'posted_at_asc': $order = 'entries.posted_at ASC, entries.id ASC';       break;
      case 'title_desc':    $order = 'entries.title DESC, entries.id DESC';         break;
      case 'title_asc':     $order = 'entries.title ASC, entries.id ASC';           break;
      case 'comment_desc':  $order = 'entries.comment_count DESC, entries.id DESC'; break;
      case 'comment_asc':   $order = 'entries.comment_count ASC, entries.id ASC';   break;
      case 'body_desc':     $order = 'entries.body DESC, entries.id DESC';          break;
      case 'body_asc':      $order = 'entries.body ASC, entries.id ASC';            break;
    }

    Session::set('sig', App::genRandomString());

    // オプション設定
    $options = array(
      'fields' => 'entries.*',
      'where'  => $where,
      'params' => $params,
      'from'   => $from,
      'limit'  => $request->get('limit', Config::get('ENTRY.DEFAULT_LIMIT'), Request::VALID_POSITIVE_INT),
      'page'   => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
      'order'  => $order,
    );
    $entries = $entries_model->find('all', $options);
    $paging  = $entries_model->getPaging($options);

    $this->set('entries', $entries);
    $this->set('paging', $paging);
  }

  /**
   * 新規作成
   */
  public function create()
  {
    // IE11のエディター対応
    if (stristr($_SERVER['HTTP_USER_AGENT'], 'trident')) {
      header('X-UA-Compatible: IE=EmulateIE10');
    }

    $request = Request::getInstance();
    /** @var EntriesModel $entries_model */
    $entries_model = Model::load('Entries');
    /** @var EntryCategoriesModel $entry_categories_model */
    $entry_categories_model = Model::load('EntryCategories');

    $blog_id = $this->getBlogId();

    // 初期表示時
    if (!$request->get('entry') || !Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      Session::set('sig', App::genRandomString());
      return ;
    }

    // 新規登録処理
    $errors = array();
    $whitelist_entry = array('title', 'body', 'extend', 'open_status', 'password', 'auto_linefeed', 'comment_accepted', 'posted_at');
    $errors['entry'] = $entries_model->validate($request->get('entry'), $entry_data, $whitelist_entry);
    $errors['entry_categories'] = $entry_categories_model->validate($request->get('entry_categories'), $entry_categories_data, array('category_id'));
    if (empty($errors['entry']) && empty($errors['entry_categories'])) {
      $entry_data['blog_id'] = $blog_id;
      if ($id=$entries_model->insert($entry_data)) {
        // カテゴリと紐付
        $entry_categories_model->save($blog_id, $id, $entry_categories_data);
        // タグと紐付
        Model::load('EntryTags')->save($blog_id, $id, $request->get('entry_tags'));
        // 一覧ページへ遷移
        $this->setInfoMessage(__('I created a entry'));
        $this->redirect(array('action'=>'index'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
   * 編集
   */
  public function edit()
  {
    // IE11のエディター対応
    if (stristr($_SERVER['HTTP_USER_AGENT'], 'trident')) {
      header('X-UA-Compatible: IE=EmulateIE10');
    }

    $request = Request::getInstance();
    /** @var EntriesModel $entries_model */
    $entries_model = Model::load('Entries');
    /** @var EntryCategoriesModel $entry_categories_model */
    $entry_categories_model = Model::load('EntryCategories');

    $id = $request->get('id');
    $blog_id = $this->getBlogId();

    // 初期表示時に編集データの取得&設定
    if (!$request->get('entry')) {
      if (!$entry=$entries_model->findByIdAndBlogId($id, $blog_id)) {
        $this->redirect(array('action'=>'index'));
      }
      $request->set('entry', $entry);
      $request->set('entry_categories', array('category_id'=>$entry_categories_model->getCategoryIds($blog_id, $id)));
      $request->set('entry_tags', Model::load('Tags')->getEntryTagNames($blog_id, $id));   // タグの文字列をテーブルから取得
      return ;
    }

    if (!Session::get('sig') || Session::get('sig') !== $request->get('sig')) {
      $request->clear();
      return;
    }
    
    // 更新処理
    $errors = array();
    $whitelist_entry = array('title', 'body', 'extend', 'open_status', 'password', 'auto_linefeed', 'comment_accepted', 'posted_at');
    $errors['entry'] = $entries_model->validate($request->get('entry'), $entry_data, $whitelist_entry);
    $errors['entry_categories'] = $entry_categories_model->validate($request->get('entry_categories'), $entry_categories_data, array('category_id'));
    if (empty($errors['entry']) && empty($errors['entry_categories'])) {
      if ($entries_model->updateByIdAndBlogId($entry_data, $id, $blog_id)) {
        // カテゴリと紐付
        $entry_categories_model->save($blog_id, $id, $entry_categories_data);
        // タグと紐付
        Model::load('EntryTags')->save($blog_id, $id, $request->get('entry_tags'));
        // 一覧ページへ遷移
        $this->setInfoMessage(__('I have updated the entry'));
        $this->redirect(array('action'=>'index'));
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);
  }

  /**
   * 削除
   */
  public function delete()
  {
    $request = Request::getInstance();
    if (Session::get('sig') && Session::get('sig') === $request->get('sig')) {
      // 削除処理
      if (Model::load('Entries')->deleteByIdsAndBlogId($request->get('id'), $this->getBlogId()))
        $this->setInfoMessage(__('I removed the entry'));
    }
    $this->redirect(array('action'=>'index'));
  }

  /**
  * ajaxでメディアを表示する画面
  */
  public function ajax_media_load()
  {
    Config::set('DEBUG', 0);    // デバッグ設定を変更

    $request = Request::getInstance();
    $files_model = Model::load('Files');

    $blog_id = $this->getBlogId();

    // 検索条件
    $where = 'blog_id=?';
    $params = array($blog_id);
    if ($request->get('keyword')) {
      $where .= ' AND name like ?';
      $params[] = '%' . $request->get('keyword') . '%';
    }

    $options = array(
      'where'  => $where,
      'params' => $params,
      'limit'  => Config::get('PAGE.FILE.LIMIT', App::getPageLimit('FILE_AJAX')),
      'page'   => $request->get('page', 0, Request::VALID_UNSIGNED_INT),
      'order'  => 'id DESC',
    );
    $files = $files_model->find('all', $options);
    $paging = $files_model->getPaging($options);

    $this->set('files', $files);
    $this->set('paging', $paging);

    $this->layout = 'ajax.html';
  }

}

