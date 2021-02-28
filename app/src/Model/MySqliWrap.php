<?php
/**
* DB接続クラス
* mysqliのラッピング
*/

namespace Fc2blog\Model;

class MySqliWrap implements DBInterface{

  // DB情報
  private $host = null;
  private $port = null;
  private $user = null;
  private $password = null;
  private $database = null;
  private $charset  = null;

  // DBオブジェクト
  private $db = null;

  function __construct($host, $port, $user, $password, $database, $charset){
    $this->host = $host;
    $this->port = $port;
    $this->user = $user;
    $this->password = $password;
    $this->database = $database;
    $this->charset  = $charset;
  }

  /**
   * 参照系SQL
   * @param string $sql
   * @param array $params
   * @param array $options
   * @return array|false|int|mixed
   */
  public function find(string $sql, array $params=[], array $options=[]){
    $_options = array(
      'types'  => '',                  // paramsの型設定(sdi)
      'result' => \Fc2blog\Model\DBInterface::RESULT_ALL,    // 戻り値 one/row/all/statement...
    );
    $options = array_merge($_options, $options);
    try{
      $stmt = $this->query($sql, $params, $options['types']);
    }catch(\Exception $e){
      if (\Fc2blog\Config::get('SQL_DEBUG', 0)) {
        \Fc2blog\Util\Log::old_log($e->getMessage(), $params, 'error', __FILE__, __LINE__);
      }
      return false;
    }
    return $this->result($stmt, $options['result']);
  }

  /**
   * 更新系SQL
   * @param string $sql
   * @param array $params
   * @param array $options
   * @return array|false|int|mixed
   */
  public function execute(string $sql, array $params=[], array $options=[]){
    $_options = array(
      'types' => '',                      // paramsの型設定(sdi)
      'result' => \Fc2blog\Model\DBInterface::RESULT_SUCCESS,    // 戻り値 one/row/all/statement...
    );
    $options = array_merge($_options, $options);
    try{
      $stmt = $this->query($sql, $params, $options['types']);
    }catch(\Exception $e){
      if (\Fc2blog\Config::get('SQL_DEBUG', 0)) {
        \Fc2blog\Util\Log::old_log($e->getMessage(), $params, 'error', __FILE__, __LINE__);
      }
      return false;
    }
    return $this->result($stmt, $options['result']);
  }

