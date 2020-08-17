<?php if (count($files)): ?>

  <ul class="sys-form-add-media">
    <?php foreach($files as $file): ?>
    <li>
      <label for="sys-form-add-media-check-<?php echo $file['id']; ?>">
        <img src="<?php echo \Fc2blog\App::getUserFilePath($file, false); ?>" />
      </label>
      <p>
        <input type="checkbox" id="sys-form-add-media-check-<?php echo $file['id']; ?>"/>
        <label for="sys-form-add-media-check-<?php echo $file['id']; ?>">
          <?php echo th($file['name'], 10); ?>
        </label>
      </p>
    </li>
    <?php endforeach; ?>
  </ul>

  <!-- paging -->
  <?php
    // 計算処理
    $range = !empty($range) ? $range : 3;
    $start = max($paging['page'] - $range, 0);
    $end = min($paging['page'] + $range + 1, $paging['max_page']);
  ?>
  <ul class="paging">
    <?php if($paging['is_prev']): ?>
      <li><a onclick="addMedia.load({page: <?php echo $paging['page']-1; ?>, keyword: '<?php echo ue($request->get('keyword')); ?>'})">前のページ</a></li>
    <?php endif; ?>

    <?php if(0 < $start): ?>
      <li><a onclick="addMedia.load({page: 0, keyword: '<?php echo ue($request->get('keyword')); ?>'})">1</a></li>
    <?php endif; ?>

    <?php if(1 < $start): ?><li>...</li><?php endif; ?>

    <?php for($i = $start; $i < $end; $i++): ?>
      <?php if($i == $paging['page']): ?>
        <li class="active"><?php echo $i+1; ?></li>
      <?php else: ?>
        <li><a onclick="addMedia.load({page: <?php echo $i; ?>, keyword: '<?php echo ue($request->get('keyword')); ?>'})"><?php echo $i+1; ?></a></li>
      <?php endif; ?>
    <?php endfor; ?>

    <?php if($end < $paging['max_page'] - 1): ?><li>...</li><?php endif; ?>

    <?php if($end < $paging['max_page']): ?>
      <li><a  onclick="addMedia.load({page: <?php echo $paging['max_page']-1; ?>, keyword: '<?php echo ue($request->get('keyword')); ?>'})"><?php echo $paging['max_page']; ?></a></li>
    <?php endif; ?>

    <?php if($paging['is_next']): ?>
      <li><a onclick="addMedia.load({page: <?php echo $paging['page']+1; ?>, keyword: '<?php echo ue($request->get('keyword')); ?>'})">次のページ</a></li>
    <?php endif; ?>
  </ul>

<?php else: ?>

  <p>対象のファイルは存在しません</p>

<?php endif; ?>
