<?php
declare(strict_types=1);

namespace Fc2blog\Tests\App\Controller\Admin\BlogTemplates;

use Fc2blog\Model\BlogsModel;
use Fc2blog\Model\BlogTemplatesModel;
use Fc2blog\Tests\DBHelper;
use Fc2blog\Tests\Helper\ClientTrait;
use Fc2blog\Tests\Helper\SampleDataGenerator\GenerateSampleTemplate;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use PHPUnit\Framework\TestCase;

class ApplyTest extends TestCase
{
    use ClientTrait;

    public function setUp(): void
    {
        DBHelper::clearDbAndInsertFixture();

        $gst = new GenerateSampleTemplate();
        $gst->generateSampleTemplate("testblog2", 1, 1);
        $gst->generateSampleTemplate("testblog2", 1, 4);

        parent::setUp();
    }

    public function testApply(): void
    {
        Session::destroy(new Request());
        $this->resetSession();
        $this->resetCookie();
        $this->mergeAdminSession();
        $sig = $this->getSig();

        $bm = new BlogsModel();
        $blog = $bm->findById('testblog2');
//    var_export($blog);

        $before_pc_template_id = $blog['template_pc_id'];
        $before_sp_template_id = $blog['template_sp_id'];

        $tm = new BlogTemplatesModel();
        $pc_templates = $tm->getTemplatesOfDevice('testblog2', 1)[1];
        $sp_templates = $tm->getTemplatesOfDevice('testblog2', 4)[4];
//    var_dump($pc_templates);
//    var_dump($sp_templates);

        $new_pc_template = null;
        foreach ($pc_templates as $pc_template) {
            if ($pc_template['id'] !== $before_pc_template_id) {
                $new_pc_template = $pc_template;
                break;
            }
        }
//    var_dump($new_pc_template);

        $new_sp_template = null;
        foreach ($sp_templates as $sp_template) {
            if ($sp_template['id'] !== $before_sp_template_id) {
                $new_sp_template = $sp_template;
                break;
            }
        }

        $this->assertNotEquals($before_pc_template_id, $new_pc_template['id']);
        $this->assertNotEquals($before_sp_template_id, $new_sp_template['id']);

        $r = $this->reqGetBeRedirect("/admin/blog_templates/apply", [
            'id' => $new_pc_template['id'],
            'sig' => $sig
        ]);
        $this->assertEquals('/admin/blog_templates/index', $r->redirectUrl);

        $fm = $this->getFlashMessages();
        $this->assertFalse($fm['is_error']);
        $this->assertFalse($fm['is_warn']);
        $this->assertTrue($fm['is_info']);

        $r = $this->reqGetBeRedirect("/admin/blog_templates/apply", [
            'id' => $new_sp_template['id'],
            'sig' => $sig
        ]);
        $this->assertEquals('/admin/blog_templates/index', $r->redirectUrl);

        $fm = $this->getFlashMessages();
        $this->assertFalse($fm['is_error']);
        $this->assertFalse($fm['is_warn']);
        $this->assertTrue($fm['is_info']);

        $blog = $bm->findById('testblog2');

        $this->assertEquals($new_pc_template['id'], $blog['template_pc_id']);
        $this->assertEquals($new_sp_template['id'], $blog['template_sp_id']);
    }
}
