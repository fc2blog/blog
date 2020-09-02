<?php throw new LogicException("Already converted to twig. something wrong."); ?>
<header><h2><?php echo __('Login'); ?></h2></header>

<form method="POST" class="admin-form">

<table id="id_form">
  <tbody>
    <tr>
      <th><?php echo __('Login ID'); ?></th>
      <td>
        <?php echo \Fc2blog\Web\Html::input($request, 'user[login_id]', 'text'); ?>
        <?php if (isset($errors['login_id'])): ?><?php echo $errors['login_id']; ?><?php endif; ?>
      </td>
    </tr>
    <tr>
      <th><?php echo __('Password'); ?></th>
      <td>
        <?php echo \Fc2blog\Web\Html::input($request, 'user[password]', 'password'); ?>
        <?php if (isset($errors['password'])): ?><?php echo $errors['password']; ?><?php endif; ?>
      </td>
    </tr>
    <tr>
      <td class="form-button" colspan="2">
        <input type="submit" value="<?php echo __('Login'); ?>" />
      </td>
    </tr>
  </tbody>
</table>
</form>

