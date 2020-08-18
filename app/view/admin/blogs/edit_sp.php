<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('Blog setting'); ?></span></h1></header>

<?php $this->display($request, 'BlogSettings/tab.php', array('tab'=>'blog_edit')); ?>

<h2 id="blog_settings"><span class="h2_inner"><?php echo __('Setting'); ?></span></h2>
<form method="POST" class="admin-form">
  <div class="form_area">
    <div class="form_contents">
      <h4><?php echo __('Blog name'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'blog[name]', 'text'); ?></div>
      <?php if (isset($errors['blog']['name'])): ?><span class="error"><?php echo $errors['blog']['name']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Blog Description'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'blog[introduction]', 'text'); ?></div>
      <?php if (isset($errors['blog']['introduction'])): ?><span class="error"><?php echo $errors['blog']['introduction']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Nickname'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'blog[nickname]', 'text'); ?></div>
      <?php if (isset($errors['blog']['nickname'])): ?><span class="error"><?php echo $errors['blog']['nickname']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Visibility Blog'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input($request, 'blog[open_status]', 'select', array('options'=>\Fc2blog\Model\BlogsModel::getOpenStatusList())); ?>
      <?php if (isset($errors['blog']['open_status'])): ?><span class="error"><?php echo $errors['blog']['open_status']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('View password blog'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'blog[blog_password]', 'text'); ?></div>
      <?php if (isset($errors['blog']['blog_password'])): ?><span class="error"><?php echo $errors['blog']['blog_password']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Time zone'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input($request, 'blog[timezone]', 'select', array('options'=>\Fc2blog\Model\BlogsModel::getTimezoneList())); ?>
      <?php if (isset($errors['blog']['timezone'])): ?><span class="error"><?php echo $errors['blog']['timezone']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <div class="btn">
        <button type="submit" class="btn_contents positive touch"><i class="save_icon btn_icon"></i><?php echo __('Update'); ?></button>
      </div>
    </div>
  </div>
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>">
</form>

