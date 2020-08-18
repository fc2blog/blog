<?php

namespace Fc2blog\Web\Controller\User;

use Exception;
use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Debug;
use Fc2blog\Lib\CaptchaImage;
use Fc2blog\Lib\ThumbnailImageMaker;
use Fc2blog\Web\Cookie;
use Fc2blog\Web\Request;
use RuntimeException;

class CommonController extends UserController
{

  /**
   * 言語設定変更
   * @param Request $request
   */
  public function lang(Request $request)
  {
    // 言語の設定
    $lang = $request->get('lang');
    if ($language= Config::get('LANGUAGES.' . $lang)) {
      Cookie::set('lang', $lang);
    }

    // 元のURLに戻す
    $this->redirectBack('/');
  }

  /**
   * デバイス変更
   * @param Request $request
   */
  public function device_change(Request $request)
  {
    // 言語の設定
    $device_type = 0;
    $device = $request->get('device');
    switch ($device) {
      case 'pc': $device_type = Config::get('DEVICE_PC'); break;
      case 'sp': $device_type = Config::get('DEVICE_SP'); break;
      default:
        Cookie::set('device', null);
        $this->redirectBack(array('controller'=>'entries', 'action'=>'index', 'blog_id'=>$this->getBlogId($request)));
    }

    Cookie::set('device', $device_type);
    $this->redirectBack(array('controller'=>'entries', 'action'=>'index', 'blog_id'=>$this->getBlogId($request)));
  }

  /**
   * 画像認証
   * @param Request $request
   */
  public function captcha(Request $request)
  {
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
    $this->setToken($key);    // トークン設定
    $isJa = Config::get('LANG')=='ja'; // 日本語以外は数字のみを表示
    $captcha = new CaptchaImage($size_x, $size_y, Config::get('PASSWORD_SALT'), $isJa);
    try {
      $captcha->drawNumber($key, true);
    } catch (Exception $e) {
      throw new RuntimeException("drawNumber failed. {$e->getMessage()} {$e->getFile()}:{$e->getLine()}");
    }

    Config::set('DEBUG', 0);
    $this->layout = 'none.php';
  }

  /**
   * サムネイル処理
   * @param Request $request
   * @return string
   */
  public function thumbnail(Request $request)
  {
    $blog_id = $request->get('blog_id');
    $id      = $request->get('id');
    $ext     = $request->get('ext');
    $size    = $request->get('size');
    $whs     = $request->get('whs', 's');
    $width   = $request->get('width');
    $height  = $request->get('height');
    $file = array(
      'blog_id' => $blog_id,
      'id'      => $id,
      'ext'     => $ext,
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
        if ($size!=72) {
          return $this->error404();
        }
        break;
    }

    // サムネイル出力処理
    $image = new ThumbnailImageMaker();
    $load_result = $image->load($file_path);
    if($load_result!==true){
        Debug::log('Load image fail[' . $file_path . ']', false, 'error', __FILE__, __LINE__);
        return $this->error404();
    }
    switch ($whs) {
      default:
        $whs = '';
        $resize_result = $image->resize($size, $size, false);
        break;
      case 'w': $resize_result = $image->resizeToWidth($size, false);  break;
      case 'h': $resize_result = $image->resizeToHeight($size, false); break;
      case 'wh':
        $resize_result = $image->resizeToWidthInCenter($width, $height,false);
        break;
    }
    if($resize_result!==true){
      Debug::log('Resize thumbnail image fail[' . $file_path . ']', false, 'error', __FILE__, __LINE__);
      return $this->error404();
    }

    preg_match('{^(.*?)\.(png|gif|jpe?g)$}', $file_path, $matches);
    if ($whs === 'wh') {
      $save_file = $matches[1] . '_' . $whs . $width . '_' . $height . '.' . $matches[2];
    } else {
      $save_file = $matches[1] . '_' . $whs . $size . '.' . $matches[2];
    }
    $save_result = $image->save($save_file, $image->image_type, 90);
    if($save_result!==true){
      Debug::log('Save thumbnail image fail[' . $file_path . ']', false, 'error', __FILE__, __LINE__);
      return $this->error404();
    }
    chmod($save_file, 0777);

    // 作成したファイルへリダイレクト
    $this->redirect($request, $request->getPath() . '?' . $request->getQuery());
  }
}

