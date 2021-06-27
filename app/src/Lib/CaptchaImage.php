<?php /** @noinspection SpellCheckingInspection */

/**
 * 文字認証クラス
 */

namespace Fc2blog\Lib;

use Exception;

class CaptchaImage
{
    var $img_size_x;
    var $img_size_y;
    var $hirakana_mode;

    /**
     * CaptchaImage constructor.
     * @param $src_img_size_x 8 以上が必要
     * @param $src_img_size_y 1 以上が必要
     * @param bool $hirakana_mode
     */
    public function __construct($src_img_size_x, $src_img_size_y, bool $hirakana_mode = true)
    {
        $this->img_size_x = $src_img_size_x;
        $this->img_size_y = $src_img_size_y;
        $this->hirakana_mode = $hirakana_mode;
    }

    /**
     * Captcha Imageを描画し、送信
     * @param $number
     * @param bool $mini_mode
     * @throws Exception
     */
    public function drawNumber($number, bool $mini_mode = false): void
    {
        //memo. sjisの書体はサーバー環境によっては使えない
        $arr_fonts = array(
            array("path" => dirname(__FILE__) . "/fonts/ume-ugo4.ttf", "code" => "UTF-8", "min" => 10, "max" => 14),  //梅フォント
        );

        $hirakana = array(
            "0" => "ぜろ",
            "1" => "いち",
            "2" => "に",
            "3" => "さん",
            "4" => "よん",
            "5" => "ゴ", //"ご", ← 判別むずかしいため
            "6" => "ろく",
            "7" => "なな",
            "8" => "はち",
            "9" => "きゅう",
        );

        $katakana = array(
            "0" => "ゼロ",
            "1" => "イチ",
            "2" => "に",//"ニ", ← 判別むずかしいため
            "3" => "サン",
            "4" => "ヨン",
            "5" => "ゴ",
            "6" => "ロク",
            "7" => "ナナ",
            "8" => "ハチ",
            "9" => "キュウ",
        );

        $tmp_str = sprintf("%d", $number);
        $im = imagecreatetruecolor($this->img_size_x, $this->img_size_y);

        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 0, 0, 0);

        // 背景の描画
        imagealphablending($im, true);
        imagefilledrectangle($im, 0, 0, $this->img_size_x, $this->img_size_y, $white);

        // 文字の描写
        imagealphablending($im, true);

        //フォント種類
        $fid = random_int(0, count($arr_fonts) - 1);

        $cur_x = random_int(10, 60); // 描画する文字の左開始位置
        $length = strlen($tmp_str);
        for ($i = 0; $i < $length; $i++) {
            if ($this->hirakana_mode) {
                //描画文字
                if (random_int(0, 1)) {
                    $char = $hirakana[$tmp_str[$i]];//ひらかな
                } else {
                    $char = $katakana[$tmp_str[$i]];//カタカナ
                }
            } else {
                //日本語は使わず数字だけの場合
                $char = $tmp_str[$i];
            }

            $angle = random_int(-10, 10);// 角度
            $font1 = $arr_fonts[$fid]["path"];
            $code = $arr_fonts[$fid]["code"];

            //フォントサイズ
            if (mb_strlen($char, 'UTF-8') > 2) {
                $font_size = $arr_fonts[$fid]["min"];
            } else {
                $font_size = random_int($arr_fonts[$fid]["min"] * 10, $arr_fonts[$fid]["max"] * 10) / 6;
            }

            if ($mini_mode) $font_size = $font_size * 0.80;

            //フォントの文字コードにあわせる
            if ($code != "UTF-8") {
                $char = mb_convert_encoding($char, $code, "UTF-8");
            }
            $gd_info = gd_info();
            if (!empty($gd_info['JIS-mapped Japanese Font Support'])) {
                $char = mb_convert_encoding($char, "SJIS-win", "UTF-8");
            }
            $arr_bbox = imagettfbbox($font_size, $angle, $font1, $char);
            $x1 = $arr_bbox[0] < $arr_bbox[6] ? $arr_bbox[0] : $arr_bbox[6];
            $y1 = $arr_bbox[5] < $arr_bbox[7] ? $arr_bbox[5] : $arr_bbox[7];
            $x2 = $arr_bbox[2] > $arr_bbox[4] ? $arr_bbox[2] : $arr_bbox[4];

            imagettftext($im, $font_size, $angle, ($cur_x - $x1) + 1, (0 - $y1) + 5, $black, $font1, $char);
            $cur_x += $x2 + random_int(0, 8);
            $tmp_pace = ($this->img_size_x / $length) * ($i + 1);
            if ($tmp_pace > $cur_x) $cur_x += random_int(0, ($tmp_pace - $cur_x));
        }

        // 可読性を下げる効果
        $im2 = imagecreatetruecolor($this->img_size_x, $this->img_size_y);