  /**
   * 複数の更新系SQL
   */
  public function multiExecute($sql)
  {
    $sql = preg_replace('/^--.*?\n/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    $sqls = explode(';', $sql);
    $execute_sqls = array();
    foreach ($sqls as $sql) {
      if (trim($sql) !== '') {
        $execute_sqls[] = $sql;
      }
    }

    foreach ($execute_sqls as $sql) {
      if($this->execute($sql) === false) {
        return false;
      }
    }
    return true;
  }

  /**
   * SQLの実行
   * @throws \Exception
   */
  private function query($sql, $params=array(), $types=''){
    $mtime = 0;    // SQL実行結果時間取得用
    if (\Fc2blog\Config::get('SQL_DEBUG', 0)) {
      $mtime = microtime(true);
    }

    $this->connect();

    if (!count($params)) {
      // SQL文をそのまま実行
      $stmt = $this->db->query($sql);
      if (getType($stmt) == 'boolean' && !$stmt) {
        throw new \Exception('[query Error]' . print_r($this->db->error, true) .$sql);
      }
      if (\Fc2blog\Config::get('SQL_DEBUG', 0)) {
        $mtime = sprintf('%0.2fms', (microtime(true) - $mtime)*1000);
        \Fc2blog\Util\Log::old_log('実行時間：' . $mtime . ' ' . $sql, $params, 'sql', __FILE__, __LINE__);
      }
      return $stmt;
    }

    // プリペアドステートメント
    $stmt = $this->db->prepare($sql);
    if (!$stmt) {
      throw new \Exception('[prepare Error]' . $sql);
    }
    $types = count($params) == strlen($types) ? $types : $this->getBindTypes($params);
    $bindParams = array($types);
    foreach($params as $key => $value){
      $bindParams[] = &$params[$key];
    }
    $res = call_user_func_array(array($stmt, 'bind_param'), $bindParams);
    if (!$stmt->execute()) {
      throw new \Exception('[execute Error]' . $sql);
    }
    if (\Fc2blog\Config::get('SQL_DEBUG', 0)) {
      $mtime = sprintf('%0.2fms', (microtime(true) - $mtime)*1000);
      \Fc2blog\Util\Log::old_log('実行時間：' . $mtime . ' ' . $sql, $params, 'sql', __FILE__, __LINE__);
    }
    return $stmt;
  }

  public function connect($is_charset=true, $is_database=true){
    // TODO Show Warning message on connect failed. but mysqli throw error, not exception...
    if ($this->db == null) {
      if ($is_database) {
        $this->db = new \mysqli($this->host, $this->user, $this->password, $this->database, (int)$this->port);
      } else {
        $this->db = new \mysqli($this->host, $this->user, $this->password, null, (int)$this->port);
      }
      $this->errorCheck('Connect Error');
      if ($is_charset) {
        $this->db->set_charset($this->charset);
      }
    }
  }

  public function close(){
    if ($this->db != null) {
      $this->db->close();
      $this->db = null;
    }
  }

  private function errorCheck($msg){
    if (mysqli_connect_error()) {
      throw new \Exception('['.$msg . '] (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
    }
/*
    // PHP 5.2.9 および 5.3.0 より前のバージョンでは $connect_error は動作していない
    if ($this->db->connect_errno) {
      throw new \Exception('['.$msg . '] (' . $this->db->connect_errno . ') ' . $this->db->connect_error);
    }
*/
  }

  private function getBindTypes($params){
    $types = "";
    foreach($params as $key => $value){
      $type = getType($value);
      switch($type){
        default:
          $type = "string";
        case 'boolean': case 'integer': case 'double': case 'string':
          $types .= $type[0];
      }
    }
    return $types;
  }

  public function result($stmt, $type){
    switch($type){
      // １カラムのみ取得
      case \Fc2blog\Model\DBInterface::RESULT_ONE :
        $one = null;
        switch (get_class($stmt)) {
          case 'mysqli_stmt':
            $one = $this->fetchstatement($stmt, 'one');
            $stmt->close();
            break;

          case 'mysqli_result':
            $row = $stmt->fetch_row();
            $one = $row[0];
            $stmt->close();
            break;
        }
        $one = empty($one) ? 0 : $one;
        return $one;

      // １行のみ取得
      case \Fc2blog\Model\DBInterface::RESULT_ROW :
        $row = array();
        switch (get_class($stmt)) {
          case 'mysqli_stmt':
            $row = $this->fetchstatement($stmt, 'row');
            $stmt->close();
            break;

          case 'mysqli_result':
            $row = $stmt->fetch_assoc();
            $stmt->close();
            break;
        }
        return $row;

      // リスト形式で取得
      case \Fc2blog\Model\DBInterface::RESULT_LIST :
        $rows = array();
        switch (get_class($stmt)) {
          case 'mysqli_stmt':
            $rows = $this->fetchstatement($stmt, 'list');
            $stmt->close();
            break;

          case 'mysqli_result':
            while ($row = $stmt->fetch_row()) {
              $rows[$row[0]] = $row[1];
            }
            $stmt->close();
            break;
        }
        return $rows;


      // 全て取得
      case \Fc2blog\Model\DBInterface::RESULT_ALL :
        $rows = array();
        switch (get_class($stmt)) {
          case 'mysqli_stmt':
            $rows = $this->fetchstatement($stmt);
            $stmt->close();
            break;

          case 'mysqli_result':
            while ($row = $stmt->fetch_assoc()) {
              $rows[] = $row;
            }
            $stmt->close();
            break;
        }
        return $rows;

      // InsertIDを返却
      case \Fc2blog\Model\DBInterface::RESULT_INSERT_ID :
        $insert_id = $this->db->insert_id;
        if (getType($stmt) === 'object') {
          $stmt->close();
        }
        return $insert_id;

      // 影響のあった行数を返却
      case \Fc2blog\Model\DBInterface::RESULT_AFFECTED :
        $affected_rows = $this->db->affected_rows;
        if (getType($stmt) === 'object') {
          $stmt->close();
        }
        return $affected_rows;

      // 成功したかどうかを返却
      case \Fc2blog\Model\DBInterface::RESULT_SUCCESS :
        return 1;

      case \Fc2blog\Model\DBInterface::RESULT_STAT: default:
        return $stmt;
    }
  }

  /**
   * mysqli_statement用のfetch
   */
  private function fetchstatement(&$stmt, $type='all'){
    $hits = array();
    $params = array();

    if (!$stmt->store_result()) {
      throw new \Exception('[store_result] error=[' . $stmt->error . ']');
    }
    $meta = $stmt->result_metadata();
    while ($field = $meta->fetch_field()) {
      $params[] = &$row[$field->name];
    }
    call_user_func_array(array($stmt, 'bind_result'), $params);
    while ($stmt->fetch()) {
      if ($type=='one') {
        return array_shift($row);
      }
      $c = array();
      foreach($row as $key => $val) {
        $c[$key] = $val;
      }
      if ($type=='row') {
        return $c;
      }
      if ($type=='list') {
        $hits[array_shift($c)] = array_shift($c);
      }else{
        $hits[] = $c;
      }
    }
    return $hits;
  }

  public function getVersion()
  {
    $this->connect(false, false);
    $version = explode('-', $this->db->server_info);
    return $version[0];
  }
}
