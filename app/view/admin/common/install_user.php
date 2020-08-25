<header><h2><?php echo __('Administrator registration'); ?></h2></header>

<form method="POST" class="admin-form">
  <input type="hidden" name="state" value="2" />

  <table id="id_form">
    <tbody>
      <tr>
        <th><?php echo __('Login ID'); ?></th>
        <td>
          <?php echo \Fc2blog\Web\Html::input($request, 'user[login_id]', 'text'); ?>
          <?php if (isset($errors['user']['login_id'])): ?><?php echo $errors['user']['login_id']; ?><?php endif; ?>
        </td>
      </tr>
      <tr>
        <th><?php echo __('Password'); ?></th>
        <td>
          <?php echo \Fc2blog\Web\Html::input($request, 'user[password]', 'password'); ?>
          <?php if (isset($errors['user']['password'])): ?><?php echo $errors['user']['password']; ?><?php endif; ?>
        </td>
      </tr>
      <tr>
        <th><?php echo __('Blog ID'); ?></th>
        <td>
          <?php echo \Fc2blog\Web\Html::input($request, 'blog[id]', 'text'); ?>
          <?php if (isset($errors['blog']['id'])): ?><?php echo $errors['blog']['id']; ?><?php endif; ?>
        </td>
      </tr>
      <tr>
        <th><?php echo __('Blog name'); ?></th>
        <td>
          <?php echo \Fc2blog\Web\Html::input($request, 'blog[name]', 'text'); ?>
          <?php if (isset($errors['blog']['name'])): ?><?php echo $errors['blog']['name']; ?><?php endif; ?>
        </td>
      </tr>
      <tr>
        <th><?php echo __('Nickname'); ?></th>
        <td>
          <?php echo \Fc2blog\Web\Html::input($request, 'blog[nickname]', 'text'); ?>
          <?php if (isset($errors['blog']['nickname'])): ?><?php echo $errors['blog']['nickname']; ?><?php endif; ?>
        </td>
      </tr>
      <tr>
        <td class="form-button" colspan="2">
          <input type="submit" value="<?php echo __('Register'); ?>" />
        </td>
      </tr>
    </tbody>
  </table>

</form>

