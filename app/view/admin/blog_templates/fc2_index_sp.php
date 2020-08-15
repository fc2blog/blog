<header><h1 class="sh_heading_main_b"><?php echo __('FC2 Template list'); ?>[<?php echo \Fc2blog\Config::get('DEVICE_NAME.' . $request->get('device_type')); ?>]</h1></header>

<?php if (!empty($templates)): ?>
  <ul class="template_list">
  <?php foreach ($templates as $template) : ?>
        <li class="template_list_item">
          <a href="<?php echo \Fc2blog\Web\Html::url(array('controller'=>'blog_templates', 'action'=>'fc2_view', 'fc2_id'=>$template['id'], 'device_type'=>$request->get('device_type'))); ?>">
            <img class="template_img" src="<?php echo $template['image']; ?>" width="135" height="90" alt="<?php echo $template['name']; ?>" />
            <p class="template_name"><?php echo $template['name']; ?></p>
          </a>
        </li>
<!--<?php echo __('Summary'); ?> : <?php echo $template['discription']; ?>
        <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\App::userURL(array('controller'=>'Entries', 'action'=>'preview', 'blog_id'=>$this->getBlogId(), 'fc2_id'=>$template['id'], 'device_type'=>$request->get('device_type')), false, true); ?>" target="_blank"><?php echo __('Preview'); ?></a>
        <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url(array('controller'=>'blog_templates', 'action'=>'download', 'fc2_id'=>$template['id'], 'device_type'=>$request->get('device_type'))); ?>"><?php echo __('Download'); ?></a>-->
  <?php endforeach; ?>
  </ul>
<?php else: ?>
  <p class="no_item"><?php echo __('FC2 template can not be found'); ?></p>
<?php endif; ?>

<?php $this->display('Common/paging.php', array('paging' => $paging)); ?>

