<ul class="tab">
  <li class="tab_item<?php if ($tab=='edit') echo ' selected'; ?>">
    <a href="<?php echo \Fc2blog\Web\Html::url(array('controller'=>'Users', 'action'=>'edit')); ?>"><?php echo __('User setting'); ?></a>
  </li>
  <li class="tab_item<?php if ($tab=='withdrawal') echo ' selected'; ?>">
    <a href="<?php echo \Fc2blog\Web\Html::url(array('controller'=>'Users', 'action'=>'withdrawal')); ?>"><?php echo __('Withdrawal process'); ?></a>
  </li>
</ul>
