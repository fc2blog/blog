<?php
/**
* DB用のインタフェース
*/

interface DBInterface{

  const RESULT_ONE = 'one';                // １カラムのみ取得
  const RESULT_ROW = 'row';                // １行のみ取得
  const RESULT_LIST = 'list';              // リスト形式で取得
  const RESULT_ALL = 'all';                // 全て取得
  const RESULT_STAT = 'statment';          // 結果ステートメントを返却する
  const RESULT_INSERT_ID = 'insert_id';    // AUTO_INCREMENTの値を返却
  const RESULT_AFFECTED = 'affected';      // 変更のあった行数を返却
  const RESULT_SUCCESS = 'success';        // SQLの実行結果が成功かどうかを返却

  public function find($sql, $params=array(), $options=array());
  public function execute($sql, $params=array(), $options=array());
  public function multiExecute($sql);
  public function connect();
  public function close();
  public function getVersion();

}
