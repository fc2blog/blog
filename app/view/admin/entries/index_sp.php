<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('List of articles'); ?></span><span class="accordion_btn"><i class="search_icon btn_icon"></i></span></h1></header>
<div id="entry_search" class="accordion_contents" style="display:none;">
  <form method="GET" id="sys-search-form">
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="Entries" />
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="index" />
    <dl class="input_search">
      <dt class="lineform_text_wrap common_input_text"><?php echo \Fc2blog\Web\Html::input('keyword', 'text'); ?></dt>
      <dd class="lineform_btn_wrap"><button type="submit" value="<?php echo __('Search'); ?>" class="lineform_btn touch"><?php echo __('Search'); ?></button></dd>
    </dl>
    <div class="select_search">
      <?php echo \Fc2blog\Web\Html::input('category_id', 'select', array('options'=>array(''=>__('Category name')) + \Fc2blog\Model\Model::load('Categories')->getSearchList($this->getBlogId()))); ?>
      <?php echo \Fc2blog\Web\Html::input('tag_id', 'select', array('options'=>array(''=>__('Tag name')) + \Fc2blog\Model\Model::load('Tags')->getSearchList($this->getBlogId()))); ?>
      <?php echo \Fc2blog\Web\Html::input('open_status', 'select', array('options'=>array(''=>__('Public state')) + \Fc2blog\Model\EntriesModel::getOpenStatusList())); ?>
      <?php echo \Fc2blog\Web\Html::input('limit', 'hidden', array('default'=>\Fc2blog\Config::get('ENTRY.DEFAULT_LIMIT'))); ?>
      <?php echo \Fc2blog\Web\Html::input('page', 'hidden', array('default'=>0)); ?>
      <?php echo \Fc2blog\Web\Html::input('order', 'hidden', array('default'=>'posted_at_desc')); ?>
    </div>
  </form>
</div>
<script src="/js/admin/search_form.js" type="text/javascript" charset="utf-8"></script>

<?php $open_status_list = \Fc2blog\Model\EntriesModel::getOpenStatusList(); ?>
<form method="POST" id="sys-list-form">
  <ul class="link_list">
  <?php foreach($entries as $entry): ?>
    <li class="link_list_item">
      <a href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'edit', 'id'=>$entry['id'])); ?>" class="common_next_link next_bg">
        <dl>
          <dt class="item_title"><?php echo th($entry['title'], 10); ?></dt>
          <dd class="item_time"><i class="entry_time detail_icon"></i><time><?php echo df($entry['posted_at'], 'y-m-d'); ?></time></dd>
          <dd class="state"><i class="entry_state detail_icon"></i><?php echo $open_status_list[$entry['open_status']]; ?></dd>
          <dd class="comment"><i class="entry_comment detail_icon"></i><?php echo $entry['comment_count']; ?></dd>
        </dl>
      </a>
    </li>
  <?php endforeach; ?>
  </ul>

  <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="Entries" />
  <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="delete" />
</form>

<?php $this->display('Common/paging.php', array('paging' => $paging)); ?>

