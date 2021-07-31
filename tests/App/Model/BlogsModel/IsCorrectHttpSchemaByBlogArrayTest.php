<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Model\BlogsModel;

use Fc2blog\Model\Blog;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Web\Request;
use PHPUnit\Framework\TestCase;

class IsCorrectHttpSchemaByBlogArrayTest extends TestCase
{
    public function testIsCorrectHttpSchemaByBlogArray(): void
    {
        $request = new Request(
            'GET', '/', null, null, null, null,
            [
                'HTTP_USER_AGENT' => 'phpunit',
                'HTTPS' => "on"
            ]
        );
        $blog = new Blog();
        $blog->ssl_enable = BlogsModel::BLOG['SSL_ENABLE']['DISABLE'];
        $this->assertFalse(BlogsModel::isCorrectHttpSchemaByBlog($request, $blog));
        $request = new Request(
            'GET', '/', null, null, null, null,
            [
                'HTTP_USER_AGENT' => 'phpunit',
            ]
        );
        $blog->ssl_enable = BlogsModel::BLOG['SSL_ENABLE']['DISABLE'];
        $this->assertTrue(BlogsModel::isCorrectHttpSchemaByBlog($request, $blog));

        $request = new Request(
            'GET', '/', null, null, null, null,
            [
                'HTTP_USER_AGENT' => 'phpunit',
                'HTTPS' => "on"
            ]
        );
        $blog->ssl_enable = BlogsModel::BLOG['SSL_ENABLE']['ENABLE'];
        $this->assertTrue(BlogsModel::isCorrectHttpSchemaByBlog($request, $blog));
        $request = new Request(
            'GET', '/', null, null, null, null,
            [
                'HTTP_USER_AGENT' => 'phpunit',
            ]
        );
        $blog->ssl_enable = BlogsModel::BLOG['SSL_ENABLE']['ENABLE'];
        $this->assertFalse(BlogsModel::isCorrectHttpSchemaByBlog($request, $blog));
    }
}
