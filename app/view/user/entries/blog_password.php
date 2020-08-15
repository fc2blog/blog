<table class="private_lock">
  <tr>
    <td class="private_lock_left">
      <div  class="private_lock_left_inner">
        <h2 class="private_lock_title"><?php echo __('Password authentication'); ?></h2>
        <div class="private_about">
          <?php echo __('Enter the password manager has set is required to view'); ?>
        </div>
      </div>
    </td>
    <td class="private_lock_right">
      <form method="POST">
        <div class="private_lock_right_inner">
          <div class="private_lock_pass_area">
            <h3 class="private_lock_right_title"><?php echo __('View password'); ?></h3>
            <input type="password" name="blog[password]" />
            <?php if (isset($errors['password'])): ?><p class="error"><?php echo $errors['password']; ?></p><?php endif; ?>
            <div><input type="submit" value="<?php echo __('I see the blog'); ?>" class="private_lock_btn" /></div>
          </div>
        </div>
      </form>
    </td>
  </tr>
</table>
