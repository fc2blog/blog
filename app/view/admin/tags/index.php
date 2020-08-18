<header><h2><?php echo __('List of tags'); ?></h2></header>

<h3 id="entry_count">
  <?php echo __('Tag search'); ?>[<?php echo __('Hits'); ?>&nbsp;<?php echo $paging['count']; ?><?php echo __(' results'); ?>]
  <?php echo \Fc2blog\Web\Html::input($request, 'limit', 'select', array('options'=>\Fc2blog\Config::get('TAG.LIMIT_LIST'), 'default'=>\Fc2blog\Config::get('TAG.DEFAULT_LIMIT'))); ?>
  <?php echo \Fc2blog\Web\Html::input($request, 'page', 'select', array('options'=>\Fc2blog\Model\Model::getPageList($paging), 'default'=>0)); ?>
</h3>
<p><?php echo __('I can search by tag name'); ?></p>
<div id="entry_search">
  <form method="GET" id="sys-search-form">
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="Tags" />
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="index" />
    <?php echo \Fc2blog\Web\Html::input($request, 'limit', 'hidden', array('default'=>\Fc2blog\Config::get('TAG.DEFAULT_LIMIT'))); ?>
    <?php echo \Fc2blog\Web\Html::input($request, 'page', 'hidden', array('default'=>0)); ?>
    <?php echo \Fc2blog\Web\Html::input($request, 'order', 'hidden', array('default'=>'count_desc')); ?>
    <?php echo \Fc2blog\Web\Html::input($request, 'name', 'text', array('placeholder'=>__('Tag name'))); ?>
    <input type="submit" value="<?php echo __('Search'); ?>" />
  </form>
</div>
<script src="/js/admin/search_form.js" type="text/javascript" charset="utf-8"></script>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>
<form method="POST" id="sys-list-form">
  <table>
    <thead>
      <tr>
        <th><input type="checkbox" onclick="common.fullCheck(this);" /></th>
        <th><a href="javascript:void(0);" onclick="orderChange('name_asc'); return false;"><?php echo __('Tag name'); ?></a></th>
        <th><a href="javascript:void(0);" onclick="orderChange('count_desc'); return false;"><?php echo __('Entry count'); ?></a></th>
        <th><?php echo __('Edit'); ?></th>
        <th><?php echo __('Delete'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($tags as $tag): ?>
      <tr>
        <td class="center ss_cell"><input type="checkbox" name="id[]" value="<?php echo $tag['id']; ?>" /></td>
        <td><a href="<?php echo \Fc2blog\Model\BlogsModel::getFullHostUrlByBlogId(\Fc2blog\Web\Session::get('blog_id'), \Fc2blog\Config::get('DOMAIN_USER')); ?>/<?php echo \Fc2blog\Web\Session::get('blog_id'); ?>/?tag=<?php echo ue($tag['name']); ?>" target="_blank"><?php echo h($tag['name']); ?></a></td>
        <td><?php echo $tag['count']; ?></td>
        <td class="center s_cell"><a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'edit', 'id'=>$tag['id'])); ?>"><?php echo __('Edit'); ?></a></td>
        <td class="center s_cell"><a href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'delete', 'id'=>$tag['id'], 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><?php echo __('Delete'); ?></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="tags" />
  <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="delete" />
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />
  <input type="button" id="sys-delete-button" value="<?php echo __('Remove what you have selected'); ?>" onclick="if(confirm('<?php echo __('Are you sure you want to delete?'); ?>')) $('#sys-list-form').submit();" disabled="disabled" />
</form>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>

<script>
$(function(){
  // 削除用のチェックボックスがチェックされている時だけ削除ボタンを有効化する
  $('input[type=checkbox][name="id[]"]').on('change', function(){
    $('#sys-delete-button').prop('disabled', !$('input[type=checkbox][name="id[]"]:checked').length);
  });
});
</script>

