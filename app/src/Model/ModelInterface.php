<?php
/**
 * Model用のインタフェース
 */

namespace Fc2blog\Model;

interface ModelInterface
{

  /**
   * Table名を返却
   */
  public function getTableName(): string;

  /**
   * インスタンスを取得する
   */
  public static function getInstance();

}
