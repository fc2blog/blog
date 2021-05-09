<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Fc2Template;

use ErrorException;
use Fc2blog\Config;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Web\Request;
use ParseError;
use PHPUnit\Framework\TestCase;
use TypeError;

/**
 * Class Fc2TemplateIfTest
 * FC2TemplateにおいてiF系のタグのレンダリングテスト
 * @package Fc2blog\Tests\App\Fc2Template
 */
class Fc2TemplateIfTest extends TestCase
{
    // TODO 全 fc2_template_ifをチェックできているか担保する仕組み

    public function setUp(): void
    {
        Config::read('fc2_template.php');
        parent::setUp();
    }

    public function test_index_area()
    {
        $this->ifStateTester('index_area', 'ok', ['index_area' => true]);
        $this->ifStateTester('index_area', '', []);
        $this->ifStateTester('not_index_area', 'ok', []);
        $this->ifStateTester('not_index_area', '', ['index_area' => true]);
    }

    public function test_titlelist_area()
    {
        $this->ifStateTester('titlelist_area', 'ok', ['titlelist_area' => true]);
        $this->ifStateTester('titlelist_area', '', []);
        $this->ifStateTester('not_titlelist_area', 'ok', []);
        $this->ifStateTester('not_titlelist_area', '', ['titlelist_area' => true]);
    }

    public function test_date_area()
    {
        $this->ifStateTester('date_area', 'ok', ['date_area' => true]);
        $this->ifStateTester('date_area', '', []);
        $this->ifStateTester('not_date_area', 'ok', []);
        $this->ifStateTester('not_date_area', '', ['date_area' => true]);
    }


    public function test_tag_area()
    {
        $this->ifStateTester('tag_area', 'ok', ['tag_area' => true]);
        $this->ifStateTester('tag_area', '', []);
        $this->ifStateTester('not_tag_area', 'ok', []);
        $this->ifStateTester('not_tag_area', '', ['tag_area' => true]);
    }

    public function test_ctag_exists()
    {
        $this->ifStateTester('ctag_exists', 'ok', ['blog_id' => 'testblog2']);
        $this->ifStateTester('ctag_exists', 'ok', ['t_tags' => ['somethings']]);
        $this->ifStateTester('ctag_exists', '', ['blog_id' => 'unknown blog id']);
    }


    public function test_search_area()
    {
        $this->ifStateTester('search_area', 'ok', ['search_area' => true]);
        $this->ifStateTester('search_area', '', []);
        $this->ifStateTester('not_search_area', 'ok', []);
        $this->ifStateTester('not_search_area', '', ['search_area' => true]);
    }

