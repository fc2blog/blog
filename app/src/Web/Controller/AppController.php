<?php
/**
 * アプリ用のControllerの親クラス
 */

namespace Fc2blog\Web\Controller;

use Fc2blog\App;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

abstract class AppController extends Controller
{
  /**
   * blog_idからブログ情報を取得
   * @param $blog_id
   * @return array|false|mixed
   * @deprecated TODO Modelに移動するべき
   */
  public function getBlog($blog_id)
  {
    return (new BlogsModel())->findById($blog_id);
  }

  /**
   * デバイスタイプを取得する
   * @return string
   * @deprecated TODO requestに置換できる
   */
  protected function getDeviceType(): string
  {
    return $this->request->deviceType;
  }

  /**
   * token発行
   * @param null $key
   * @param string $name
   * TODO captchaでしかつかっていないので、名前をかえるべき
   */
  protected function setToken($key = null, $name = 'token'): void
  {
    if ($key === null) {
      // 適当な値をトークンに設定
      $key = App::genRandomStringAlphaNum(32);
    }
    Session::set($name, $key);
  }

  /**
   * tokenチェック
   * @param Request $request
   * @param string $name
   * @return string|null
   * TODO captchaでしかつかっていないので、名前をかえるべき
   */
  protected function tokenValidate(Request $request, $name = 'token')
  {
    $value = $request->get($name, '');
    $value = mb_convert_kana($value, 'n');
    return Session::remove($name) == $value ? null : __('Token authentication is invalid');
  }
}
