<?php
/**
* ControllerとModelの中間ぐらいの位置(ActiveRecordではありません)
* Sql群の書き出しクラス 子クラスでSingletonで呼び出し予定
*/

namespace Fc2blog\Model;

abstract class Model implements \Fc2blog\Model\ModelInterface
{
  const LIKE_WILDCARD = '\\_%'; // MySQL用

  private static $loaded = array();
  public $validates = array();

  /**
  * 複合キーのAutoIncrement対応
  */
  protected function getAutoIncrementCompositeKey()
  {
    return false;
  }

  public function getDB()
  {
    return \Fc2blog\Model\MSDB::getInstance();
  }

  public function close()
  {
    $db = $this->getDB();
    $db->close();
  }

  /**
  * 入力チェック処理
  * @param array $data 入力データ
  * @param array &$valid_data 入力チェック後の返却データ
  * @param array $white_list 入力のチェック許可リスト
  */
  public function validate($data, &$valid_data, $white_list=array())
  {
    $errors = array();
    $valid_data = array();

    $isWhiteList = !!count($white_list);
    foreach ($this->validates as $key => $valid) {
      // カラムのホワイトリストチェック
      if ($isWhiteList && !in_array($key, $white_list)) {
        continue ;
      }
      foreach ($valid as $mKey => $options) {
        $method = is_array($options) && isset($options['rule']) ? $options['rule'] : $mKey;
        if (!isset($data[$key])) {
          $data[$key] = null;
        }
        $error = \Fc2blog\Model\Validate::$method($data[$key], $options, $key, $data, $this);
        if ($error === false) {
          break;
        }
        if (getType($error) === 'string') {
          $errors[$key] = $error;
          break;
        }
      }
      if (isset($data[$key])) {
        $valid_data[$key] = $data[$key];
      }
    }

    return $errors;
  }

  /**
   * Modelをロードする(requireとgetInstance)
   * @param string $model
   * @return mixed
   */
  public static function load(string $model)
  {
    $model = "\\Fc2blog\\Model\\" . $model . 'Model';
    if (empty(self::$loaded[$model])) {
      self::$loaded[$model] = new $model;
    }
    return self::$loaded[$model];
  }


  /**
  * LIKE検索用にワイルドカードのエスケープ
  */
  public static function escape_wildcard($str)
  {
    return addcslashes($str, self::LIKE_WILDCARD);
  }


  /**
  * 配列内が全て数値型かチェック
  */
  public function is_numeric_array($array)
  {
    // 配列チェック
    if (!is_array($array)) {
      return false;
    }
    // 空配列チェック
    if (!count($array)) {
      return false;
    }
    // 数値チェック
    foreach ($array as $value) {
      if (!is_numeric($value)) {
        return false;
      }
    }
    return true;
  }

  public function find($type, $options=array())
  {
    if (!isset($options['options'])) {
      $options['options'] = array();
    }
    if (!isset($options['params'])) {
      $options['params'] = array();
    }
    switch($type){
      case 'count':
        $options['fields'] = 'COUNT(*)';
        $options['limit'] = 1;
        $options['options']['result'] = \Fc2blog\Model\DBInterface::RESULT_ONE;
        break;
      case 'one':
        $options['limit'] = 1;
        $options['options']['result'] = \Fc2blog\Model\DBInterface::RESULT_ONE;
        break;
      case 'row':
        $options['limit'] = 1;
        $options['options']['result'] = \Fc2blog\Model\DBInterface::RESULT_ROW;
        break;
      case 'list':
        $options['options']['result'] = \Fc2blog\Model\DBInterface::RESULT_LIST;
        break;
      case 'all':
        $options['options']['result'] = \Fc2blog\Model\DBInterface::RESULT_ALL;
        break;
      case 'statment': default:
        $options['options']['result'] = \Fc2blog\Model\DBInterface::RESULT_STAT;
        break;
    }
    $fields = '*';
    if (isset($options['fields'])) {
      $fields = is_array($options['fields']) ? implode(',', $options['fields']) : $options['fields'];
    }
    if (!empty($options['limit']) && isset($options['page'])) {
      $fields = 'SQL_CALC_FOUND_ROWS ' . $fields;
    }
    $sql = 'SELECT ' . $fields . ' FROM ' . $this->getTableName();
    if (!empty($options['from'])) {
      if (is_array($options['from'])) {
        $sql .= ', ' . implode(',', $options['from']);
      } else {
        $sql .= ', ' . $options['from'];
      }
    }
    if (isset($options['where']) && $options['where']!="") {
      $sql .= ' WHERE ' . $options['where'];
    }
    if (isset($options['group']) && $options['group']!="") {
      $sql .= ' GROUP BY ' . $options['group'];
    }
    if (isset($options['order']) && $options['order']!="") {
      $sql .= ' ORDER BY ' . $options['order'];
    }
    if (!empty($options['limit'])) {
      $sql .= ' LIMIT ' . $options['limit'];
      if (isset($options['page'])) {
        $sql .= ' OFFSET ' . $options['limit'] * $options['page'];
      } else if (isset($options['offset'])) {
        $sql .= ' OFFSET ' . $options['offset'];
      }
    }
    return $this->findSql($sql, $options['params'], $options['options']);
  }

