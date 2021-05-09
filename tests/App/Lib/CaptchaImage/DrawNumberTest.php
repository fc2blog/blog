<?php /** @noinspection PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace Fc2blog\Tests\App\Lib\CaptchaImage;

use Exception;
use Fc2blog\Lib\CaptchaImage;
use GdImage;
use PHPUnit\Framework\TestCase;

class DrawNumberTest extends TestCase
{
    private static $test_output_gif_path = __DIR__ . "/test_output.gif";

    /**
     * @param array $params
     * @param bool $mini_mode
     * @return false|resource gd resource
     * @throws Exception
     */
    private function generateGifImage(array $params, bool $mini_mode = true)
    {
        $captcha = new CaptchaImage($params['size_x'], $params['size_y'], false);

        $is_in_ob = ob_get_level() > 0;
        $before_ob = "";
        if ($is_in_ob) {
            $before_ob = ob_get_clean();
        }

        ob_start();
        if (!defined("TEST_DONT_FLUSH_OUTPUT_BUFFER")) define("TEST_DONT_FLUSH_OUTPUT_BUFFER", true);
        $captcha->drawNumber($params['key'], $mini_mode);
        $gif = ob_get_clean();

        if ($is_in_ob) {
            ob_start();
            echo $before_ob;
        }

        if (file_exists(static::$test_output_gif_path)) {
            unlink(static::$test_output_gif_path);
        }
        $this->assertFileDoesNotExist(static::$test_output_gif_path);

        file_put_contents(static::$test_output_gif_path, $gif);
        $this->assertFileExists(static::$test_output_gif_path);
        $this->assertGreaterThan(1, filesize(static::$test_output_gif_path));

        $gif_resource = imagecreatefromgif(static::$test_output_gif_path);
        $this->assertEquals($params['size_x'], imagesx($gif_resource));
        $this->assertEquals($params['size_y'], imagesy($gif_resource));

        return $gif_resource;
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getDefaultParams(): array
    {
        // CommonController::captcha() よりパラメーターを拝借
        return [
            "key" => random_int(1000, 9999),
            "size_x" => 200,
            "size_y" => 40,
            "isJa" => true, // or false
        ];
    }

    public function testDrawNumber(): void
    {
        $params = $this->getDefaultParams();
        $gif_resource = $this->generateGifImage($params);
        $this->assertInstanceOf(GdImage::class, $gif_resource);
    }

    public function testDrawEnNumber(): void
    {
        $params = $this->getDefaultParams();
        $params['isJa'] = false;
        $gif_resource = $this->generateGifImage($params);
        $this->assertInstanceOf(GdImage::class, $gif_resource);
    }

    public function testDrawOddSize(): void
    {
        $params = $this->getDefaultParams();
        $params['size_x'] = 199;
        $params['size_y'] = 39;
        $gif_resource = $this->generateGifImage($params);
        $this->assertInstanceOf(GdImage::class, $gif_resource);
    }

    public function testDrawToSmall(): void
    {
        $params = $this->getDefaultParams();
        // 以下サイズがロジック上最小
        $params['size_x'] = 8;
        $params['size_y'] = 1;
        $gif_resource = $this->generateGifImage($params);
        $this->assertInstanceOf(GdImage::class, $gif_resource);
        $gif_resource = $this->generateGifImage($params, false);
        $this->assertInstanceOf(GdImage::class, $gif_resource);
    }

    public function testMiniOrNotMiniMode(): void
    {
        $params = $this->getDefaultParams();
        $gif_resource = $this->generateGifImage($params, true);
        $this->assertInstanceOf(GdImage::class, $gif_resource);
        $gif_resource = $this->generateGifImage($params, false);
        $this->assertInstanceOf(GdImage::class, $gif_resource);
    }

}
