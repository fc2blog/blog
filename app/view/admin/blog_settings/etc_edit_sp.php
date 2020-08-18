<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('Other Settings'); ?></span></h1></header>

<?php $this->display($request, 'BlogSettings/tab.php', array('tab'=>'etc_edit')); ?>

<h2 id="blog_settings"><span class="h2_inner"><?php echo __('Setting'); ?></span></h2>
<form method="POST" id="sys-blog-template-form" class="admin-form">
  <div class="form_area">
    <div class="form_contents">
      <h4><?php echo __('Initial display page'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[start_page]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getStartPageList())); ?>
      <?php if (isset($errors['blog_setting']['start_page'])): ?><span class="error"><?php echo $errors['blog_setting']['start_page']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <div class="btn">
        <button type="submit" class="btn_contents positive touch"><i class="save_icon btn_icon"></i><?php echo __('Update'); ?></button>
      </div>
    </div>
  </div>
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>">
</form>

