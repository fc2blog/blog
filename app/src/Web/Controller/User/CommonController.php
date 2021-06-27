<?php
declare(strict_types=1);

namespace Fc2blog\Web\Controller\User;

use Exception;
use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Lib\CaptchaImage;
use Fc2blog\Lib\ThumbnailImageMaker;
use Fc2blog\Util\Log;
use Fc2blog\Web\Cookie;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;
use RuntimeException;

class CommonController extends UserController
{

    /**
     * 言語設定変更
     * @param Request $request
     * @return string
     */
    public function lang(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // 言語の設定
        $lang = $request->get('lang');
        if (Config::get('LANGUAGES.' . $lang)) {
            Cookie::set($request, 'lang', $lang);
        }

        // 元のURLに戻す
        $this->redirectBack($request, '/');
        return "";
    }

    /**
     * デバイス変更
     * @param Request $request
     * @return string
     */
    public function device_change(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        // デバイスの設定
        $device_type = 0;
        $device = $request->get('device');
        switch ($device) {
            case 'pc':
                $device_type = App::DEVICE_PC;
                break;
            case 'sp':
                $device_type = App::DEVICE_SP;
                break;
            default:
                Cookie::set($request, 'device', (string)App::DEVICE_PC);
                $this->redirectBack($request, array('controller' => 'entries', 'action' => 'index', 'blog_id' => $request->getBlogId()));
        }

        Cookie::set($request, 'device', (string)$device_type);
        $this->redirectBack($request, array('controller' => 'entries', 'action' => 'index', 'blog_id' => $request->getBlogId()));
        return "";
    }

    const CAPTCHA_TOKEN_KEY_NAME = 'token';

    /**
     * 画像認証
     * @param Request $request
     * @return string
     */
    public function captcha(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $size_x = 200;
        $size_y = 40;
        // 自動テスト用に"DEBUG_FORCE_CAPTCHA_KEY"環境変数で、Captchaキーの固定機能
        if (strlen((string)getenv("DEBUG_FORCE_CAPTCHA_KEY")) === 4) {
            $key = (int)getenv("DEBUG_FORCE_CAPTCHA_KEY");
        } else {
            try {
                $key = random_int(1000, 9999);
            } catch (Exception $e) {
                throw new RuntimeException("random_int thrown exception {$e->getMessage()}");
            }
        }
        Session::set(self::CAPTCHA_TOKEN_KEY_NAME, $key); // トークン設定

        // captchaの日本語モード判定
        // Cookieでlangがja以外は英語モード
        // Cookieに指定がなければ、Accept Languageヘッダーを参照し、一番がjaでなければ英語モード
        // どちらにもかからなければ、Config設定を優先する
        if (isset($request->cookie['lang']) && $request->cookie['lang'] !== 'ja') {
            $isJa = false;
        } elseif (isset($request->server['HTTP_ACCEPT_LANGUAGE']) && preg_match('/\Aja/ui', $request->server['HTTP_ACCEPT_LANGUAGE'])) {
            $isJa = false;
        } else {
            $isJa = Config::get('LANG') == 'ja'; // 日本語以外は数字のみを表示
        }
        $captcha = new CaptchaImage($size_x, $size_y, $isJa);
        try {
            $captcha->drawNumber($key, true); // ここでデータ送信済み
        } catch (Exception $e) {
            throw new RuntimeException("drawNumber failed. {$e->getMessage()} {$e->getFile()}:{$e->getLine()}");
        }
        return "";
    }

    /**
     * Captcha token有効性チェック
     * @param Request $request
     * @return bool
     */
    public static function isValidCaptcha(Request $request): bool
    {
        $value = $request->get(CommonController::CAPTCHA_TOKEN_KEY_NAME, '');
        $value = mb_convert_kana($value, 'n');
        return (string)Session::remove(CommonController::CAPTCHA_TOKEN_KEY_NAME) === $value;
    }

    /**
     * サムネイル処理
     * @param Request $request
     * @return string
     */
    public function thumbnail(Request $request): string
    {
        if (!$request->isGet()) return $this->error400();

        $blog_id = $request->get('blog_id');
        $id = $request->get('id');
        $ext = $request->get('ext');
        $size = $request->get('size');
        $whs = $request->get('whs', 's');
        $width = $request->get('width');
        $height = $request->get('height');
        $file = array(
            'blog_id' => $blog_id,
            'id' => $id,
            'ext' => $ext,
        );
        $file_path = App::getUserFilePath($file, true);
        if (!file_exists($file_path)) {
            return $this->error404();
        }

        // GDが入っていない場合
        if (!function_exists('gd_info')) {
            return $this->error404();
        }

        // FC2規定サムネイルサイズ制限(72x72、width=300, 400, 600、760×420のみ対応)
        switch ($whs) {
            case 'h':
                return $this->error404();
            case 'w':
                if (!in_array($size, array(300, 400, 600))) {
                    return $this->error404();
                }
                break;
            case 'wh':
                if ($width != 760 || $height != 420) {
                    return $this->error404();
                }
                break;
            default:
                if ($size != 72) {
                    return $this->error404();
                }
                break;
        }

        // サムネイル出力処理
        $image = new ThumbnailImageMaker();
        $load_result = $image->load($file_path);
        if ($load_result !== true) {
            Log::error(__FILE__ . ":" . __LINE__ . " " . 'Load image fail[' . $file_path . ']');
            return $this->error404();
        }
        switch ($whs) {
            default:
                $whs = '';
                $resize_result = $image->resize($size, $size, false);
                break;
            case 'w':
                $resize_result = $image->resizeToWidth($size, false);
                break;
            case 'h':
                $resize_result = $image->resizeToHeight($size, false);
                break;
            case 'wh':
                $resize_result = $image->resizeToWidthInCenter($width, $height, false);
                break;
        }
        if ($resize_result !== true) {
            Log::error(__FILE__ . ":" . __LINE__ . " " . 'Resize thumbnail image fail[' . $file_path . ']');
            return $this->error404();
        }

        preg_match('{^(.*?)\.(png|gif|jpe?g)$}', $file_path, $matches);
        if ($whs === 'wh') {
            $save_file = $matches[1] . '_' . $whs . $width . '_' . $height . '.' . $matches[2];
        } else {
            $save_file = $matches[1] . '_' . $whs . $size . '.' . $matches[2];
        }
        $save_result = $image->save($save_file, $image->image_type, 90);
        if ($save_result !== true) {
            Log::error(__FILE__ . ":" . __LINE__ . " " . 'Save thumbnail image fail[' . $file_path . ']');
            return $this->error404();
        }
        chmod($save_file, 0777);

        // 作成したファイルへリダイレクト
        $this->redirect($request, $request->getPath() . '?' . $request->getQuery());
        return "";
    }
}

