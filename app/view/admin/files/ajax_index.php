
<h3 id="entry_count">
  <?php echo __('File search'); ?>[<?php echo __('Hits'); ?>&nbsp;<?php echo $paging['count']; ?><?php echo __(' results'); ?>]
  <?php echo \Fc2blog\Web\Html::input('limit', 'select', array('options'=>\Fc2blog\App::getPageList('FILE'), 'default'=>\Fc2blog\Config::get('FILE.DEFAULT_LIMIT'))); ?>
  <?php echo \Fc2blog\Web\Html::input('page', 'select', array('options'=>\Fc2blog\Model\Model::getPageList($paging), 'default'=>0)); ?>
</h3>
<p><?php echo __('You can search to match the conditions file.'); ?></p>
<div id="entry_search">
  <form method="GET" id="sys-search-form" onsubmit="return false;">
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="Files" />
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="ajax_index" />
    <?php echo \Fc2blog\Web\Html::input('limit', 'hidden', array('default'=>\Fc2blog\App::getPageLimit('FILE'))); ?>
    <?php echo \Fc2blog\Web\Html::input('page', 'hidden', array('default'=>0)); ?>
    <?php echo \Fc2blog\Web\Html::input('order', 'hidden', array('default'=>'created_at_desc')); ?>
    <br /><?php echo \Fc2blog\Web\Html::input('keyword', 'text', array('maxlength'=>100)); ?>
    <input type="submit" value="<?php echo __('Search'); ?>" />
  </form>
</div>

<?php $this->display('Common/paging.php', array('paging' => $paging)); ?>

<table>
  <thead>
    <tr>
      <th><input type="checkbox" onclick="common.fullCheck(this);" /></th>
      <th class="file_view"></th>
      <th><a href="javascript:void(0);" onclick="orderChange('created_at_desc'); return false;"><?php echo __('Date'); ?></a></th>
      <th><a href="javascript:void(0);" onclick="orderChange('name_asc'); return false;"><?php echo __('Name'); ?></a></th>
      <th><?php echo __('Edit'); ?></th>
      <th><?php echo __('Delete'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php if (count($files)) : ?>
      <?php foreach($files as $file): ?>
      <tr>
        <td class="center ss_cell"><input type="checkbox" name="id[]" value="<?php echo $file['id']; ?>" /></td>
        <td class="center">
          <?php if (in_array($file['ext'], array('jpeg', 'jpg', 'png', 'gif'))) : ?>
            <img src="<?php echo \Fc2blog\App::getUserFilePath($file, false, true); ?>" style="width: 120px;" />
          <?php endif; ?>
        </td>
        <td><?php echo df($file['created_at'], 'y/m/d'); ?></td>
        <td><a href="<?php echo \Fc2blog\App::getUserFilePath($file, false, true); ?>" target="_blank"><?php echo th($file['name'], 30); ?></a></td>
        <td class="center s_cell"><a href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'edit', 'id'=>$file['id'])); ?>"><?php echo __('Edit'); ?></a></td>
        <td class="center s_cell"><a href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'delete', 'id'=>$file['id'], 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><?php echo __('Delete'); ?></a></td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="5"><?php echo __('The target file does not exist'); ?></td></tr>
    <?php endif; ?>
  </tbody>
</table>

<input type="button" id="sys-delete-button" value="<?php echo __('Remove what you have selected'); ?>" disabled="disabled" />

<?php $this->display('Common/paging.php', array('paging' => $paging)); ?>

<script>
$(function(){
  // ページ件数 or ページ数を変更した際に自動でサブミット
  $('select[name=limit]').on('change', function(){
    $('input[name=limit]').val($(this).val());
    ajaxSubmit();
  });
  $('select[name=page]').on('change', function(){
    $('input[name=page]').val($(this).val());
    isPageChange = true;
    ajaxSubmit();
  });
  // 検索ボタンのサブミット処理
  $('#sys-search-form input[type=submit]').on('click', function(){
    ajaxSubmit();
  });
  $('ul.paging a').on('click', function(){
    ajaxSubmit($(this).prop('href'));
    return false;
  });

  // ファイル削除ボタン
  $('#sys-delete-button').click(function(){
    if (!confirm('<?php echo __('Are you sure you want to delete?'); ?>')) {
      return ;
    }
    var ids = [];
    $('input[type=checkbox][name="id[]"]:checked').each(function(){
      ids.push($(this).val());
    });

    if ($('#sys-delete-button').prop('disabled')) {
      return ;
    }

    isAjaxSubmit = false;
    $('#sys-delete-button').attr('disabled', 'disabled');
    $('#sys-delete-button').val('通信中');

    // Ajaxで削除処理
    $.ajax({
      url: '<?php echo \Fc2blog\Web\Html::url(array('controller'=>'Files', 'action'=>'ajax_delete')); ?>',
      type: 'POST',
      data: {id: ids, sig: '<?php echo \Fc2blog\Web\Session::get('sig'); ?>'},
      dataType: 'json',
      success: function(json){
        // 削除完了後検索処理を実行
        isAjaxSubmit = isPageChange = true;
        ajaxSubmit();
      }
    });
    return false;
  });
  // 削除用のチェックボックスがチェックされている時だけ削除ボタンを有効化する
  $('input[type=checkbox][name="id[]"]').on('change', function(){
    $('#sys-delete-button').prop('disabled', !$('input[type=checkbox][name="id[]"]:checked').length);
  });
});
</script>

