<?php
/**
* Model用のインタフェース
*/

interface ModelInterface{

  /**
  * Table名を返却
  */
  public function getTableName();

  /**
  * インスタンスを取得する
  */
  public static function getInstance();

}
