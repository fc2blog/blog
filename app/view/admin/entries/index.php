<header><h2><?php echo __('List of articles'); ?></h2></header>

<h3 id="entry_count">
  <?php echo __('Entry search'); ?>[<?php echo __('Hits'); ?>&nbsp;<?php echo $paging['count']; ?><?php echo __(' results'); ?>]
  <?php echo \Fc2blog\Web\Html::input('limit', 'select', array('options'=>\Fc2blog\Config::get('ENTRY.LIMIT_LIST'), 'default'=>\Fc2blog\Config::get('ENTRY.DEFAULT_LIMIT'))); ?>
  <?php echo \Fc2blog\Web\Html::input('page', 'select', array('options'=>\Fc2blog\Model\Model::getPageList($paging), 'default'=>0)); ?>
</h3>
<p><?php echo __('You can search in accordance with the conditions of past articles.'); ?></p>
<div id="entry_search">
  <form method="GET" id="sys-search-form">
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="Entries" />
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="index" />
    <?php echo \Fc2blog\Web\Html::input('category_id', 'select', array('options'=>array(''=>__('Category name')) + \Fc2blog\Model\Model::load('Categories')->getSearchList($this->getBlogId($request)))); ?>
    <?php echo \Fc2blog\Web\Html::input('tag_id', 'select', array('options'=>array(''=>__('Tag name')) + \Fc2blog\Model\Model::load('Tags')->getSearchList($this->getBlogId($request)))); ?>
    <?php echo \Fc2blog\Web\Html::input('open_status', 'select', array('options'=>array(''=>__('Public state')) + \Fc2blog\Model\EntriesModel::getOpenStatusList())); ?>
    <?php echo \Fc2blog\Web\Html::input('limit', 'hidden', array('default'=>\Fc2blog\Config::get('ENTRY.DEFAULT_LIMIT'))); ?>
    <?php echo \Fc2blog\Web\Html::input('page', 'hidden', array('default'=>0)); ?>
    <?php echo \Fc2blog\Web\Html::input('order', 'hidden', array('default'=>'posted_at_desc')); ?>
    <br /><?php echo \Fc2blog\Web\Html::input('keyword', 'text', array('maxlength'=>100)); ?>
    <input type="submit" value="<?php echo __('Search'); ?>" />
  </form>
</div>
<script src="/js/admin/search_form.js" type="text/javascript" charset="utf-8"></script>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>

<?php $open_status_list = \Fc2blog\Model\EntriesModel::getOpenStatusList(); ?>
<form method="POST" id="sys-list-form">
  <table>
    <thead>
      <tr>
        <th><input type="checkbox" onclick="common.fullCheck(this);" /></th>
        <th><a href="javascript:void(0);" onclick="orderChange('posted_at_desc'); return false;"><?php echo __('Date'); ?></a></th>
        <th><a href="javascript:void(0);" onclick="orderChange('title_asc'); return false;"><?php echo __('Title'); ?></a></th>
        <th><a href="javascript:void(0);" onclick="orderChange('body_asc'); return false;"><?php echo __('Body'); ?></a></th>
        <th><?php echo __('Edit'); ?></th>
        <th><?php echo __('State'); ?></th>
        <th><a href="javascript:void(0);" onclick="orderChange('comment_desc'); return false;"><?php echo __('Comment'); ?></a></th>
        <th><?php echo __('Delete'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($entries as $entry): ?>
      <tr>
        <td class="center ss_cell"><input type="checkbox" name="id[]" value="<?php echo $entry['id']; ?>" /></td>
        <td><?php echo df($entry['posted_at'], 'y-m-d'); ?></td>
        <td class="m_cell">
          <a href="<?php echo \Fc2blog\Model\BlogsModel::getFullHostUrlByBlogId($this->getBlogId($request), \Fc2blog\Config::get('DOMAIN_USER')); ?><?php echo \Fc2blog\App::userURL($request,array('controller'=>'Entries', 'action'=>'view', 'blog_id'=>$entry['blog_id'], 'id'=>$entry['id'])); ?>" target="_blank">
            <?php echo th($entry['title'], 10); ?>
          </a>
        </td>
        <td><?php echo th($entry['body'], 10); ?></td>
        <td class="center s_cell"><a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'edit', 'id'=>$entry['id'])); ?>"><?php echo __('Edit'); ?></a></td>
        <td><?php echo $open_status_list[$entry['open_status']]; ?></td>
        <td>
          <?php if ($entry['comment_count']>0): ?>
            <a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Comments', 'action'=>'index', 'entry_id'=>$entry['id'])); ?>"><?php echo $entry['comment_count']; ?></a>
          <?php else: ?>
            <?php echo $entry['comment_count']; ?>
          <?php endif; ?>
        </td>
        <td class="center s_cell"><a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'delete', 'id'=>$entry['id'], 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><?php echo __('Delete'); ?></a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="entries" />
  <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="delete" />
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />
  <input type="button" id="sys-entries-delete-button" value="<?php echo __('Remove what you have selected'); ?>" onclick="if(confirm('<?php echo __('Are you sure you want to delete?'); ?>')) $('#sys-list-form').submit();" disabled="disabled" />
</form>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>

<script>
$(function(){
  // 記事削除用のチェックボックスがチェックされている時だけ削除ボタンを有効化する
  $('input[type=checkbox][name="id[]"]').on('change', function(){
    $('#sys-entries-delete-button').prop('disabled', !$('input[type=checkbox][name="id[]"]:checked').length);
  });
});
</script>