  /**
  * 主キーをキーにしてデータを取得
  */
  public function findById($id, $options=array())
  {
    if (empty($id)) {
      return array();
    }
    $options['where'] = isset($options['where']) ? 'id=? AND ' . $options['where'] : 'id=?';
    $options['params'] = isset($options['params']) ? array_merge(array($id), $options['params']) : array($id);
    return $this->find('row', $options);
  }

  /**
  * idとblog_idの複合キーからデータを取得
  */
  public function findByIdAndBlogId($id, $blog_id, $options=array())
  {
    if (empty($id) || empty($blog_id)) {
      return array();
    }
    $options['where'] = isset($options['where']) ? 'blog_id=? AND id=? AND ' . $options['where'] : 'blog_id=? AND id=?';
    $options['params'] = isset($options['params']) ? array_merge(array($blog_id, $id), $options['params']) : array($blog_id, $id);
    return $this->find('row', $options);
  }

  /**
  * idとuser_idのキーからデータを取得
  */
  public function findByIdAndUserId($id, $user_id, $options=array())
  {
    if (empty($id) || empty($user_id)) {
      return array();
    }
    $options['where'] = isset($options['where']) ? 'id=? AND user_id=? AND ' . $options['where'] : 'id=? AND user_id=?';
    $options['params'] = isset($options['params']) ? array_merge(array($id, $user_id), $options['params']) : array($id, $user_id);
    return $this->find('row', $options);
  }

  /**
  * 存在するかどうかを取得
  */
  public function isExist($options=array())
  {
    return !!$this->find('row', $options);
  }

  /**
  * ページング用のデータ取得
  */
  public function getPaging($options=array())
  {
    if (!isset($options['page']) || !isset($options['limit'])) {
      \Fc2blog\Debug::log('getPaging options["page"] or options["limit"]が設定されておりません', array(), 'error');
      return array();
    }

    $page = $options['page'];
    $limit = $options['limit'];

//    $options['fields'] = 'COUNT(*)';
//    unset($options['page'], $options['offset'], $options['order']);    // countにpage,offset,orderは必要ないのでunset
//    $count = $this->find('one', $options);
    $count = $this->getFoundRows();

    $pages = array();
    $pages['count'] = $count;
    $pages['max_page'] = ceil($count / $limit);
    $pages['page'] = $page;
    $pages['is_next'] = $page < $pages['max_page'] - 1;
    $pages['is_prev'] = $page > 0;
    return $pages;
  }

  /**
  * SQL_CALC_FOUND_ROWSで見つかった件数を返却する
  */
  public function getFoundRows()
  {
    $sql = 'SELECT FOUND_ROWS()';
    return $this->findSql($sql, array(), array('result'=>\Fc2blog\Model\DBInterface::RESULT_ONE));
  }

