<header><h2><?php echo __('User setting'); ?></h2></header>

<?php $this->display('Users/tab.php', array('tab'=>'edit')); ?>

<form method="POST" id="sys-users-form" class="admin-form">

<table>
  <tbody>
    <tr>
      <th><?php echo __('Change Password'); ?></th>
      <td>
        <?php echo \Fc2blog\Web\Html::input('user[password]', 'password'); ?>
        <?php if (isset($errors['user']['password'])): ?><p class="error"><?php echo $errors['user']['password']; ?></p><?php endif; ?>
      </td>
    </tr>
    <tr>
      <th><?php echo __('Blog ID at login'); ?></th>
      <td>
        <?php echo \Fc2blog\Web\Html::input('user[login_blog_id]', 'select', array('options'=>\Fc2blog\Model\Model::load('Blogs')->getListByUserId(\Fc2blog\Web\Session::get('user_id')))); ?>
        <?php if (isset($errors['user']['login_blog_id'])): ?><p class="error"><?php echo $errors['user']['login_blog_id']; ?></p><?php endif; ?>
      </td>
    </tr>
    <tr>
      <td class="form-button" colspan="2">
        <input type="submit" value="<?php echo __('Update'); ?>" />
      </td>
    </tr>
  </tbody>
</table>

</form>

