<header><h2><?php echo __('I want to create a template'); ?></h2></header>

<form method="POST" id="sys-blog-template-form" class="admin-form">

  <?php echo \Fc2blog\Web\Html::input('blog_template[device_type]', 'hidden'); ?>
  <?php echo \Fc2blog\Web\Html::input('sig', 'hidden', array('value'=>\Fc2blog\Web\Session::get('sig'))); ?>
  <?php if (isset($errors['blog_template']['device_type'])): ?><p class="error"><?php echo $errors['blog_template']['device_type']; ?></p><?php endif; ?>

  <h3><?php echo __('Template name'); ?></h3>
  <div>
    <?php echo \Fc2blog\Web\Html::input('blog_template[title]', 'text'); ?>
    <?php if (isset($errors['blog_template']['title'])): ?><p class="error"><?php echo $errors['blog_template']['title']; ?></p><?php endif; ?>
  </div>
  <h3>HTML</h3>
  <div>
    <?php echo \Fc2blog\Web\Html::input('blog_template[html]', 'textarea'); ?>
    <?php if (isset($errors['blog_template']['html'])): ?><p class="error"><?php echo $errors['blog_template']['html']; ?></p><?php endif; ?>
  </div>
  <h3>CSS</h3>
  <div>
    <?php echo \Fc2blog\Web\Html::input('blog_template[css]', 'textarea'); ?>
    <?php if (isset($errors['blog_template']['css'])): ?><p class="error"><?php echo $errors['blog_template']['css']; ?></p><?php endif; ?>
  </div>

  <p class="form-button">
    <input type="submit" value="<?php echo __('Add'); ?>" id="sys-blog-template-form-submit" />
    <input type="button" value="<?php echo __('Preview'); ?>" id="sys-blog-template-form-preview" />
  </p>

</form>

<script>
$(function(){
  // form内でEnterしてもsubmitさせない
  common.formEnterNonSubmit('sys-blog-template-form');

  // プレビュー処理を行う
  $('#sys-blog-template-form-preview').click(function(){
    var action = '<?php echo \Fc2blog\App::userURL(array('controller'=>'Entries', 'action'=>'preview', 'blog_id'=>\Fc2blog\Web\Session::get('blog_id')), false, true); ?>';
    $('#sys-blog-template-form').prop('action', action);
    $('#sys-blog-template-form').prop('target', '_preview');
    $('#sys-blog-template-form').submit();
  });

  // submit処理を行う
  $('#sys-blog-template-form-submit').click(function(){
    var action = '<?php echo \Fc2blog\Web\Html::url(array('controller'=>'BlogTemplates', 'action'=>'create')); ?>';
    $('#sys-blog-template-form').prop('action', action);
    $('#sys-blog-template-form').prop('target', '_self');
  });
});
</script>

<?php echo $this->display('BlogTemplates/form_js.html'); ?>

