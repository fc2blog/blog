<header><h2><?php echo __('Registration of plug-in'); ?></h2></header>
<p class="header_btn">
  <a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'blog_plugins', 'action'=>'index')); ?>"><?php echo __('Plugin management'); ?></a>
</p>

<form method="POST" id="sys-plugin-form" class="admin-form">

  <input type="hidden" name="id" value="<?php echo $request->get('id'); ?>" />
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />
  <table>
    <tbody>
      <tr>
        <th><?php echo __('Plugin name'); ?></th>
        <td>
          <?php echo \Fc2blog\Web\Html::input('plugin[title]', 'text'); ?>
          <?php if (isset($errors['plugin']['title'])): ?><p class="error"><?php echo $errors['plugin']['title']; ?></p><?php endif; ?>
        </td>
      </tr>
      <tr>
        <th><?php echo __('Description'); ?></th>
        <td>
          <?php echo \Fc2blog\Web\Html::input('plugin[body]', 'textarea'); ?>
          <?php if (isset($errors['plugin']['body'])): ?><p class="error"><?php echo $errors['plugin']['body']; ?></p><?php endif; ?>
        </td>
      </tr>
  </table>

  <p class="form-button center">
    <input type="submit" value="<?php if ($blog_plugin['plugin_id']): ?><?php echo __('Update'); ?><?php else: ?><?php echo __('Register'); ?><?php endif; ?>" />
  </p>

</form>

