<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('List of tags'); ?></span><span class="accordion_btn"><i class="search_icon btn_icon"></i></span></h1></header>
<div id="entry_search" class="accordion_contents" style="display:none;">
    <form method="GET" id="sys-search-form">
      <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="Tags" />
      <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="index" />
      <?php echo \Fc2blog\Web\Html::input($request, 'limit', 'hidden', array('default'=>\Fc2blog\Config::get('TAG.DEFAULT_LIMIT'))); ?>
      <?php echo \Fc2blog\Web\Html::input($request, 'page', 'hidden', array('default'=>0)); ?>
      <?php echo \Fc2blog\Web\Html::input($request, 'order', 'hidden', array('default'=>'count_desc')); ?>
      <dl class="input_search">
        <dt class="lineform_text_wrap common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'name', 'text', array('placeholder'=>__('Tag name'))); ?></dt>
        <dd class="lineform_btn_wrap"><button type="submit" class="lineform_btn touch"><?php echo __('Search'); ?></button></dd>
      </dl>
    </form>
</div>

<ul class="link_list">
  <?php foreach($tags as $tag): ?>
  <li class="link_list_item">
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'edit', 'id'=>$tag['id'])); ?>" class="common_next_link next_bg">
     <dl>
       <dt class="item_title"><?php echo h($tag['name']); ?></dt>
       <dd class="state"><i class="entry_state detail_icon"></i><?php echo $tag['count']; ?></dd>
     </dl>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>

