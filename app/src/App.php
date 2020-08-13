<?php
/**
* アプリ用の便利関数群
*/

namespace Fc2blog;

use Exception;
use InvalidArgumentException;

class App
{

  /**
  * ブログIDから階層別フォルダ作成
  */
  public static function getBlogLayer($blog_id)
  {
    return $blog_id[0] . '/' . $blog_id[1] . '/' . $blog_id[2] . '/' . $blog_id;
  }

  /**
  * ユーザーのアップロードしたファイルパスを返す
  */
  public static function getUserFilePath($file, $abs=false, $timestamp=false)
  {
    $file_path = static::getBlogLayer($file['blog_id']) . '/file/' . $file['id'] . '.' . $file['ext'];
    return ($abs ? Config::get('WWW_UPLOAD_DIR') : '/' . Config::get('UPLOAD_DIR_NAME') . '/') . $file_path . ($timestamp ? '?t=' . strtotime($file['updated_at']) : '');
  }

  /**
  * サムネイル画像のパスを返却する
  * 対象外の場合は元のパスを返却する
  */
  public static function getThumbnailPath($url, $size=72, $whs=''){
    if (empty($url)) {
      return $url;
    }
    if (!preg_match('{(/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]+/file/[0-9]+)\.(png|gif|jpe?g)(\?t=[0-9]+)?$}', $url, $matches)) {
      return $url;
    }
    return $matches[1] . '_' . $whs . $size . '.' . $matches[2] . (isset($matches[3]) ? $matches[3] : '');
  }

  /**
   * 中央切り抜きのサムネイル画像のパスを返却する
   * 対象外の場合は元のパスを返却する
   */
  public static function getCenterThumbnailPath($url, $width=760, $heght=420, $whs=''){
    if (empty($url)) {
      return $url;
    }
    if (!preg_match('{(/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]+/file/[0-9]+)\.(png|gif|jpe?g)(\?t=[0-9]+)?$}', $url, $matches)) {
      return $url;
    }
    return $matches[1] . '_' . $whs . $width . '_' . $heght. '.' . $matches[2] . (isset($matches[3]) ? $matches[3] : '');
  }

  /**
  * ブログIDとIDに紐づくファイルを削除する
  */
  public static function deleteFile($blog_id, $id){
    $dir_path = Config::get('WWW_UPLOAD_DIR') . static::getBlogLayer($blog_id) . '/file/';
    $files = scandir($dir_path);
    foreach ($files as $file_name) {
      if (strpos($file_name, $id . '_') === 0) {
        // サムネイル用ファイルの削除
        unlink($dir_path . $file_name);
      }
      if (strpos($file_name, $id . '.') === 0) {
        // オリジナルファイル削除
        unlink($dir_path . $file_name);
      }
    }
  }

  /**
  * プラグインへのファイルパス
  */
  public static function getPluginFilePath($blog_id, $id)
  {
    return Config::get('BLOG_TEMPLATE_DIR') . static::getBlogLayer($blog_id) . '/plugins/' . $id . '.php';
  }

  /**
  * ファイルパスまでのフォルダを作成する
  */
  public static function mkdir($file_path)
  {
    $folder_dir = dirname($file_path);
    if (!file_exists($folder_dir)) {
      mkdir($folder_dir, 0777, true);
    }
  }

  /**
  * ブログディレクトリを削除
  */
  public static function removeBlogDirectory($blog_id)
  {
    $upload_path = Config::get('WWW_UPLOAD_DIR') . '/' .  static::getBlogLayer($blog_id);
    system("rm -fr " . $upload_path);

    $template_path = Config::get('BLOG_TEMPLATE_DIR') . static::getBlogLayer($blog_id);
    system("rm -fr " . $template_path);
  }

  /**
  * 開始日と終了日を計算する
  * 存在しない日付の場合は本日として解釈する
  */
  public static function calcStartAndEndDate($year=0, $month=0, $day=0)
  {
    if (!$year) {
      // 年が存在しない場合本日を開始、終了日時として割り当てる
      return array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'));
    }
    $start = $end = $year . '-';
    if ($month) {
      $start .= $month . '-';
      $end   .= $month . '-';
      if ($day) {
        $start .= $day;
        $end   .= $day;
      } else {
        $start .= '01';
        $end   .= date('t', mktime(0, 0, 0, $month, 1, $year));
      }
    } else {
      $start .= '01-01';
      $end   .= '12-31';
    }
    $dates = explode('-', $start);
    if (!checkdate($dates[1], $dates[2], $dates[0])) {
      // 存在日付の場合は本日を開始、終了日時として割り当てる
      $start = $end = date('Y-m-d');
    }
    $start .= ' 00:00:00';
    $end   .= ' 23:59:59';
    return array($start, $end);
  }

