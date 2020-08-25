<ul class="link_list">
  <li class="link_list_item<?php if ($tab=='edit') echo ' selected'; ?>">
    <a class="common_next_link next_bg" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Users', 'action'=>'edit')); ?>"><?php echo __('User setting'); ?></a>
  </li>
  <li class="link_list_item<?php if ($tab=='withdrawal') echo ' selected'; ?>">
    <a class="common_next_link next_bg" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Users', 'action'=>'withdrawal')); ?>"><?php echo __('Withdrawal process'); ?></a>
  </li>
</ul>
