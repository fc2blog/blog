<header class="comment-form"><?php echo __('I will post a comment'); ?></header>

<form method="POST" id="sys-comment-form" class="user-form">

  <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="Entries" />
  <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="comment_regist" />

  <?php echo \Fc2blog\Web\Html::input('comment[entry_id]', 'hidden'); ?>

  <dl class="vertical-form">
    <dt><?php echo __('Name'); ?></dt>
    <dd>
      <?php echo \Fc2blog\Web\Html::input('comment[name]', 'text'); ?>
      <?php if (isset($errors['comment']['name'])): ?><p class="error"><?php echo $errors['comment']['name']; ?></p><?php endif; ?>
    </dd>
    <dt><?php echo __('Title'); ?></dt>
    <dd>
      <?php echo \Fc2blog\Web\Html::input('comment[title]', 'text'); ?>
      <?php if (isset($errors['comment']['title'])): ?><p class="error"><?php echo $errors['comment']['title']; ?></p><?php endif; ?>
    </dd>
    <dt><?php echo __('E-mail'); ?></dt>
    <dd>
      <?php echo \Fc2blog\Web\Html::input('comment[mail]', 'text'); ?>
      <?php if (isset($errors['comment']['mail'])): ?><p class="error"><?php echo $errors['comment']['mail']; ?></p><?php endif; ?>
    </dd>
    <dt>URL</dt>
    <dd>
      <?php echo \Fc2blog\Web\Html::input('comment[url]', 'text'); ?>
      <?php if (isset($errors['comment']['url'])): ?><p class="error"><?php echo $errors['comment']['url']; ?></p><?php endif; ?>
    </dd>
    <dt><?php echo __('Comment'); ?></dt>
    <dd>
      <?php echo \Fc2blog\Web\Html::input('comment[body]', 'textarea'); ?>
      <?php if (isset($errors['comment']['body'])): ?><p class="error"><?php echo $errors['comment']['body']; ?></p><?php endif; ?>
    </dd>
    <dt><?php echo __('Password'); ?></dt>
    <dd>
      <?php echo \Fc2blog\Web\Html::input('comment[password]', 'password'); ?>
      <?php if (isset($errors['comment']['password'])): ?><p class="error"><?php echo $errors['comment']['password']; ?></p><?php endif; ?>
    </dd>
    <dt><?php echo __('Secret'); ?></dt>
    <dd>
      <?php \Fc2blog\Model\Model::load('Comments'); ?>
      <?php echo \Fc2blog\Web\Html::input('comment[open_status]', 'select', array('options'=>\Fc2blog\Model\CommentsModel::getOpenStatusUserList())); ?>
      <?php if (isset($errors['comment']['open_status'])): ?><p class="error"><?php echo $errors['comment']['open_status']; ?></p><?php endif; ?>
    </dd>
    <dt><?php echo __('Authentication keyword'); ?></dt>
    <dd>
      <img src="<?php echo \Fc2blog\Web\Html::url(array('controller'=>'common', 'action'=>'captcha')); ?>" />
      <input type="button" class="captcha_reload" value="<?php echo __('Update authentication image'); ?>" onclick="$(this).prev().attr('src', common.fwURL('common', 'captcha', {t : new Date().getTime()}));" /><br />
      <?php echo \Fc2blog\Web\Html::input('token', 'captcha'); ?>
      <p><?php echo __('Please enter the numbers written on the image'); ?></p>
      <?php if (isset($errors['token'])): ?><p class="error"><?php echo $errors['token']; ?></p><?php endif; ?>
    </dd>
  </dl>

  <p class="form-button">
    <input type="submit" value="<?php echo __('Post'); ?>" />
  </p>

</form>

