<header><h2><?php echo __('Notice'); ?></h2></header>

<?php $is_notice = false; // お知らせが合ったかどうかフラグ ?>

<?php if ($unread_count > 0) : ?>
  <?php $is_notice = true; ?>
  <p>
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array(
      'controller'   => 'Comments',
      'action'       => 'index',
      'reply_status' => \Fc2blog\Config::get('COMMENT.REPLY_STATUS.UNREAD'),
    )); ?>"><?php echo sprintf(__('There %d reviews unread comments'), $unread_count); ?></a>
  </p>
<?php endif ; ?>

<?php if ($unapproved_count > 0) : ?>
  <?php $is_notice = true; ?>
  <p>
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array(
      'controller'  => 'Comments',
      'action'      => 'index',
      'open_status' => \Fc2blog\Config::get('COMMENT.OPEN_STATUS.PENDING'),
    )); ?>"><?php echo sprintf(__('There %d reviews unapproved comment'), $unapproved_count); ?></a>
  </p>
<?php endif ; ?>

<?php if ($is_notice==false) : ?>
  <p><?php echo __('There is no notice'); ?></p>
<?php endif; ?>