        // 背景色
        $bg_r = random_int(200, 255);
        $bg_g = random_int(200, 255);
        $bg_b = random_int(200, 255);
        $bg_color = imagecolorallocate($im2, $bg_r, $bg_g, $bg_b);

        // フォント色
        $fg_r = random_int(0, 100);
        $fg_g = random_int(0, 100);
        $fg_b = random_int(0, 100);
        $fg_color = imagecolorallocate($im2, $fg_r, $fg_g, $fg_b);

        imagefilledrectangle($im2, 0, 0, $this->img_size_x, $this->img_size_y, $bg_color);
        imagefilledrectangle($im2, 0, $this->img_size_y, $this->img_size_x, $this->img_size_y, $fg_color);
        $center = $this->img_size_x / 2;

        // periods
        $rand1 = random_int(750000, 1200000) / 10000000;
        $rand2 = random_int(750000, 1200000) / 10000000;
        $rand3 = random_int(750000, 1200000) / 10000000;
        $rand4 = random_int(750000, 1200000) / 10000000;
        // phases
        $rand5 = random_int(0, 31415926) / 10000000;
        $rand6 = random_int(0, 31415926) / 10000000;
        $rand7 = random_int(0, 31415926) / 10000000;
        $rand8 = random_int(0, 31415926) / 10000000;
        // amplitudes
        $rand9 = random_int(330, 420) / 110;
        $rand10 = random_int(330, 450) / 110;

        // 歪み処理
        for ($x = 0; $x < $this->img_size_x; $x++) {
            for ($y = 0; $y < $this->img_size_y; $y++) {
                $sx = $x + (sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6)) * $rand9 - $this->img_size_x / 2 + $center + 1;
                $sy = $y + (sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8)) * $rand10;

                if (($sx < 0) || ($sy < 0) || ($sx >= $this->img_size_x - 1) || ($sy >= $this->img_size_y - 1)) {
                    continue;
                } else {
                    $color = imagecolorat($im, $sx, $sy) & 0xFF;
                    $color_x = imagecolorat($im, $sx + 1, $sy) & 0xFF;
                    $color_y = imagecolorat($im, $sx, $sy + 1) & 0xFF;
                    $color_xy = imagecolorat($im, $sx + 1, $sy + 1) & 0xFF;
                }

                if (($color == 255) && ($color_x == 255) && ($color_y == 255) && ($color_xy == 255)) {
                    continue;
                } else if (($color == 0) && ($color_x == 0) && ($color_y == 0) && ($color_xy == 0)) {
                    $newred = $fg_r;
                    $newgreen = $fg_g;
                    $newblue = $fg_b;
                } else {
                    $frsx = $sx - floor($sx);
                    $frsy = $sy - floor($sy);
                    $frsx1 = 1 - $frsx;
                    $frsy1 = 1 - $frsy;

                    $newcolor = ($color * $frsx1 * $frsy1 + $color_x * $frsx * $frsy1 + $color_y * $frsx1 * $frsy + $color_xy * $frsx * $frsy);

                    if ($newcolor > 255) $newcolor = 255;
                    $newcolor = $newcolor / 255;
                    $newcolor0 = 1 - $newcolor;

                    $newred = $newcolor0 * $fg_r + $newcolor * $bg_r;
                    $newgreen = $newcolor0 * $fg_g + $newcolor * $bg_g;
                    $newblue = $newcolor0 * $fg_b + $newcolor * $bg_b;
                }

                imagesetpixel($im2, $x, $y, imagecolorallocate($im2, $newred, $newgreen, $newblue));
            }
        }

        // 背景のノイズラインの描画
        for ($i = 0; $i < 4; $i++) {
            $col = imagecolorallocate($im2, random_int(99, 188), random_int(99, 188), random_int(99, 188));
            imageline($im2, random_int(4, $this->img_size_x - 1), 4, random_int(4, $this->img_size_x - 4), $this->img_size_y - 4, $col);
        }

        if (!$mini_mode) {//背景色で横ダミーライン
            imageline($im2, 0, random_int(0, ($this->img_size_y - 1)), ($this->img_size_x - 1), random_int(0, ($this->img_size_y - 1)), $bg_color);
            imageline($im2, random_int(0, $this->img_size_x - 1), 0, random_int(0, $this->img_size_x / 2), $this->img_size_y - 1, $bg_color);
            imageline($im2, random_int(0, $this->img_size_x / 2), 0, random_int(0, $this->img_size_x - 1), $this->img_size_y - 1, $bg_color);
        }

        // ノイズドット
        for ($i = 0; $i < 12; $i++) {
            $col = ImageColorAllocate($im2, random_int(80, 220), random_int(80, 220), random_int(80, 220));
            for ($j = 0; $j < 12; $j++) {
                imagesetpixel($im2, random_int(1, $this->img_size_x), random_int(1, $this->img_size_y), $col);
            }
        }

        if (!headers_sent()) { # UnitTestで受け取るため
            header("Content-type: image/gif");
        }

        imagegif($im2);
        imagedestroy($im2);
    }
}
