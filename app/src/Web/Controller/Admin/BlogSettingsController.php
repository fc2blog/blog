<?php
declare(strict_types=1);

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
     * @return string
     */
    public function comment_edit(Request $request): string
    {
        $white_list = array(
            'comment_confirm', 'comment_display_approval', 'comment_display_private',
            'comment_cookie_save', 'comment_captcha',
            'comment_order', 'comment_display_count', 'comment_quote'
        );
        $this->set('template_path', 'admin/blog_settings/comment_edit.twig');
        $this->set('tab', 'comment_edit');
        $this->set('blog_settings_comment_confirm_list', BlogSettingsModel::getCommentConfirmList());
        $this->set('blog_settings_comment_display_approval_list', BlogSettingsModel::getCommentDisplayApprovalList());
        $this->set('blog_settings_comment_display_private_list', BlogSettingsModel::getCommentDisplayPrivateList());
        $this->set('blog_settings_comment_cookie_save_list', BlogSettingsModel::getCommentCookieSaveList());
        $this->set('blog_settings_comment_captcha_list', BlogSettingsModel::getCommentCaptchaList());
        $this->set('blog_settings_comment_order_list', BlogSettingsModel::getCommentOrderList());
        $this->set('blog_settings_comment_quote_list', BlogSettingsModel::getCommentQuoteList());
        return $this->settingEdit($request, $white_list, 'comment_edit');
    }

    /**
     * 記事編集
     * @param Request $request
     * @return string
     * @noinspection PhpUnused
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
     * @return string
     * @noinspection PhpUnused
     */
    public function etc_edit(Request $request): string
    {
        $white_list = array('start_page');
        $this->set('template_path', 'admin/blog_settings/etc_edit.twig');
        $this->set('blog_settings_start_page_list', BlogSettingsModel::getStartPageList());
        $this->set('tab', 'etc_edit');
        return $this->settingEdit($request, $white_list, 'etc_edit');
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
        $blog_id = $this->getBlogIdFromSession();

        // 初期表示時に編集データの取得&設定
        if (!$request->get('blog_setting') || !$request->isValidSig()) {
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

