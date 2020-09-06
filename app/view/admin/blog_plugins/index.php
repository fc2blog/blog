<?php throw new LogicException("Already converted to twig. something wrong."); ?>
<header><h2><?php echo __('Plugin management'); ?></h2></header>

<?php $device_type = $request->get('device_type'); ?>
<div class="header_select">
  <?php $devices = \Fc2blog\Config::get('DEVICE_NAME'); ?>
  <select onchange="location.href=$(this).val();">
    <?php foreach ($devices as $type => $name) : ?>
      <option value="<?php echo \Fc2blog\Web\Html::url($request, array('device_type'=>$type)); ?>" <?php if($device_type==$type): ?>selected="selected"<?php endif; ?>><?php echo h($name) ?></option>
    <?php endforeach; ?>
  </select>
</div>

<div id="sys-index">
  <?php foreach($category_blog_plugins as $category => $blog_plugins): ?>
    <h3><?php echo __('Category'); ?><?php echo $category; ?></h3>
    <p class="header_btn">
      <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogPlugins','action'=>'official_search', 'device_type'=>$device_type, 'category'=>$category)); ?>"><?php echo __('Official Plugin Search'); ?></a>
      <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogPlugins','action'=>'share_search', 'device_type'=>$device_type, 'category'=>$category)); ?>"><?php echo __('Share Plugin Search'); ?></a>
      <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogPlugins','action'=>'create', 'device_type'=>$device_type, 'category'=>$category)); ?>"><?php echo __('Plugin Creation'); ?></a>
    </p>
    <table>
      <thead>
        <tr>
          <th><?php echo __('Plugin name'); ?></th>
          <th class="m_cell"><?php echo __('Display'); ?></th>
          <th><?php echo __('Share plugin'); ?></th>
          <th class="s_cell"><?php echo __('Delete'); ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($blog_plugins as $blog_plugin): ?>
          <tr>
            <td>
              <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'edit', 'id'=>$blog_plugin['id'])); ?>"><?php echo th($blog_plugin['title'], 20); ?></a>
            </td>
            <td class="center m_cell">
              <input type="checkbox" name="blog_plugin[display]" value="<?php echo $blog_plugin['id']; ?>" <?php if ($blog_plugin['display']==\Fc2blog\Config::get('APP.DISPLAY.SHOW')) echo 'checked="checked"'; ?> />
            </td>
            <td class="center">
              <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'register', 'id'=>$blog_plugin['id'])); ?>"><?php if($blog_plugin['plugin_id']): ?><?php echo __('Update'); ?><?php else: ?><?php echo __('Register'); ?><?php endif; ?></a>
            </td>
            <td class="center s_cell">
              <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'delete', 'id'=>$blog_plugin['id'], 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><?php echo __('Delete'); ?></a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endforeach; ?>
  <div class="center">
    <input type="button" id="sys-index-sort-button" value="<?php echo __('Sort'); ?>" />
  </div>
</div>

<?php $json = array(); ?>
<form action="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'blog_plugins', 'action'=>'sort')); ?>" method="POST" id="sys-order" style="display: none;">
  <?php foreach($category_blog_plugins as $category => $blog_plugins): ?>
    <h3><?php echo __('Category'); ?><?php echo $category; ?></h3>
    <ul id="sys-category-<?php echo $category; ?>" class="jquery-ui-sortable mb20"></ul>
    <?php foreach($blog_plugins as $blog_plugin): ?>
      <?php
        $json[] = array(
          'id'       => $blog_plugin['id'],
          'category' => $blog_plugin['category'],
          'title'    => $blog_plugin['title'],
        );
      ?>
    <?php endforeach; ?>
  <?php endforeach; ?>
  <div class="center">
    <input type="button" id="sys-order-cancel-button" value="<?php echo __('Cancel'); ?>" />
    <input type="button" id="sys-order-save-button" value="<?php echo __('Completion'); ?>" />
  </div>
  <input type="hidden" name="device_type" value="<?php echo $device_type; ?>" />
</form>

<script>
var blog_plugins = <?php echo json_encode($json); ?>;
$(function(){
  // 表示切り替え
  $('input[type=checkbox][name="blog_plugin[display]"]').on('click', function(){
    $.ajax({
      url : common.fwURL('blog_plugins', 'display_change', {
        id: $(this).val(),
        display: $(this).prop('checked') ? 1 : 0,
        sig: "<?php echo \Fc2blog\Web\Session::get('sig'); ?>"
      }),
      cache: false
    });
  });

  // 並び順変更
  $('#sys-index-sort-button').on('click', function(){
    $('#sys-index').hide();
    $('#sys-order').show();
    // ソート用のリスト作成
    $('.jquery-ui-sortable').each(function(){
      $(this).append('<li class="ui-state-disabled">プラグイン名</li>');
    });
    for(var i=0;i<blog_plugins.length;i++){
      var blog_plugin = blog_plugins[i];
      var html = '<li>';
      html += '<input type="hidden" name="id" value="' + blog_plugin['id'] + '" />';
      html += blog_plugin['title'];
      html += '<a href="javascript:void(0);" onclick="$(this).parent().fadeOut(\'fast\', function(){$(this).remove();})"><?php echo __('Delete'); ?></a>',
      html += '</li>';
      $('#sys-category-' + blog_plugin['category']).append(html);
    }
    $('.jquery-ui-sortable').sortable({
      connectWith: '.jquery-ui-sortable',
      items: 'li:not(.ui-state-disabled)'
    });
  });

  // キャンセル
  $('#sys-order-cancel-button').on('click', function(){
    $('#sys-order').hide();
    $('#sys-index').show();
    // リストの中身を初期化
    $('#sys-category-1, #sys-category-2, #sys-category-3').html('');
  });

  // 完了
  $('#sys-order-save-button').on('click', function(){
    $(this).parent().html('<?php echo __('Is communicating ...'); ?>');
    $('.jquery-ui-sortable > li:not(.ui-state-disabled) > input[type=hidden]').each(function(){
      var id = $(this).val();
      var category = $(this).closest('ul.jquery-ui-sortable').attr('id').match(/sys-category-([123])/)[1];
      var order = $(this).closest('ul.jquery-ui-sortable').find('li:not(.ui-state-disabled)').index($(this).closest('li'));
      $('#sys-order').append('<input type="hidden" name="blog_plugins['+id+'][order]" value="'+order+'" />');
      $('#sys-order').append('<input type="hidden" name="blog_plugins['+id+'][category]" value="'+category+'" />');
    });
    $('#sys-order').submit();
  });

});
</script>

