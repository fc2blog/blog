<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('Login'); ?></span></h1></header>

<form method="POST" class="admin-form">
  <div class="form_area">
    <div class="form_contents">
      <h4><?php echo __('Login ID'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'user[login_id]', 'text'); ?></div>
      <?php if (isset($errors['login_id'])): ?><span class="error"><?php echo $errors['login_id']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Password'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'user[password]', 'password'); ?></div>
      <?php if (isset($errors['password'])): ?><span class="error"><?php echo $errors['password']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <div class="btn">
        <button type="submit" class="btn_contents positive touch"><i class="btn_icon"></i><?php echo __('Login'); ?></button>
      </div>
    </div>
  </div>
</form>

