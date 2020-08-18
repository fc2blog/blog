<?php
/**
 * DB用のインタフェース
 */

namespace Fc2blog\Model;

interface DBInterface
{

  const RESULT_ONE = 'one';                // １カラムのみ取得
  const RESULT_ROW = 'row';                // １行のみ取得
  const RESULT_LIST = 'list';              // リスト形式で取得
  const RESULT_ALL = 'all';                // 全て取得
  const RESULT_STAT = 'statment';          // 結果ステートメントを返却する
  const RESULT_INSERT_ID = 'insert_id';    // AUTO_INCREMENTの値を返却
  const RESULT_AFFECTED = 'affected';      // 変更のあった行数を返却
  const RESULT_SUCCESS = 'success';        // SQLの実行結果が成功かどうかを返却

  public function find(string $sql, array $params = [], array $options = []);

  public function execute(string $sql, array $params = [], array $options = []);

  public function multiExecute(string $sql);

  public function connect();

  public function close();

  public function getVersion();

}
