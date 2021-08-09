<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\User;

use Fc2blog\Model\BlogsModel;
use Fc2blog\Web\Controller\Controller;
use Fc2blog\Web\Fc2BlogTemplate;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use InvalidArgumentException;

abstract class UserController extends Controller
{
    /**
     * 管理画面ログイン中のブログIDを取得する
     */
    protected static function getAdminBlogId(): ?string
    {
        return Session::get('blog_id');
    }

    /**
     * 管理画面ログイン中のUserIDを取得する
     */
    protected static function getAdminUserId()
    {
        return Session::get('user_id');
    }

    /**
     * ログイン中のブログかどうかを返却
     * @param Request $request
     * @return bool
     */
    protected function isLoginBlog(Request $request): bool
    {
        // ログイン中か
        $admin_blog_id = $this->getAdminBlogId();
        if (empty($admin_blog_id)) {
            return false;
        }
        // セッションに持っているログイン中のブログIDを取得
        $blog_id = $request->getBlogId();
        if ($admin_blog_id == $blog_id) {
            return true;
        }
        // あるいはログイン中のアカウントがブログ所有者か判定
        $blogs_model = new BlogsModel();
        return $blogs_model->isUserHaveBlogId($admin_blog_id, $blog_id);
    }

    /**
     * ブログのパスワードキー
     * @param string $blog_id
     * @return string
     */
    protected static function getBlogPasswordKey(string $blog_id): string
    {
        return 'blog_password.' . $blog_id;
    }

    /**
     * 記事のパスワードキー
     * @param string $blog_id
     * @param int $entry_id
     * @return string
     */
    protected static function getEntryPasswordKey(string $blog_id, int $entry_id): string
    {
        /** @noinspection PhpUnnecessaryStringCastInspection */
        return 'entry_password.' . $blog_id . '.' . (string)$entry_id;
    }

    /**
     * FC2タグを用いたユーザーテンプレート（PHP）でHTMLをレンダリング
     * @param Request $request
     * @param string $template_file_path
     * @return string
     */
    protected function renderByFc2Template(Request $request, string $template_file_path): string
    {
        if (!is_file($template_file_path)) {
            throw new InvalidArgumentException("missing template");
        }

        $this->data = Fc2BlogTemplate::preprocessingData($request, $this->data);

        // 設定されているdataをローカルスコープに展開
        extract($this->data);

        // テンプレートをレンダリングして返す
        ob_start();
        include($template_file_path);
        return ob_get_clean();
    }
}
