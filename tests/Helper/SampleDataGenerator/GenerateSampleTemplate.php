<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Fc2blog\App;
use Fc2blog\Model\BlogTemplatesModel;
use InvalidArgumentException;
use RuntimeException;

class GenerateSampleTemplate
{
    use FakerTrait;
    use RandomUtilTrait;

    public function generateSampleTemplate(string $blog_id, int $num = 10, int $device_type = null): array
    {
        $faker = static::getFaker();
        $template_list = [];

        $blog_templates_model = new BlogTemplatesModel();

        while ($num-- > 0) {
            $device_list = App::DEVICES;

            $template_request = [
                "device_type" => $device_type ?? static::getRandomValue($device_list),
                "title" => $faker->sentence(3),
                "html" => $faker->randomHtml(),
                "css" => "/* this is pseudo css " . $faker->text() . "*/",
            ];

            // 新規登録処理
            $white_list = ['title', 'html', 'css', 'device_type'];
            $errors_blog_template = $blog_templates_model->validate($template_request, $blog_template_data, $white_list);

            if (count($errors_blog_template) > 0) {
                throw new InvalidArgumentException("invalid request:" . print_r($template_request, true));
            }

            $blog_template_data['blog_id'] = $blog_id;

            $id = $blog_templates_model->insert($blog_template_data);

            if ($id === false) {
                throw new RuntimeException("insert failed");
            }

            $template_list[] = $blog_templates_model->findById($id);
        }

        return $template_list;
    }
}
