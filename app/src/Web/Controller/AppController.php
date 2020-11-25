<?php
/**
 * アプリ用のControllerの親クラス
 */

namespace Fc2blog\Web\Controller;

use Fc2blog\App;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

abstract class AppController extends Controller
{

  /**
   * 出力結果を後処理置換
   * @param string $html
   * @return string
   */
  protected function afterFilter(string $html): string
  {
    // cssとjsを置き換える
    $css_html = Html::getCSSHtml();
    $js_html = Html::getJSHtml();

    // cssとjsを置き換え
    $html = str_replace([$this->includeCSS(), $this->includeJS()], [$css_html, $js_html], $html);

    return $html;
  }

  /**
   * CSSの置き換え引数
   * @return string
   */
  public function includeCSS(): string
  {
    return '<!-- ' . "\xFF" . 'CSS_INCLUDE -->';
  }

  /**
   * Javascriptの置き換え引数
   * @return string
   */
  public function includeJS(): string
  {
    return '<!-- ' . "\xFF" . 'JS_INCLUDE -->';
  }

  /**
   * blog_idからブログ情報を取得
   * @param $blog_id
   * @return array|false|mixed
   */
  public function getBlog($blog_id)
  {
    return (new BlogsModel())->findById($blog_id);
  }

  /**
   * デバイスタイプを取得する
   * @return string
   */
  protected function getDeviceType(): string
  {
    return $this->request->deviceType;
  }

  /**
   * token発行
   * @param null $key
   * @param string $name
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
   */
  protected function tokenValidate(Request $request, $name = 'token')
  {
    $value = $request->get($name, '');
    $value = mb_convert_kana($value, 'n');
    return Session::remove($name) == $value ? null : __('Token authentication is invalid');
  }
}