    public function test_comment_area()
    {
        $this->ifStateTester('comment_area', 'ok', ['comment_area' => true, 'entry' => ['comment_accepted' => Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED')]]);
        $this->ifStateTester('comment_area', '', []);
        $this->ifStateTester('not_comment_area', 'ok', []);
        $this->ifStateTester('not_comment_area', '', ['comment_area' => true]);
    }

    public function test_form_area()
    {
        $this->ifStateTester('form_area', 'ok', ['form_area' => true, 'entry' => ['comment_accepted' => Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED')]]);
        $this->ifStateTester('form_area', '', []);
        $this->ifStateTester('not_form_area', 'ok', []);
        $this->ifStateTester('not_form_area', '', ['form_area' => true]);
    }

    public function test_edit_area()
    {
        $this->ifStateTester('edit_area', 'ok', ['edit_area' => true]);
        $this->ifStateTester('edit_area', '', []);
        $this->ifStateTester('not_edit_area', 'ok', []);
        $this->ifStateTester('not_edit_area', '', ['edit_area' => true]);
    }

    public function test_comment_edit()
    {
        $this->ifStateTester('comment_edit', 'ok', ['comment' => ['password' => 'something']]);
        $this->ifStateTester('comment_edit', '', ['comment' => ['password' => '']]);
        $this->ifStateTester('comment_edit', '', ['comment' => []]);
        $this->ifStateTester('comment_edit', '', []);
    }

    public function test_trackback_area()
    {
        $this->ifStateTester('trackback_area', '', ['trackback_area' => true]);
        $this->ifStateTester('trackback_area', '', []);
        $this->ifStateTester('not_trackback_area', 'ok', []);
        $this->ifStateTester('not_trackback_area', 'ok', ['trackback_area' => true]);
    }

    public function test_permanent_area()
    {
        $this->ifStateTester('permanent_area', 'ok', ['permanent_area' => true]);
        $this->ifStateTester('permanent_area', '', []);
        $this->ifStateTester('not_permanent_area', 'ok', []);
        $this->ifStateTester('not_permanent_area', '', ['permanent_area' => true]);
    }

    public function test_spplugin_area()
    {
        $this->ifStateTester('spplugin_area', 'ok', ['spplugin_area' => true]);
        $this->ifStateTester('spplugin_area', '', []);
        $this->ifStateTester('not_spplugin_area', 'ok', []);
        $this->ifStateTester('not_spplugin_area', '', ['spplugin_area' => true]);
    }

    public function test_relate_list_area()
    {
        $this->ifStateTester('relate_list_area', '', ['relate_list_area' => true]);
        $this->ifStateTester('relate_list_area', '', []);
        $this->ifStateTester('not_relate_list_area', 'ok', []);
        $this->ifStateTester('not_relate_list_area', 'ok', ['relate_list_area' => true]);
    }

    public function test_more_link()
    {
        $this->ifStateTester('more_link', 'ok', ['entry' => ['extend' => "something"]]);
        $this->ifStateTester('more_link', 'ok', ['comment_area' => false, 'entry' => ['extend' => "something"]]);
        $this->ifStateTester('more_link', '', ['comment_area' => true]);
        $this->ifStateTester('more_link', '', []);
    }

    public function test_more()
    {
        $this->ifStateTester('more', 'ok', ['comment_area' => true, 'entry' => ['extend' => "something"]]);
        $this->ifStateTester('more', '', ['comment_area' => false, 'entry' => ['extend' => "something"]]);
        $this->ifStateTester('more', '', ['entry' => ['extend' => "something"]]);
        $this->ifStateTester('more', '', ['comment_area' => true]);
        $this->ifStateTester('more', '', []);
    }

    public function test_allow_comment()
    {
        $this->ifStateTester('allow_comment', 'ok', ['entry' => ['comment_accepted' => Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED')]]);
        $this->ifStateTester('allow_comment', '', ['entry' => ['comment_accepted' => Config::get('ENTRY.COMMENT_ACCEPTED.REJECT')]]);
    }

    public function test_deny_comment()
    {
        $this->ifStateTester('deny_comment', 'ok', ['entry' => ['comment_accepted' => Config::get('ENTRY.COMMENT_ACCEPTED.REJECT')]]);
        $this->ifStateTester('deny_comment', '', ['entry' => ['comment_accepted' => Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED')]]);

    }

    public function test_community()
    {
        $this->ifStateTester('community', '', []);
    }

    public function test_allow_tb()
    {
        $this->ifStateTester('allow_tb', '', []);
    }

    public function test_deny_tb()
    {
        $this->ifStateTester('deny_tb', 'ok', []);
    }

    public function test_comment_reply()
    {
        $this->ifStateTester('comment_reply', 'ok', ['comment' => ['reply_body' => 'something']]);
        $this->ifStateTester('comment_reply', '', ['entry' => ['reply_body' => '']]);
        $this->ifStateTester('comment_reply', '', ['entry' => []]);
        $this->ifStateTester('comment_reply', '', []);
    }

    public function test_topentry_tag()
    {
        $this->ifStateTester('topentry_tag', 'ok', ['entry' => ['tags' => ['something']]]);
        $this->ifStateTester('topentry_tag', '', ['entry' => ['tags' => '']]);
        $this->ifStateTester('topentry_tag', '', ['entry' => []]);
        $this->ifStateTester('topentry_tag', '', []);
    }

    public function test_not_topentry_tag()
    {
        $this->ifStateTester('not_topentry_tag', '', ['entry' => ['tags' => ['something']]]);
        $this->ifStateTester('not_topentry_tag', 'ok', ['entry' => ['tags' => '']]);
        $this->ifStateTester('not_topentry_tag', 'ok', ['entry' => []]);
        $this->ifStateTester('not_topentry_tag', 'ok', []);
    }

    public function test_body_img()
    {
        $this->ifStateTester('body_img', 'ok', ['entry' => ['first_image' => 'something']]);
        $this->ifStateTester('body_img', '', ['entry' => ['first_image' => '']]);
        $this->ifStateTester('body_img', '', ['entry' => []]);
        $this->ifStateTester('body_img', '', []);
    }

    public function test_body_img_none()
    {
        $this->ifStateTester('body_img_none', '', ['entry' => ['first_image' => 'something']]);
        $this->ifStateTester('body_img_none', 'ok', ['entry' => ['first_image' => '']]);
        $this->ifStateTester('body_img_none', 'ok', ['entry' => []]);
        $this->ifStateTester('body_img_none', 'ok', []);
    }

    public function test_category_parent()
    {
        $this->ifStateTester('category_parent', 'ok', ['t_category' => ['is_parent' => true]]);
        $this->ifStateTester('category_parent', '', ['t_category' => ['is_parent' => false]]);
        $this->ifStateTester('category_parent', '', []);
    }

    public function test_category_nosub()
    {
        $this->ifStateTester('category_nosub', 'ok', ['t_category' => ['is_nosub' => true]]);
        $this->ifStateTester('category_nosub', '', ['t_category' => ['is_nosub' => false]]);
        $this->ifStateTester('category_nosub', '', []);
    }

    public function test_category_sub_begin()
    {
        $this->ifStateTester('category_sub_begin', 'ok', ['t_category' => ['is_sub_begin' => true]]);
        $this->ifStateTester('category_sub_begin', '', ['t_category' => ['is_sub_begin' => false]]);
        $this->ifStateTester('category_sub_begin', '', []);
    }

    public function test_category_sub_hasnext()
    {
        $this->ifStateTester('category_sub_hasnext', 'ok', ['t_category' => ['is_sub_hasnext' => true]]);
        $this->ifStateTester('category_sub_hasnext', '', ['t_category' => ['is_sub_hasnext' => false]]);
        $this->ifStateTester('category_sub_hasnext', '', []);
    }

    public function test_category_sub_end()
    {
        $this->ifStateTester('category_sub_end', 'ok', ['t_category' => ['is_sub_end' => true]]);
        $this->ifStateTester('category_sub_end', '', ['t_category' => ['is_sub_end' => false]]);
        $this->ifStateTester('category_sub_end', '', []);
    }

    public function test_plugin()
    {
        $this->ifStateTester('plugin', 'ok', []);
    }

    public function test_spplugin()
    {
        $this->ifStateTester('spplugin', 'ok', []);
    }

    public function test_page_area()
    {
        $this->ifStateTester('page_area', 'ok', ['paging' => ['something']]);
        $this->ifStateTester('page_area', '', []);
    }

    public function test_nextpage()
    {
        $this->ifStateTester('nextpage', 'ok', ['paging' => ['is_next' => true]]);
        $this->ifStateTester('nextpage', '', ['paging' => ['is_next' => false]]);
        $this->ifStateTester('nextpage', '', []);
    }


    public function test_prevpage()
    {
        $this->ifStateTester('prevpage', 'ok', ['paging' => ['is_prev' => true]]);
        $this->ifStateTester('prevpage', '', ['paging' => ['is_prev' => false]]);
        $this->ifStateTester('prevpage', '', []);
    }

    public function test_nextentry()
    {
        $this->ifStateTester('nextentry', 'ok', ['next_entry' => true]);
        $this->ifStateTester('nextentry', '', ['next_entry' => false]);
        $this->ifStateTester('nextentry', '', []);
    }

    public function test_preventry()
    {
        $this->ifStateTester('preventry', 'ok', ['prev_entry' => true]);
        $this->ifStateTester('preventry', '', ['prev_entry' => false]);
        $this->ifStateTester('preventry', '', []);
    }

    public function test_firstpage_disp()
    {
        $this->ifStateTester('firstpage_disp', 'ok', ['paging' => ['is_prev' => true]]);
        $this->ifStateTester('firstpage_disp', '', ['paging' => ['is_prev' => false]]);
        $this->ifStateTester('firstpage_disp', '', []);
    }

    public function test_lastpage_disp()
    {
        $this->ifStateTester('lastpage_disp', 'ok', ['paging' => ['is_next' => true]]);
        $this->ifStateTester('lastpage_disp', '', ['paging' => ['is_next' => false]]);
        $this->ifStateTester('lastpage_disp', '', []);
    }

    public function test_res_nextpage_area()
    {
        $this->ifStateTester('res_nextpage_area', 'ok', ['paging' => ['is_next' => true]]);
        $this->ifStateTester('res_nextpage_area', '', ['paging' => ['is_next' => false]]);
        $this->ifStateTester('res_nextpage_area', '', []);
    }

    public function test_res_prevpage_area()
    {
        $this->ifStateTester('res_prevpage_area', 'ok', ['paging' => ['is_prev' => true]]);
        $this->ifStateTester('res_prevpage_area', '', ['paging' => ['is_prev' => false]]);
        $this->ifStateTester('res_prevpage_area', '', []);
    }

    public function test_ios()
    {
        $iPhone_ua = 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1';
        $req = new Request('GET', '/', [], [], [], [], ['HTTP_USER_AGENT' => $iPhone_ua]);
        $this->ifStateTester('ios', 'ok', ['request' => $req]);

        //ipad
        $iPad_ua = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Safari/605.1.15';
        $req = new Request('GET', '/', [], [], [], [], ['HTTP_USER_AGENT' => $iPad_ua]);
        $this->ifStateTester('ios', '', ['request' => $req]);
    }

    public function test_android()
    {
        $android_ua = 'Mozilla/5.0 (Linux; U; Android 4.0.3; ja-jp; SC-02C Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
        $req = new Request('GET', '/', [], [], [], [], ['HTTP_USER_AGENT' => $android_ua]);
        $this->ifStateTester('android', 'ok', ['request' => $req]);

        $android_ua = 'Mozilla/5.0 (Android; Mobile; rv:21.0) Gecko/21.0 Firefox/21.0';
        $req = new Request('GET', '/', [], [], [], [], ['HTTP_USER_AGENT' => $android_ua]);
        $this->ifStateTester('android', 'ok', ['request' => $req]);

        $android_ua = 'Mozilla/5.0 (Linux; Android 4.0.3; SC-02C Build/IML74K) AppleWebKit/537.31 (KHTML, like Gecko) Chrome/26.0.1410.58 Mobile Safari/537.31';
        $req = new Request('GET', '/', [], [], [], [], ['HTTP_USER_AGENT' => $android_ua]);
        $this->ifStateTester('android', 'ok', ['request' => $req]);
    }

    public function ifStateTester($tag, $expected, $env)
    {
        $input_template = "<!--{$tag}-->ok<!--/{$tag}-->";
        $php_template = $this->convertFc2TemplateToPhpTemplate($input_template);
        $res = $this->evalPhpTemplate($php_template, $env);
        $this->assertEquals($expected, $res);
    }

    public function convertFc2TemplateToPhpTemplate(string $input_template): string
    {
        $b = new BlogTemplatesModel();
        return $b->convertFC2Template($input_template);
    }

    /**
     * PHPのフラグメントをPHPとして評価してみる
     * @param string $php_template
     * @param array $env
     * @return string
     */
    public function evalPhpTemplate(string $php_template, array $env): string
    {
        extract($env);

        $rtn = null;
        try {
            ob_start();
            eval("?>" . $php_template);
            $rtn = ob_get_contents();
            ob_end_clean();
            $this->assertIsString($rtn);
        } /** @noinspection PhpRedundantCatchClauseInspection eval時に発生する可能性がある */ catch (ErrorException $e) {
            $this->fail("exec error `{$php_template}` got {$e->getMessage()}");
        } catch (TypeError $e) {
            $this->fail("type error `{$php_template}` got {$e->getMessage()}");
        } catch (ParseError $e) {
            $this->fail("parse error `{$php_template}` got {$e->getMessage()}");
        }

        return $rtn;
    }
}
