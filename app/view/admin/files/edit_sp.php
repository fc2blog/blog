<header><h1 class="detail sh_heading_main_b"><?php echo __('Details of file'); ?></h1></header>
<h2><span class="h2_inner"><?php echo __('Checking file '); ?></span></h2>
<p class="editor_img">
  <a href="<?php echo \Fc2blog\App::getUserFilePath($file, false, true); ?>" target="_blank"><img src="<?php echo \Fc2blog\App::getThumbnailPath(\Fc2blog\App::getUserFilePath($file, false, true), 400, 'w'); ?>" alt="<?php echo $file['name']; ?>" /></a>
</p>

<h3><span class="h3_inner"><?php echo __('Information in the file'); ?></span></h3>
<ul class="link_list">
  <li class="link_list_item common_next_link"><?php echo $file['name']; ?></li>
  <li class="link_list_item common_next_link"><?php echo df($file['created_at']); ?></li>
</ul>

<h2><span class="h2_inner"><?php echo __('Edit File'); ?></span></h2>
<form method="POST" id="sys-file-form" class="admin-form" enctype="multipart/form-data">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo \Fc2blog\Config::get('FILE.MAX_SIZE'); ?>" />
  <input type="hidden" name="id" value="<?php echo $request->get('id'); ?>" />
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />
  <div class="btn_area">
    <div class="up_file_btn">
      <?php echo \Fc2blog\Web\Html::input($request, 'file[name]', 'text', array('id'=>'sys-file-name')); ?>
      <?php echo \Fc2blog\Web\Html::input($request, 'file[file]', 'file', array('style'=>'opacity: 0; position: absolute; width: 120px;', 'onchange'=>"$('#sys-file-name').val($(this).val().split('\\\\').pop());")); ?>
      <button type="button" class="lineform_btn touch" onclick="$(this).prev().click();" style="width: 120px;" /><?php echo __('File selection'); ?></button>
    </div>
    <?php if (isset($errors['file']['ext'])): ?>
      <p class="error"><?php echo $errors['file']['ext']; ?></p>
    <?php elseif (isset($errors['file']['file'])): ?>
      <p class="error"><?php echo $errors['file']['file']; ?></p>
    <?php endif; ?>
    <?php if (isset($errors['file']['name'])): ?><p class="error"><?php echo $errors['file']['name']; ?></p><?php endif; ?>

    <div class="btn">
      <button type="submit" class="btn_contents positive touch"><i class="save_icon btn_icon"></i><?php echo __('Update'); ?></button>
    </div>
  </div>
</form>

<div class="btn_area">
  <ul class="btn_area_inner">
    <li><a class="btn_contents touch" href="<?php if($request->isArgs('back_url')): ?><?php echo $request->get('back_url'); ?><?php else: ?><?php echo \Fc2blog\Web\Html::url($request, array('action'=>'upload')); ?><?php endif; ?>"><i class="return_icon btn_icon"></i><?php echo __('I Back to List'); ?></a></li>
    <li><a class="btn_contents touch" href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'delete', 'id'=>$file['id'], 'back_url'=>ue($request->get('back_url')), 'sig'=>ue(\Fc2blog\Web\Session::get('sig')))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><i class="delete_icon btn_icon"></i><?php echo __('Delete'); ?></a></li>
  </ul>
</div>

