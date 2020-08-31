<?php
/**
 * アプリ用のControllerの親クラス
 */

namespace Fc2blog\Web\Controller;

use Fc2blog\App;
use Fc2blog\Config;
use Fc2blog\Exception\PseudoExit;
use Fc2blog\Model\Model;
use Fc2blog\Web\Html;
use Fc2blog\Web\Request;
use Fc2blog\Web\Session;

abstract class AppController extends Controller
{

  /**
   * 出力結果を装飾
   */
  protected function beforeRender()
  {
    $html = $this->output;

    // cssとjsを置き換える
    $css_html = Html::getCSSHtml();
    $js_html = Html::getJSHtml();

    // cssとjsを置き換え
    $html = str_replace(array($this->includeCSS(), $this->includeJS()), array($css_html, $js_html), $html);

    // 編集後のHTMLを出力結果に代入
    $this->output = $html;
  }

  /**
   * CSSの置き換え引数
   */
  public function includeCSS()
  {
    return '<!-- ' . "\xFF" . 'CSS_INCLUDE -->';
  }

  /**
   * Javascriptの置き換え引数
   */
  public function includeJS()
  {
    return '<!-- ' . "\xFF" . 'JS_INCLUDE -->';
  }

  /**
   * blog_idからブログ情報を取得
   * @param $blog_id
   * @return
   */
  public function getBlog($blog_id)
  {
    return Model::load('Blogs')->findById($blog_id);
  }

  /**
   * デバイスタイプを取得する
   */
  protected function getDeviceType()
  {
    return Config::get('DeviceType');
  }

  /**
   * token発行
   * @param null $key
   * @param string $name
   */
  protected function setToken($key = null, $name = 'token')
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