  /**
  * ページングのリストを表示する
  */
  public static function getPageList($paging)
  {
    $pages = array();
    $pages[0] = '1' . __(' page');
    for ($i = 1; $i < $paging['max_page']; $i++) {
      $pages[$i] = ($i+1) . __(' page');
    }
    return $pages;
  }

  public function findSql($sql, $params=array(), $options=array())
  {
    return $this->getDB()->find($sql, $params, $options);
  }

  public function insert($values, $options=array())
  {
    if (!count($values)) {
      return 0;
    }
    $tableName = $this->getTableName();
    $compositeKey = $this->getAutoIncrementCompositeKey();
    if ($compositeKey && empty($values['id']) && !empty($values[$compositeKey])) {
      // 複合キーのauto_increment対応
      $sql = 'INSERT INTO ' . $tableName . ' (id, ' . implode(',', array_keys($values)) . ') '
           . 'VALUES ((SELECT LAST_INSERT_ID(COALESCE(MAX(id), 0)+1) FROM ' . $tableName . ' as auto_increment_temp '
           . 'WHERE ' . $compositeKey . '=?), ' . implode(',', array_fill(0, count($values), '?')) . ')';
      $value = $values[$compositeKey];
      $values = array_values($values);
      array_unshift($values, $value);
    }else{
      // 通常のINSERT
      $sql = 'INSERT INTO ' . $tableName . ' (' . implode(',', array_keys($values)) . ') VALUES (' . implode(',', array_fill(0, count($values), '?')) . ')';
      $values = array_values($values);
    }
    if (!isset($options['result'])) {
      $options['result'] = \Fc2blog\Model\DBInterface::RESULT_INSERT_ID;
    }
    return $this->executeSql($sql, $values, $options);
  }

  public function update($values, $where, $params=array(), $options=array())
  {
    if (!count($values)) {
      return 0;
    }
    $sets = array();
    foreach($values as $key => $value){
      $sets[] = $key . '=?';
    }
    $sql = 'UPDATE ' . $this->getTableName() . ' SET ' . implode(',', $sets) . ' WHERE ' . $where;
    $params = array_merge(array_values($values), $params);
    $options['result'] = \Fc2blog\Model\DBInterface::RESULT_SUCCESS;
    return $this->executeSql($sql, $params, $options);
  }


  /**
  * idをキーとした更新
  */
  public function updateById($values, $id, $options=array())
  {
    return $this->update($values, 'id=?', array($id), $options);
  }


  /**
  * idとblog_idをキーとした更新
  */
  public function updateByIdAndBlogId($values, $id, $blog_id, $options=array())
  {
    return $this->update($values, 'id=? AND blog_id=?', array($id, $blog_id), $options);
  }


  public function delete($where, $params=array(), $options=array())
  {
    $sql = 'DELETE FROM ' . $this->getTableName() . ' WHERE ' . $where;
    $options['result'] = \Fc2blog\Model\DBInterface::RESULT_SUCCESS;
    return $this->executeSql($sql, $params, $options);
  }

  /**
  * idをキーとした更新
  */
  public function deleteById($id, $options=array())
  {
    return $this->delete('id=?', array($id), $options);
  }

  /**
  * idとblog_idをキーとした更新
  */
  public function deleteByIdAndBlogId($id, $blog_id, $options=array())
  {
    return $this->delete('blog_id=? AND id=?', array($blog_id, $id), $options);
  }

  /**
  * idとuser_idをキーとした更新
  */
  public function deleteByIdAndUserId($id, $user_id, $options=array())
  {
    return $this->delete('id=? AND user_id=?', array($id, $user_id), $options);
  }

  public function multipleInsert($columns=array(), $params=array(), $options=array())
  {
    $sql = 'INSERT INTO ' . $this->getTableName() . ' (' . implode(',', $columns) . ') VALUES ';
    $len = count($params) / count($columns);
    $sqls = array();
    for ($i = 0; $i < $len; $i++) {
      $sqls[] = '(' . implode(',',array_fill(0, count($columns), '?')) . ')';
    }
    $sql .= implode(',', $sqls);
    $options['result'] = \Fc2blog\Model\DBInterface::RESULT_INSERT_ID;
    return $this->executeSql($sql, $params, $options);
  }

