<?php

require_once(\Fc2blog\Config::get('CONTROLLER_DIR') . 'app_controller.php');

abstract class UserController extends AppController
{

  /**
  * ブログID取得
  */
  public function getBlogId()
  {
    $request = Request::getInstance();
    return $request->get('blog_id');
  }

  /**
  * 管理画面ログイン中のブログIDを取得する
  */
  protected function getAdminBlogId()
  {
    return \Fc2blog\Session::get('blog_id');
  }

  /**
  * 管理画面ログイン中のUserIDを取得する
  */
  protected function getAdminUserId()
  {
    return \Fc2blog\Session::get('user_id');
  }

  /**
  * ログイン中のブログかどうかを返却
  */
  protected function isLoginBlog(){
    // ログイン中判定
    $admin_blog_id = $this->getAdminBlogId();
    if (empty($admin_blog_id)) {
      return false;
    }
    // ログイン中のブログIDと判定
    $blog_id = $this->getBlogId();
    if ($admin_blog_id==$blog_id) {
      return true;
    }
    // ログイン判定
    return Model::load('Blogs')->isUserHaveBlogId($this->getAdminUserId(), $blog_id);
  }

  /**
  * ブログのパスワードキー
  */
  protected function getBlogPasswordKey($blog_id)
  {
    return 'blog_password.' . $blog_id;
  }

  /**
  * 記事のパスワードキー
  */
  protected function getEntryPasswordKey($blog_id, $entry_id)
  {
    return 'entry_password.' . $blog_id . '.' . $entry_id;
  }

}

