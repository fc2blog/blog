<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('Article setting'); ?></span></h1></header>

<?php $this->display('BlogSettings/tab.php', array('tab'=>'entry_edit')); ?>

<h2 id="blog_settings"><span class="h2_inner"><?php echo __('Setting'); ?></span></h2>
<form method="POST" id="sys-blog-template-form" class="admin-form">
  <div class="form_area">
    <div class="form_contents">
      <h4><?php echo __('Display the latest entries'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input('blog_setting[entry_recent_display_count]', 'text'); ?></div>
      <?php if (isset($errors['blog_setting']['entry_recent_display_count'])): ?><span class="error"><?php echo $errors['blog_setting']['entry_recent_display_count']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Display the number of articles, display order'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input('blog_setting[entry_display_count]', 'text'); ?></div>
      <?php if (isset($errors['blog_setting']['entry_display_count'])): ?><span class="error"><?php echo $errors['blog_setting']['entry_display_count']; ?></span><?php endif; ?>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[entry_order]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getEntryOrderList())); ?>
      <?php if (isset($errors['blog_setting']['entry_order'])): ?><p class="error"><?php echo $errors['blog_setting']['entry_order']; ?></p><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4>
        <?php echo __('Password of the article view'); ?><br />
        (<?php echo __('It is adapted to the password is not set at the time of the article'); ?>)
      </h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input('blog_setting[entry_password]', 'text'); ?></div>
      <?php if (isset($errors['blog_setting']['entry_password'])): ?><span class="error"><?php echo $errors['blog_setting']['entry_password']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <div class="btn">
        <button type="submit" class="btn_contents positive touch"><i class="save_icon btn_icon"></i><?php echo __('Update'); ?></button>
      </div>
    </div>
  </div>
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>">
</form>

