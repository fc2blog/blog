<?php
declare(strict_types=1);

namespace Fc2blog\Service;

use Fc2blog\Model\Blog;
use Fc2blog\Model\BlogsModel;

class BlogService
{
    public static function getById(string $blog_id): ?Blog
    {
        $repo = new BlogsModel();
        $res = $repo->findById($blog_id);
        if ($res === false) return null;
        return Blog::factory($res);
    }
}
