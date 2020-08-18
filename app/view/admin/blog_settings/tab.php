<ul class="tab">
  <li class="tab_item<?php if ($tab=='blog_edit') echo ' selected'; ?>">
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Blogs', 'action'=>'edit')); ?>"><?php echo __('Blog setting'); ?></a>
  </li>
  <li class="tab_item<?php if ($tab=='entry_edit') echo ' selected'; ?>">
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogSettings', 'action'=>'entry_edit')); ?>"><?php echo __('Article setting'); ?></a>
  </li>
  <li class="tab_item<?php if ($tab=='comment_edit') echo ' selected'; ?>">
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogSettings', 'action'=>'comment_edit')); ?>"><?php echo __('Comments Settings'); ?></a>
  </li>
  <li class="tab_item<?php if ($tab=='etc_edit') echo ' selected'; ?>">
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogSettings', 'action'=>'etc_edit')); ?>"><?php echo __('Other Settings'); ?></a>
  </li>
  <li class="tab_item<?php if ($tab=='blog_delete') echo ' selected'; ?>">
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Blogs', 'action'=>'delete')); ?>"><?php echo __('Blog Delete'); ?></a>
  </li>
</ul>
