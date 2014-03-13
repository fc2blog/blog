<?php
/**
* Master/Slaveの切り替え用
* 基本的にMySqliWrapと同じ機能を有する
*/

require(Config::get('CORE_DIR') . 'db_interface.php');

if (DB_CONNECT_LIB==='PDO') {
  require(Config::get('CORE_DIR') . 'pdo_wrap.php');
}
if (DB_CONNECT_LIB==='mysqli') {
  require(Config::get('CORE_DIR') . 'my_sqli_wrap.php');
}

class MSDB implements DBInterface{

  // master/slave
  private $master = null;
  private $slave = null;

  // singleton pattern
  private static $instance = null;
  private function __construct(){}
  public static function getInstance(){
    if (self::$instance === null) {
      self::$instance = new MSDB();
    }
    return self::$instance;
  }

  private function getMasterDB(){
    if(!$this->master){
      if (DB_CONNECT_LIB==='PDO') {
        $this->master = new PDOWrap(
          Config::get('MASTER_DB.HOST'),
          Config::get('MASTER_DB.USER'),
          Config::get('MASTER_DB.PASSWORD'),
          Config::get('MASTER_DB.DATABASE'),
          Config::get('DB_CHARSET')
        );
      }
      if (DB_CONNECT_LIB==='mysqli') {
        $this->master = new MySqliWrap(
          Config::get('MASTER_DB.HOST'),
          Config::get('MASTER_DB.USER'),
          Config::get('MASTER_DB.PASSWORD'),
          Config::get('MASTER_DB.DATABASE'),
          Config::get('DB_CHARSET')
        );
      }
    }
    return $this->master;
  }

  private function getSlaveDB(){
    if(!$this->slave){
      if (DB_CONNECT_LIB==='PDO') {
        $this->slave = new PDOWrap(
          Config::get('SLAVE_DB.HOST'),
          Config::get('SLAVE_DB.USER'),
          Config::get('SLAVE_DB.PASSWORD'),
          Config::get('SLAVE_DB.DATABASE'),
          Config::get('DB_CHARSET')
        );
      }
      if (DB_CONNECT_LIB==='mysqli') {
        $this->slave = new MySqliWrap(
          Config::get('SLAVE_DB.HOST'),
          Config::get('SLAVE_DB.USER'),
          Config::get('SLAVE_DB.PASSWORD'),
          Config::get('SLAVE_DB.DATABASE'),
          Config::get('DB_CHARSET')
        );
      }
    }
    return $this->slave;
  }

  public function getDB($isMaster=false){
    // Master/Slave機能のON/OFF
    if(!Config::get('IS_MASTER_SLAVE', true)){
      return $this->getMasterDB();
    }
    return $isMaster ? $this->getMasterDB() : $this->getSlaveDB();
  }

  public function close(){
    if($this->master){
      $this->master->close();
      $this->master = null;
    }
    if($this->slave){
      $this->slave->close();
      $this->slave = null;
    }
  }

  /**
   * 参照系SQL
   */
  public function find($sql, $params=array(), $options=array()){
    $_options = array(
      'master' => false,                  // Masterから取得するかどうか
      'types' => '',                      // paramsの型設定(sdi)
      'result' => DBInterface::RESULT_ALL,  // 戻り値 one/row/all/statment...
    );
    $options = array_merge($_options, $options);
    $db = $this->getDB($options['master']);
    return $db->find($sql, $params, $options);
  }

  /**
   * 更新系SQL
   */
  public function execute($sql, $params=array(), $options=array()){
    $_options = array(
      'types' => '',                            // paramsの型設定(sdi)
      'result' => DBInterface::RESULT_AFFECTED,  // 戻り値 one/row/all/statment...
    );
    $options = array_merge($_options, $options);
    $db = $this->getMasterDB();
    return $db->execute($sql, $params, $options);
  }

  /**
  * 複数の更新系SQL
  */
  public function multiExecute($sql)
  {
    return $this->getDB()->multiExecute($sql);
  }

  /**
  * 接続処理
  */
  public function connect($is_charset=true, $is_database=true){
    $this->getDB()->connect($is_charset, $is_database);
  }

  /**
  * MySQLのバージョンを取得する
  */
  public function getVersion()
  {
    return $this->getDB()->getVersion();
  }

}

