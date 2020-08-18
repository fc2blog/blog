<header><h2><?php echo __('Installation complete'); ?></h2></header>

<?php echo __('The installation was completed'); ?><br />
<?php echo __('Please log from the following'); ?><br />
<a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Users', 'action'=>'login')); ?>"><?php echo __('Login'); ?></a><br />
<?php echo __('If you want to re-enable this installer, please delete `installed.lock` file in temp dir.'); ?>