  public function insertSql($sql, $params=array(), $options=array())
  {
    $options['result'] = \Fc2blog\Model\DBInterface::RESULT_INSERT_ID;
    return $this->executeSql($sql, $params, $options);
  }

  public function executeSql($sql, $params=array(), $options=array())
  {
    return $this->getDB()->execute($sql, $params, $options);
  }

  /**
  * 階層構造の一覧取得
  */
  public function findNode($options)
  {
    $options['order'] = 'lft ASC';
    $nodes = $this->find('all', $options);

    // levelを付与
    $level = 0;
    $levels = array();
    foreach ($nodes as $key => $value) {
      // 最初のノード
      if ($level==0){
        $levels[] = $value;
        $nodes[$key]['level'] = $level = count($levels);
        continue;
      }
      // left=left+1であれば子供ノードとして解釈
      if ($value['lft']==$levels[$level-1]['lft']+1) {
        $levels[] = $value;
        $nodes[$key]['level'] = $level = count($levels);
        continue;
      }
      // left=right+1であれば兄弟ノードとして解釈
      if ($value['lft']==$levels[$level-1]['rgt']+1) {
        $levels[$level-1] = $value;
        $nodes[$key]['level'] = $level;
        continue;
      }
      // 兄弟ノードになるまで階層を遡る
      while(array_pop($levels)){
        $level = count($levels);
        if ($value['lft']==$levels[$level-1]['rgt']+1) {
          $levels[$level-1] = $value;
          $nodes[$key]['level'] = $level;
          break;
        }
      }
    }
    return $nodes;
  }

  /**
  * 階層構造の追加
  * @param array $data 追加するノード情報
  * @param string $where 親ノード検索時のwhere句
  * @param array $params 親ノード検索時のバインドデータ
  */
  public function addNode($data=array(), $where='', $params=array(), $options=array())
  {
    // 親として末尾に追加する場合
    if (empty($data['parent_id'])) {
      $max_right = $this->find('one', array('fields'=>'MAX(rgt)', 'where'=>$where, 'params'=>$params, 'options'=>$options));
      // 親として末尾に追加
      $data['lft'] = $max_right + 1;
      $data['rgt'] = $max_right + 2;
      return $this->insert($data);
    }

    // 親の子供として末尾に追加する場合
    $parent = $this->findById($data['parent_id'], array('fields'=>'rgt', 'where'=>$where, 'params'=>$params, 'options'=>$options));
    if (!$parent) {
      return false;
    }
    $right = $parent['rgt'];

    // 挿入する場所を確保する
    $table = $this->getTableName();
    if ($where != "") {
      $where .= ' AND ';
    }
    $updateSql = <<<SQL
UPDATE {$table} SET
 lft = CASE WHEN lft > {$right} THEN lft + 2 ELSE lft END,
 rgt = CASE WHEN rgt >= {$right} THEN rgt + 2 ELSE rgt END
 WHERE $where rgt >= {$right}
SQL;
    if (!$this->executeSql($updateSql, $params)) {
      return false;
    }

    // 子供として末尾に追加
    $data['lft'] = $right;
    $data['rgt'] = $right + 1;
    return $this->insert($data);
  }

