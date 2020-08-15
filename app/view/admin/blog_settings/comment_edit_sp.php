<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('Comments Settings'); ?></span></h1></header>

<?php $this->display('BlogSettings/tab.html', array('tab'=>'comment_edit')); ?>

<h2 id="blog_settings"><span class="h2_inner"><?php echo __('Setting'); ?></span></h2>
<form method="POST" id="sys-blog-template-form" class="admin-form">
  <div class="form_area">
    <div class="form_contents">
      <h4><?php echo __('Approval settings'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[comment_confirm]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getCommentConfirmList())); ?>
      <?php if (isset($errors['blog_setting']['comment_confirm'])): ?><span class="error"><?php echo $errors['blog_setting']['comment_confirm']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Display awaiting message'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[comment_display_approval]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getCommentDisplayApprovalList())); ?>
      <?php if (isset($errors['blog_setting']['comment_display_approval'])): ?><span class="error"><?php echo $errors['blog_setting']['comment_display_approval']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Display private comment'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[comment_display_private]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getCommentDisplayPrivateList())); ?>
      <?php if (isset($errors['blog_setting']['comment_display_private'])): ?><span class="error"><?php echo $errors['blog_setting']['comment_display_private']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Sender information'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[comment_cookie_save]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getCommentCookieSaveList())); ?>
      <?php if (isset($errors['blog_setting']['comment_cookie_save'])): ?><span class="error"><?php echo $errors['blog_setting']['comment_cookie_save']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Comment confirmation setting'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[comment_captcha]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getCommentCaptchaList())); ?>
      <?php if (isset($errors['blog_setting']['comment_captcha'])): ?><span class="error"><?php echo $errors['blog_setting']['comment_captcha']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Display the latest comments'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input('blog_setting[comment_display_count]', 'text'); ?></div>
      <?php if (isset($errors['blog_setting']['comment_display_count'])): ?><span class="error"><?php echo $errors['blog_setting']['comment_display_count']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Display order of comments'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[comment_order]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getCommentOrderList())); ?>
      <?php if (isset($errors['blog_setting']['comment_order'])): ?><p class="error"><?php echo $errors['blog_setting']['comment_order']; ?></p><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Quotes Comments'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('blog_setting[comment_quote]', 'select', array('options'=>\Fc2blog\Model\BlogSettingsModel::getCommentQuoteList())); ?>
      <?php if (isset($errors['blog_setting']['comment_quote'])): ?><span class="error"><?php echo $errors['blog_setting']['comment_quote']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <div class="btn">
        <button type="submit" class="btn_contents positive touch"><i class="save_icon btn_icon"></i><?php echo __('Update'); ?></button>
      </div>
    </div>
  </div>
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>">
</form>

