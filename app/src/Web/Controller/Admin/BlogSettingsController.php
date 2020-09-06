<?php

namespace Fc2blog\Web\Controller\Admin;

use Fc2blog\Config;
use Fc2blog\Model\BlogSettingsModel;
use Fc2blog\Model\CommentsModel;
use Fc2blog\Web\Request;

class BlogSettingsController extends AdminController
{

  /**
   * コメント編集
   * @param Request $request
   */
  public function comment_edit(Request $request)
  {
    $white_list = array(
      'comment_confirm', 'comment_display_approval', 'comment_display_private',
      'comment_cookie_save', 'comment_captcha',
      'comment_order', 'comment_display_count', 'comment_quote'
    );
    $this->settingEdit($request, $white_list, 'comment_edit');
  }

  /**
   * 記事編集
   * @param Request $request
   * @return string
   */
  public function entry_edit(Request $request): string
  {
    $white_list = ['entry_order', 'entry_recent_display_count', 'entry_display_count', 'entry_password'];
    $this->set('template_path', 'admin/blog_settings/entry_edit.twig');
    $this->set('tab', 'entry_edit');
    $this->set('blog_settings_entry_order_list', BlogSettingsModel::getEntryOrderList());
    return $this->settingEdit($request, $white_list, 'entry_edit');
  }

  /**
   * その他編集
   * @param Request $request
   */
  public function etc_edit(Request $request)
  {
    $white_list = array('start_page');
    $this->settingEdit($request, $white_list, 'etc_edit');
  }

  /**
   * ブログの設定変更処理
   * @param Request $request
   * @param $white_list
   * @param $action
   * @return string
   */
  private function settingEdit(Request $request, $white_list, $action): string
  {
    $blog_settings_model = new BlogSettingsModel();
    $blog_id = $this->getBlogId($request);

    // 初期表示時に編集データの取得&設定
    if (!$request->get('blog_setting') || !$request->isValidSig()) {
      $request->generateNewSig();
      $blog_setting = $blog_settings_model->findByBlogId($blog_id);
      $request->set('blog_setting', $blog_setting);
      return $this->get('template_path');
    }

    // 更新処理
    $errors = [];
    $errors['blog_setting'] = $blog_settings_model->validate($request->get('blog_setting'), $blog_setting_data, $white_list);
    if (empty($errors['blog_setting'])) {
      // コメント確認からコメントを確認せずそのまま表示に変更した場合既存の承認待ちを全て承認済みに変更する
      $blog_setting = $blog_settings_model->findByBlogId($blog_id);
      if ($blog_setting['comment_confirm'] == Config::get('COMMENT.COMMENT_CONFIRM.CONFIRM')
        && isset($blog_setting_data['comment_confirm'])
        && $blog_setting_data['comment_confirm'] == Config::get('COMMENT.COMMENT_CONFIRM.THROUGH')
      ) {
        $comments = new CommentsModel();
        $comments->updateApproval($blog_id);
      }

      // ブログの設定情報更新処理
      if ($blog_settings_model->updateByBlogId($blog_setting_data, $blog_id)) {
        // 一覧ページへ遷移
        $this->setInfoMessage(__("I have updated the configuration information of the blog"));
        $this->redirect($request, ['action' => $action]);
      }
    }

    // エラー情報の設定
    $this->setErrorMessage(__('Input error exists'));
    $this->set('errors', $errors);

    return $this->get('template_path');
  }

}