  /**
  * 階層構造の更新
  */
  public function updateNodeById($data, $id, $where='', $params=array(), $options=array())
  {
    $idWhere = $where ? 'id=? AND ' . $where : 'id=?';

    // 自身取得
    $self_params = array_merge(array($id), $params);
    $self = $this->find('row', array('where'=>$idWhere, 'params'=>$self_params, 'options'=>$options));
    if (!$self) {
      return false;
    }

    // 親が変更されていない場合そのまま更新
    if ($self['parent_id']==$data['parent_id']) {
      return $this->update($data, $idWhere, $self_params, $options);
    }

    $parent = null;
    if ($self['parent_id'] && empty($data['parent_id'])) {
      // 親から外れた時
      $parent = array();
      $parent['lft'] = $parent['rgt'] = $this->find('one', array('fields'=>'MAX(rgt)', 'where'=>$where, 'params'=>$params, 'options'=>$options)) + 1;
    }else{
      // 変更先の親を取得
      $parent_params = array_merge(array($data['parent_id']), $params);
      $parent = $this->find('row', array('where'=>$idWhere, 'params'=>$parent_params, 'options'=>$options));
      if (!$parent) {
        return false;
      }
    }

    // 変更先の親が自身や自分の子供の場合はエラー
    if ($self['lft'] <= $parent['lft'] && $parent['rgt'] <= $self['rgt']) {
      return false;
    }

    // ノードの変更位置計算
    $self_lft = $self['lft'];
    $self_rgt = $self['rgt'];
    $parent_lft = $parent['lft'];
    $parent_rgt = $parent['rgt'];
    $space = $self_rgt - $self_lft + 1;

    $table = $this->getTableName();
    $where = $where ? $where . ' AND ' : '';
    $sql = '';
    if ($self_rgt > $parent_rgt) {
      // 自身を左へ移動
      $move = $parent_rgt - $self_lft;
      $sql = <<<SQL
UPDATE $table SET
lft = CASE WHEN lft > $parent_rgt AND lft < $self_lft
          THEN lft + $space
         WHEN lft >= $self_lft AND lft < $self_rgt
           THEN lft + $move
         ELSE lft END,
rgt = CASE WHEN rgt >= $parent_rgt AND rgt < $self_lft
          THEN rgt + $space
         WHEN rgt > $self_lft AND rgt <= $self_rgt
           THEN rgt + $move
         ELSE rgt END
WHERE $where
  rgt >= $parent_rgt AND lft < $self_rgt
SQL;
    }else{
      // 自身を右へ移動
      $move = $parent_rgt - $self_rgt - 1;
      $sql = <<<SQL
UPDATE $table SET
lft = CASE WHEN lft > $self_rgt AND lft < $parent_rgt
          THEN lft - $space
         WHEN lft >= $self_lft AND lft < $self_rgt
           THEN lft + $move
         ELSE lft END,
rgt = CASE WHEN rgt > $self_rgt AND rgt < $parent_rgt
          THEN rgt - $space
         WHEN rgt > $self_lft AND rgt <= $self_rgt
           THEN rgt + $move
         ELSE rgt END
WHERE $where
  rgt > $self_lft AND lft < $parent_rgt
SQL;
    }

    // 親の位置変更処理
    if (!$this->executeSql($sql, $params, $options)){
      return false;
    }

    // 自身の更新処理
    return $this->update($data, $idWhere, $self_params, $options);
  }

  /**
  * 階層構造のノード削除
  */
  public function deleteNodeById($id, $where='', $params=array(), $options=array())
  {
    // 自身取得
    $idWhere = $where ? 'id=? AND ' . $where : 'id=?';
    $self_params = array_merge(array($id), $params);
    $self = $this->find('row', array('where'=>$idWhere, 'params'=>$self_params, 'options'=>$options));

    if (!$self) {
      return false;
    }

    $self_lft = $self['lft'];
    $self_rgt = $self['rgt'];
    $space = $self_rgt - $self_lft + 1;

    $table = $this->getTableName();
    $where = $where ? $where . ' AND ' : '';

    // 削除処理
    $sql = 'DELETE FROM ' . $table . ' WHERE ' . $where . ' lft >= ' . $self_lft . ' AND rgt <= ' . $self_rgt;
    if (!$this->executeSql($sql, $params, $options)){
      return false;
    }

    // 詰める処理
    $sql = <<<SQL
UPDATE $table SET
lft = CASE WHEN lft > $self_rgt
          THEN lft - $space
         ELSE lft END,
rgt = CASE WHEN rgt > $self_rgt
          THEN rgt - $space
         ELSE rgt END
WHERE $where
  rgt > $self_rgt
SQL;
    return $this->executeSql($sql, $params, $options);
  }

}

