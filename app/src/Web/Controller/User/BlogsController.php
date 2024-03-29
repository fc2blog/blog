<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\User;

use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\EntriesModel;
use Fc2blog\Web\Request;
use FeedWriter\RSS2;

class BlogsController extends UserController
{
    /**
     * ランダムなブログにリダイレクト
     * プラグインインストールでポータル画面化予定
     * @param Request $request
     * @return string
     */
    public function index(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog = (new BlogsModel())->findByRandom();
        if (empty($blog)) {
            return $this->error404();
        }
        $this->redirect($request, $request->baseDirectory . $blog['id'] . '/');
        return "";
    }

    /**
     * RSS Feed
     * @param Request $request
     * @return string
     */
    public function feed(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blogs_model = new BlogsModel();
        $blog = $blogs_model->findById($request->getBlogId());
        if (empty($blog)) {
            return $this->error404();
        }

        // 記事一覧取得
        $entries_model = new EntriesModel();
        $entries = $entries_model->find('all', [
            'where' => 'blog_id=?',
            'params' => [$blog['id']],
            'limit' => 10,
            'order' => 'updated_at DESC',
        ]);

        // 非公開ブログはRSS拒否
        if ($blog['open_status'] === BlogsModel::BLOG['OPEN_STATUS']['PRIVATE']) {
            return $this->error403();
        }

        // build feed
        $feed = new RSS2();
        $feed->setTitle((string)$blog["name"]);
        $feed->setLink(BlogsModel::getFullUrlByBlogId($blog['id']));
        $feed->setDescription((string)$blog['introduction']);
        foreach ($entries as $entry) {
            $item = $feed->createNewItem();
            $item->setTitle((string)$entry['title']);
            $item->setLink(BlogsModel::getEntryFullUrlByBlogIdAndEntryId($blog['id'], $entry['id']));
            $item->setDate((string)$entry['updated_at']);
            $item->setAuthor((string)$blog['nickname']);
            $item->setDescription((string)$entry['body']);
            $feed->addItem($item);
        }

        // set response
        $this->setContentType($feed->getMIMEType() . "; charset=utf-8");
        $this->output = $feed->generateFeed();
        return "";
    }
}
