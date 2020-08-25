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

<div class="page">
  <div class="btn_area">

    <p class="page_num"><span class="page_num_inner"><?php echo $paging['page']+1; ?> / <?php echo $paging['max_page']; ?></span></p>

    <ul class="pager btn_contents">
      <?php if($paging['is_prev']): ?>
        <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('page'=>$paging['page']-1), true); ?>"><?php echo __('Previous page'); ?></a></li>
      <?php else: ?>
        <li><span><?php echo __('Previous page'); ?></span></li>
      <?php endif; ?>

      <?php if($paging['is_next']): ?>
        <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('page'=>$paging['page']+1), true); ?>"><?php echo __('Next page'); ?></a></li>
      <?php else: ?>
        <li><span><?php echo __('Next page'); ?></span></li>
      <?php endif; ?>
    </ul>

  </div>
</div>

