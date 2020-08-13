<?php
/**
* ページングの表示HTML
* @param $paging array  ページング情報      必須
* @param $range int      ページングの表示幅  省略可
*/
?>
<?php
  // 計算処理
  $range = !empty($range) ? $range : 3;
  $start = max($paging['page'] - $range, 0);
  $end = min($paging['page'] + $range + 1, $paging['max_page']);
?>
<ul class="paging">
  <?php if($paging['is_prev']): ?>
    <li><a href="<?php echo \Fc2blog\Web\Html::url(array('page'=>$paging['page']-1)); ?>">&lt;</a></li>
  <?php endif; ?>

  <?php if(0 < $start): ?>
    <li><a href="<?php echo \Fc2blog\Web\Html::url(array('page'=>0)); ?>">1</a></li>
  <?php endif; ?>

  <?php if(1 < $start): ?>
    <li>...</li>
  <?php endif; ?>

  <?php for($i = $start; $i < $end; $i++): ?>
    <?php if($i == $paging['page']): ?>
      <li class="active"><?php echo $i+1; ?></li>
    <?php else: ?>
      <li><a href="<?php echo \Fc2blog\Web\Html::url(array('page'=>$i)); ?>"><?php echo $i+1; ?></a></li>
    <?php endif; ?>
  <?php endfor; ?>

  <?php if($end < $paging['max_page'] - 1): ?>
    <li>...</li>
  <?php endif; ?>

  <?php if($end < $paging['max_page']): ?>
    <li><a href="<?php echo \Fc2blog\Web\Html::url(array('page'=>$paging['max_page']-1)); ?>"><?php echo $paging['max_page']; ?></a></li>
  <?php endif; ?>

  <?php if($paging['is_next']): ?>
    <li><a href="<?php echo \Fc2blog\Web\Html::url(array('page'=>$paging['page']+1)); ?>">&gt;</a></li>
  <?php endif; ?>
</ul>