  /**
  * デバイスタイプを取得する
  */
  public static function getDeviceType()
  {
    // パラメータによりデバイスタイプを変更(FC2の引数順守)
    $request = Request::getInstance();
    if ($request->isArgs('pc')) {
      return Config::get('DEVICE_PC');
    }
    if ($request->isArgs('sp')) {
      return Config::get('DEVICE_SP');
    }
    if ($request->isArgs('tb')) {
      return Config::get('DEVICE_TB');
    }
    if ($request->isArgs('m')) {
      return Config::get('DEVICE_MB');
    }

    // Cookieからデバイスタイプを取得
    $device_type = Cookie::get('device');
    $devices = array(Config::get('DEVICE_PC'), Config::get('DEVICE_MB'), Config::get('DEVICE_SP'), Config::get('DEVICE_TB'));
    if (!empty($device_type) && in_array($device_type, $devices)) {
      return $device_type;
    }

    // ユーザーエージェントからデバイスタイプを取得
    $ua = $_SERVER['HTTP_USER_AGENT'];

    $devices = array('iPhone', 'iPod', 'Android');
    foreach ($devices as $device) {
      if (strpos($ua, $device)!==false) {
        return Config::get('DEVICE_SP');
      }
    }
    return Config::get('DEVICE_PC');
  }

  /**
  * 現在のデバイスタイプをPC,MB,SP,TBの形で取得する
  */
  public static function getDeviceKey()
  {
    $device_type = self::getDeviceType();
    switch ($device_type) {
      default:
      case 1: return 'PC';
      case 2: return 'MB';
      case 4: return 'SP';
      case 8: return 'TB';
    }
  }

  /**
  * 引数のデバイスタイプを取得する
  */
  public static function getArgsDevice(){
    static $device_name = null;   // 良く使用するのでキャッシュ
    if ($device_name===null) {
      $request = Request::getInstance();
      if ($request->isArgs('pc')) {
        $device_name = 'pc';
      } else if ($request->isArgs('sp')) {
        $device_name = 'sp';
      } else if ($request->isArgs('tb')) {
        $device_name = 'tb';
      } else if ($request->isArgs('m')) {
        $device_name = 'm';
      } else {
        $device_name = '';
      }
    }
    return $device_name;
  }

  /**
  * IOSかどうかを判定
  */
  public static function isIOS()
  {
    return self::isSP() && strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')!==false;
  }

  /**
  * Androidかどうかを判定
  */
  public static function isAndroid()
  {
    return self::isSP() && strpos($_SERVER['HTTP_USER_AGENT'], 'Android')!==false;
  }

  /**
  * PC環境下どうかを調べる
  */
  public static function isPC()
  {
    return Config::get('DeviceType') == Config::get('DEVICE_PC');
  }

  /**
  * SP環境下どうかを調べる
  */
  public static function isSP()
  {
    return Config::get('DeviceType') == Config::get('DEVICE_SP');
  }

