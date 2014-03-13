<?php
/**
* FC2用のリクエストデータ置き換え用
*/

$config = array();
$config['request_combine'] = array();

// コメント投稿用の引数入れ替えキー
$config['request_combine']['comment_register'] = array(
  'comment.no'     => 'comment.entry_id',
  'comment.pass'   => 'comment.password',
  'comment.himitu' => 'comment.open_status',
);

// コメント投稿用の引数入れ替えキー
$config['request_combine']['comment_edit'] = array(
  'edit.rno'    => 'comment.id',
  'edit.name'   => 'comment.name',
  'edit.title'  => 'comment.title',
  'edit.mail'   => 'comment.mail',
  'edit.url'    => 'comment.url',
  'edit.body'   => 'comment.body',
  'edit.pass'   => 'comment.password',
  'edit.himitu' => 'comment.open_status',
  'edit.delete' => 'comment.delete',
);

return $config;
