<?php

require_once(Config::get('CONTROLLER_DIR') . 'user/user_controller.php');

class CommonController extends UserController
{

  /**
  * 言語設定変更
  */
  public function lang()
  {
    $request = Request::getInstance();

    // 言語の設定
    $lang = $request->get('lang');
    if ($language=Config::get('LANGUAGES.' . $lang)) {
      Cookie::set('lang', $lang);
    }

    // 元のURLに戻す
    $this->redirectBack('/');
  }

  /**
  * デバイス変更
  */
  public function device_change()
  {
    $request = Request::getInstance();

    // 言語の設定
    $device_type = 0;
    $device = $request->get('device');
    switch ($device) {
      case 'pc': $device_type = Config::get('DEVICE_PC'); break;
      case 'm':
      case 'mb': $device_type = Config::get('DEVICE_MB'); break;
      case 'sp': $device_type = Config::get('DEVICE_SP'); break;
      case 'tb': $device_type = Config::get('DEVICE_TB'); break;
      default:
        Cookie::set('device', null);
        $this->redirectBack(array('controller'=>'entries', 'action'=>'index', 'blog_id'=>$this->getBlogId()));
    }

    Cookie::set('device', $device_type);
    $this->redirectBack(array('controller'=>'entries', 'action'=>'index', 'blog_id'=>$this->getBlogId()));
  }

  /**
  * 画像認証
  */
  public function captcha()
  {
    require_once(Config::get('LIB_DIR') . 'CaptchaImage.php');
    $size_x = 200;
    $size_y = 40;
    $key = rand(1000, 9999);
    $this->setToken($key);    // トークン設定
    $isJa = Config::get('LANG')=='ja'; // 日本語以外は数字のみを表示
    $captcha = new CaptchaImage($size_x, $size_y, Config::get('PASSWORD_SALT'), $isJa);
    $captcha->drawNumber($key, true);

    Config::set('DEBUG', 0);
    $this->layout = 'none.html';
  }

  /**
  * サムネイル処理
  */
  public function thumbnail()
  {
    $request = Request::getInstance();

    $blog_id = $request->get('blog_id');
    $id      = $request->get('id');
    $ext     = $request->get('ext');
    $size    = $request->get('size');
    $whs     = $request->get('whs', 's');

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

    // FC2規定サムネイルサイズ制限(72x72,width=300, 400, 600のみ対応)
    switch ($whs) {
      case 'h':
        return $this->error404();
      case 'w':
        if (!in_array($size, array(300, 400, 600))) {
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
    include(Config::get('LIB_DIR') . 'ThumbnailImageMaker.php');
    $image = new ThumbnailImageMaker();
    $image->load($file_path);
    switch ($whs) {
      default:
        $whs = '';
        $image->resize($size, $size, false);
        break;
      case 'w': $image->resizeToWidth($size, false);  break;
      case 'h': $image->resizeToHeight($size, false); break;
    }
    preg_match('{^(.*?)\.(png|gif|jpe?g)$}', $file_path, $matches);
    $save_file = $matches[1] . '_' . $whs . $size . '.' . $matches[2];
    $image->save($save_file, $image->image_type, 90);
    chmod($save_file, 0777);

    // 作成したファイルへリダイレクト
    $this->redirect($request->getPath() . '?' . $request->getQuery());
  }

}

