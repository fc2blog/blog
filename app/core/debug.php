<?php
/**
* Debug表示用クラス
*/

class Debug{

  private static $logs = array();

  public static function log($msg, $params=array(), $class='log', $file=null, $line=null){
    switch(Config::get('DEBUG')){
      // Debug文の表示はしない
      default: case 0:
        break;

      // echoでデバッグ文を表示
      case 1:
        echo $msg;
        if (
            is_countable($params)
            && count($params)
        ) {
          echo '<pre>';
          var_dump($params);
          echo '</pre>';
        }
        echo '<hr />';
        break;

      // htmlでデバッグ文を表示
      case 2: case 3:
        self::initLogs();    // ログの初期化
        if (!$file || !$line) {
          $traces = debug_backtrace();    // file,line取得
          $file = $traces[0]['file'];
          $line = $traces[0]['line'];
        }
        self::$logs[] = array(
          'msg'      => $msg,
          'params'  => $params,
          'class'    => $class,
          'file'    => $file,
          'line'    => $line,
          'memory'  => memory_get_usage(),
          'max_memory'  => memory_get_peak_usage(true),
          'time'    => microtime(true) - REQUEST_MICROTIME,
        );
        break;

      // echoでデバッグ文を表示
      case 4:
        echo $msg . "\n";
        if (
            is_countable($params)
            && count($params)
            && !empty($params)
        ) {
          var_dump($params);
          echo "\n";
        }
        break;
    }
  }

  /**
  * ログを取得
  */
  public static function getLogs(){
    self::initLogs();    // ログの初期化
    Session::start();
    $logs = self::$logs;
    return $logs;
  }

  /**
  * ログの初期化
  */
  private static function initLogs(){
    if (!count(self::$logs)) {
      // redirect用にセッションからログを取得
      self::$logs = self::removeSessionLogs();

      // ログの初期値としてURLをログとして追加
      $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
      $url .= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
      $params = array(
        'GET' => $_GET,
        'POST' => $_POST,
      );
      self::$logs[] = array('msg'=>$url, 'params'=>$params, 'class'=>'url');
    }
  }

  /**
  * セッションに現在のログを保存する(ページをまたがったログ用)
  */
  public static function setSessionLogs(){
    if (Config::get('DEBUG')!=2) {
      // DebugをHTMLへ吐き出す以外は処理を行わない
      return ;
    }
    Session::set('debug', self::getLogs());
  }

  /**
  * セッションから前回のログを取得し削除する
  */
  public static function removeSessionLogs(){
    return Session::remove('debug', array());
  }

  /**
  * DebugLogのアウトプットを行う
  */
  public static function output($controller){
    $debug = Config::get('DEBUG');
    if (!($debug==2 || $debug==3)) {
      // htmlでデバッグ以外は何も処理を行わない
      return ;
    }

    Debug::log('Debug::output()', false, 'system', __FILE__, __LINE__);    // 最後の出力処理
    Config::set('DEBUG_TEMPLATE_VARS', 0);  // デバッグ用テンプレートには使用可能変数一覧は非表示

    // logsデータを元にdebug用htmlをfetch
    $html = $controller->fetch('Common/debug.html', array('logs'=>self::getLogs()), false);

    // 10分前以前のファイルは削除
    $cmd = "find " . Config::get('TEMP_DIR') . 'debug_html/' . " -amin +10 -name '*.html' | xargs rm -f";
    system($cmd);

    // fetchしたデータでhtmlを作成
    $key = time() . '.html';      // 後でキー名はセッションIDやログインIDを付与する
    $filePath = Config::get('TEMP_DIR') . 'debug_html/' . $key;
    file_put_contents($filePath, $html);    // 結果をデバッグ用HTMLに書き込み
    chmod($filePath, 0777);

    $url = Html::url(array('controller'=>'common', 'action'=>'debug', 'key'=> $key));
    if ($debug==2) {
      // iframeで表示
      echo <<<HTML
<iframe id="sys-debug-iframe" src="{$url}" style="border: 2px solid #000; width: 99%;margin: 0 auto; display: block;"></iframe>
HTML;
    }else{
      // linkで表示
      echo <<<HTML
<p style="border: 2px solid black;text-align:center; background-color: #fff; color: #000;">
  <a href="{$url}" target="_blank">{$url}</a>
</p>
HTML;
    }
  }

}

