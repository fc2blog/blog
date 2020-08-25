<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('New registration of the blog'); ?></span></h1></header>

<form method="POST" class="admin-form">
  <div class="form_area">
    <div class="form_contents">
      <h4><?php echo __('Blog ID'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'blog[id]', 'text'); ?></div>
      <?php if (isset($errors['blog']['id'])): ?><span class="error"><?php echo $errors['blog']['id']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Blog name'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'blog[name]', 'text'); ?></div>
      <?php if (isset($errors['blog']['name'])): ?><span class="error"><?php echo $errors['blog']['name']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Nickname'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'blog[nickname]', 'text'); ?></div>
      <?php if (isset($errors['blog']['nickname'])): ?><span class="error"><?php echo $errors['blog']['nickname']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <div class="btn">
        <button type="submit" class="btn_contents positive touch"><i class="positive_add_icon btn_icon"></i><?php echo __('Register'); ?></button>
      </div>
    </div>
  </div>
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>">
</form>

