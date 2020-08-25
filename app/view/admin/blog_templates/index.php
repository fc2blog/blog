<header><h2><?php echo __('Template management'); ?></h2></header>

<?php $devices = \Fc2blog\Config::get('DEVICE_NAME'); ?>
<?php foreach($device_blog_templates as $device_type => $blog_templates): ?>
  <?php $device_key = \Fc2blog\Config::get('DEVICE_FC2_KEY.' . $device_type); ?>
  <h3><?php echo $devices[$device_type]; ?></h3>
  <p class="header_btn">
    <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogTemplates','action'=>'fc2_index', 'device_type'=>$device_type)); ?>"><?php echo __('Template Search'); ?></a>
    <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogTemplates','action'=>'create', 'device_type'=>$device_type)); ?>"><?php echo __('Template Creation'); ?></a>
  </p>
  <table>
    <thead>
      <tr>
        <th><?php echo __('Template name'); ?></th>
        <th><?php echo __('Usage state'); ?></th>
        <th><?php echo __('Preview'); ?></th>
        <th><?php echo __('Delete'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($blog_templates as $blog_template): ?>
        <tr>
          <td>
            <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'edit', 'id'=>$blog_template['id'])); ?>"><?php echo th($blog_template['title'], 20); ?></a>
          </td>
          <?php if (in_array($blog_template['id'], $template_ids)): ?>
            <td class="center red">
              <?php echo __('Applying'); ?>
            </td>
          <?php else: ?>
            <td class="center">
              <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'apply', 'id'=>$blog_template['id'], 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to apply this template?'); ?>')"><?php echo __('Apply'); ?></a>
            </td>
          <?php endif; ?>
          <td class="center">
            <a href="<?php echo \Fc2blog\App::userURL($request,array('controller'=>'entries', 'action'=>'preview', 'blog_id'=>$this->getBlogId($request), 'template_id'=>$blog_template['id'], $device_key=>1), false, true); ?>" target="_blank"><?php echo __('Preview'); ?></a>
          </td>
          <td class="center">
            <?php if (in_array($blog_template['id'], $template_ids)): ?>
              &nbsp;
            <?php else: ?>
              <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'delete', 'id'=>$blog_template['id'], 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><?php echo __('Delete'); ?></a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endforeach; ?>

