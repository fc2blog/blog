<header><h2><?php echo __('Plugin search'); ?></h2></header>

<?php $user_id = $this->getUserId(); ?>
<?php $devices = \Fc2blog\Config::get('DEVICE_NAME'); ?>
<?php $device_key = \Fc2blog\Config::get('DEVICE_FC2_KEY.' . $request->get('device_type')); ?>
<h3><?php echo $devices[$request->get('device_type')]; ?></h3>
    <p class="header_btn">
      <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'blog_plugins', 'action'=>'index')); ?>"><?php echo __('Plugin management'); ?></a>
    </p>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>

<table>
  <thead>
    <tr>
      <th><?php echo __('Plugin name'); ?></th>
      <th><?php echo __('Description'); ?></th>
      <th><?php echo __('Download'); ?></th>
      <th><?php echo __('Preview'); ?></th>
      <th><?php echo __('Delete'); ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($plugins as $plugin): ?>
      <tr>
        <td>
          <?php echo th($plugin['title'], 20); ?>
        </td>
        <td>
          <?php echo nl2br(h($plugin['body'])); ?>
        </td>
        <td class="center">
          <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'download', 'id'=>$plugin['id'], 'category'=>$request->get('category'), 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>"><?php echo __('Download'); ?></a>
        </td>
        <td class="center">
          <a href="<?php echo \Fc2blog\App::userURL($request,array('controller'=>'entries', 'action'=>'preview', 'blog_id'=>$this->getBlogId($request), 'plugin_id'=>$plugin['id'], 'category'=>$request->get('category'), $device_key=>1), false, true); ?>" target="_blank"><?php echo __('Preview'); ?></a>
        </td>
        <td class="center">
          <?php if ($user_id==$plugin['user_id']): ?>
            <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'plugin_delete', 'id'=>$plugin['id'], 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><?php echo __('Delete'); ?></a>
          <?php else: ?>
            &nbsp;
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>

