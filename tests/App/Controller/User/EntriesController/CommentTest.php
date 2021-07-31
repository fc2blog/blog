<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\User\EntriesController;

use Fc2blog\Exception\RedirectExit;
use Fc2blog\Model\BlogSettingsModel;
use Fc2blog\Model\CommentsModel;
use Fc2blog\Tests\Helper\ClientTrait;
use PHPUnit\Framework\TestCase;

class CommentTest extends TestCase
{
    use ClientTrait;

    public function testAddCommentCheckTrip(): void
    {
        $c = $this->reqGet('/testblog2/?no=1');
        $this->assertStringStartsWith("<!DOCTYPE html", $c->getOutput());
        $this->assertStringContainsString("testblog2", $c->getOutput());

        $c = $this->reqPost("/testblog2/",
            [
                "process" => "comment_regist",
                "comment" => [
                    "no" => "1",
                    "name" => "test_name" . time(),
                    "title" => "test_title" . time(),
                    "mail" => "test@test.com",
                    "url" => "http://example.jp",
                    "body" => "本文",
                    "pass" => "password",
                ]
            ]
        );

        $this->assertStringContainsString("コメントを投稿する", $c->getOutput());

        // Captchaをいれて実投稿

        $this->clientTraitSession['token'] = "1234"; // テスト用に固定
        try {
            $c = $this->reqPost("/testblog2/",
                [
                    "mode" => "Entries",
                    "process" => "comment_regist",
                    "comment" => [
                        "entry_id" => "1",
                        "name" => "test_name" . time(),
                        "title" => "test_title" . time(),
                        "mail" => "test@test.com",
                        "url" => "http://example.jp",
                        "body" => "本文",
                        "password" => "password",
                        "open_status" => "0",
                    ],
                    "token" => "1234"
                ]
            );
            var_dump($c);
            $this->fail();
        } catch (RedirectExit $e) {
            // ok
            $this->assertStringStartsWith("/testblog2/index.php?mode=entries&process=view&id=1", $e->redirectUrl);
        }

        // 対象記事の最新1件コメントを引く
        // Tripが生成され、保存されているかチェック
        $comments_model = new CommentsModel();
        $blog_settings_model = new BlogSettingsModel();
        $blog_id = "testblog2";
        $entry_id = 1;
        $blog_setting = $blog_settings_model->findByBlogId($blog_id);
        $options = $comments_model->getCommentListOptionsByBlogSetting($blog_id, $entry_id, $blog_setting);
        $comments = $comments_model->find('all', $options);
        // var_dump($comments);
        // ※ この箇所はかなり壊れやすい
        $this->assertEquals("s0jeH8Lw", $comments[0]['trip_hash']);

        // Tripがテンプレートにまで表示されているかチェック
        $c = $this->reqGet("/testblog2/index.php?mode=entries&process=view&id=1");
        var_dump($c);
        $this->assertStringContainsString("s0jeH8Lw", $c->getOutput());
    }
}