  /**
  * ユーザー画面用のURL
  */
  public static function userURL($args=array(), $reused=false, $abs=false)
  {
    // 現在のURLの引数を引き継ぐ
    if ($reused==true) {
      $gets = Request::getInstance()->getGet();
      unset($gets[Config::get('ARGS_CONTROLLER')]);
      unset($gets[Config::get('ARGS_ACTION')]);
      $args = array_merge($gets, $args);
    }

    $controller = Config::get('ControllerName');
    if (isset($args['controller'])) {
      $controller = $args['controller'];
      unset($args['controller']);
    }

    $action = Config::get('ActionName');
    if (isset($args['action'])) {
      $action = $args['action'];
      unset($args['action']);
    }

    // BlogIdを先頭に付与する
    $blog_id = null;
    if (isset($args['blog_id'])) {
      $blog_id = $args['blog_id'];
      unset($args['blog_id']);
    }

    // 引数のデバイスタイプを取得
    $device_name = self::getArgsDevice();
    if (!empty($device_name) && isset($args[$device_name])) {
      unset($args[$device_name]);
    }

    // 絶対パスが必要な際に、フルのホスト名を取得する
    $full_domain = ($abs) ? \BlogsModel::getFullHostUrlByBlogId($blog_id) : "";

    // TOPページの場合
    if (strtolower($controller)=='entries' && strtolower($action)=='index' && !empty($blog_id)) {
      $url = '/';

      $params = array();
      foreach($args as $key => $value){
        $params[] = $key . '=' . $value;
      }
      if (!empty($device_name)) {
        $params[] = $device_name;
      }
      if (count($params)) {
        $url .= '?' . implode('&', $params);
      }
      if ($blog_id) {
        $url = '/' . $blog_id . $url;
      }
      $url = ($abs ? $full_domain : '') . $url;
      return $url;
    }

    // 固定記事の場合
    if (strtolower($controller)=='entries' && strtolower($action)=='view' && !empty($args['id'])) {
//      $url = '/blog-entry-' . $args['id'] . '.html';
      $url = '/?no=' . $args['id'];
      unset($args['id']);

      $params = array();
      foreach($args as $key => $value){
        $params[] = $key . '=' . $value;
      }
      if (!empty($device_name)) {
        $params[] = $device_name;
      }
      if (count($params)) {
        $url .= '&' . implode('&', $params);
      }
      if ($blog_id) {
        $url = '/' . $blog_id . $url;
      }
      $url = ($abs ? $full_domain : '') . $url;
      return $url;
    }

    $params = array();
    $params[] = Config::get('ARGS_CONTROLLER') . '=' . lcfirst($controller);
    $params[] = Config::get('ARGS_ACTION') . '=' . $action;
    foreach($args as $key => $value){
      $params[] = $key . '=' . $value;
    }
    if (!empty($device_name)) {
      $params[] = $device_name;
    }

    $url = '/'. Config::get('DIRECTORY_INDEX');
    if (count($params)) {
      $url .= '?' . implode('&', $params);
    }
    if ($blog_id) {
      $url = '/' . $blog_id . $url;
    }
    $url = ($abs ? $full_domain : '') . $url;
    return $url;
  }

  /**
  * ページ毎、デバイス毎の初期制限件数
  */
  public static function getPageLimit($key)
  {
    return Config::get('PAGE.' . $key . '.' . self::getDeviceKey() . '.LIMIT', Config::get('PAGE.' . $key . '.DEFAULT.LIMIT', 10));
  }

  /**
  * ページ毎、デバイス毎の件数一覧
  */
  public static function getPageList($key)
  {
    return Config::get('PAGE.' . $key . '.' . self::getDeviceKey() . '.LIST', Config::get('PAGE.' . $key . '.DEFAULT.LIST', array()));
  }

  /**
  * 現在選択中のメニューかどうかを返す
  * @param params = array('entries/create', 'entries/edit', ...),
  */
  public static function isActiveMenu($params)
  {
    // コントローラー名とメソッド名を取得
    static $controller_name = null;
    static $method_name = null;
    if ($controller_name==null) {
      $controller_name = snakeCase(Config::get('ControllerName'));
      $method_name = snakeCase(Config::get('ActionName'));
    }

    if (is_string($params)) {
      $params = array($params);
    }

    // コントローラー名とメソッド名を判定
    foreach ($params as $value) {
      list($c_name, $m_name) = explode('/', $value);
      if (lcfirst($c_name) != $controller_name) {
        continue ;
      }
      if (!empty($m_name) && snakeCase($m_name) != $method_name) {
        continue ;
      }
      return true;
    }
    return false;
  }

  /**
   * 指定の文字配列から、指定長のランダム文字列を生成する
   * @param int $length 0文字以上の要求文字数
   * @param string $charList 1文字以上のUTF-8文字列、ただし合成文字はサポートしない
   * @return string
   * @throws Exception
   */
  public static function genRandomString(int $length = 16, string $charList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ012345679_-'): string
  {
    if ($length < 0) throw new InvalidArgumentException('must be $length 0 or more');
    if (mb_strlen($charList) <= 0) throw new InvalidArgumentException('must be $charList length more than 0');

    $charList = preg_split("//u", $charList, 0, PREG_SPLIT_NO_EMPTY);
    $charListLen = count($charList);
    $str = '';
    for ($i = 0; $i < $length; $i++) {
      $str .= $charList[random_int(0, $charListLen - 1)];
    }
    return $str;
  }

  /**
   * a-zA-Z0-9 範囲から、指定長のランダム文字列を生成する
   * @param int $length
   * @return string
   * @throws Exception
   */
  public static function genRandomStringAlphaNum(int $length = 32): string
  {
    return static::genRandomString($length, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ012345679');
  }
}
