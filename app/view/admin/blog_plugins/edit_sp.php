<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('I want to edit the plugin'); ?></span></h1></header>

<form method="POST" id="sys-blog-plugin-form" class="admin-form">

  <input type="hidden" name="id" value="<?php echo $request->get('id'); ?>" />
  <?php echo \Fc2blog\Web\Html::input($request, 'blog_plugin[device_type]', 'hidden'); ?>
  <?php echo \Fc2blog\Web\Html::input($request, 'blog_plugin[category]', 'hidden'); ?>
  <?php echo \Fc2blog\Web\Html::input($request, 'sig', 'hidden', array('value'=>\Fc2blog\Web\Session::get('sig'))); ?>
  <?php if (isset($errors['blog_plugin']['device_type'])): ?><p class="error"><?php echo $errors['blog_plugin']['device_type']; ?></p><?php endif; ?>
  <?php if (isset($errors['blog_plugin']['category'])): ?><p class="error"><?php echo $errors['blog_plugin']['category']; ?></p><?php endif; ?>

  <div class="form_area">
    <h2><span class="h2_inner"><?php echo __('Plugin name'); ?></span></h2>
    <div class="form_contents">
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'blog_plugin[title]', 'text'); ?></div>
      <?php if (isset($errors['blog_plugin']['title'])): ?><span class="error"><?php echo $errors['blog_plugin']['title']; ?></span><?php endif; ?>
    </div>
  </div>
  <div class="form_area">
    <h2><span class="h2_inner"><?php echo __('Character color setting of title statement'); ?></span></h2>
    <div class="form_contents">
      <?php echo \Fc2blog\Web\Html::input($request, 'blog_plugin[title_align]', 'select', array('options'=>\Fc2blog\Model\BlogPluginsModel::getAttributeAlign())); ?>
      <?php if (isset($errors['blog_plugin']['title_align'])): ?><p class="error"><?php echo $errors['blog_plugin']['title_align']; ?></p><?php endif; ?>
      <?php echo \Fc2blog\Web\Html::input($request, 'blog_plugin[title_color]', 'select', array('options'=>\Fc2blog\Model\BlogPluginsModel::getAttributeColor(true))); ?>
      <?php if (isset($errors['blog_plugin']['title_color'])): ?><p class="error"><?php echo $errors['blog_plugin']['title_color']; ?></p><?php endif; ?>
    </div>
  </div>
  <div class="form_area">
    <h2><span class="h2_inner"><?php echo __('Character color setting of content statement'); ?></span></h2>
    <div class="form_contents">
      <?php echo \Fc2blog\Web\Html::input($request, 'blog_plugin[contents_align]', 'select', array('options'=>\Fc2blog\Model\BlogPluginsModel::getAttributeAlign())); ?>
      <?php if (isset($errors['blog_plugin']['contents_align'])): ?><p class="error"><?php echo $errors['blog_plugin']['contents_align']; ?></p><?php endif; ?>
      <?php echo \Fc2blog\Web\Html::input($request, 'blog_plugin[contents_color]', 'select', array('options'=>\Fc2blog\Model\BlogPluginsModel::getAttributeColor(true))); ?>
      <?php if (isset($errors['blog_plugin']['contents_color'])): ?><p class="error"><?php echo $errors['blog_plugin']['contents_color']; ?></p><?php endif; ?>
    </div>
  </div>
  <div class="form_area">
    <h2><span class="h2_inner"><?php echo __('Remodeling of the plug-in'); ?></span></h2>
     <div class="edit_area_box">
     <div><a href="javascript:void(0);" onclick="$(this).parent().hide().next().show(); return false;"><?php echo __('I will do the editing of [HTML]. (For advanced users)'); ?></a></div>
      <div style="display: none;">
        <?php echo \Fc2blog\Web\Html::input($request, 'blog_plugin[contents]', 'textarea'); ?>
        <?php if (isset($errors['blog_plugin']['contents'])): ?><p class="error"><?php echo $errors['blog_plugin']['contents']; ?></p><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="form-button btn_area">
    <ul class="btn_area_inner">
      <li><button type="submit" id="sys-blog-plugin-form-submit" class="btn_contents touch positive touch"><i class="save_icon btn_icon"></i><?php echo __('Update'); ?></button></li>
      <li><button type="button" id="sys-blog-plugin-form-preview" class="btn_contents touch"><i class="preview_icon btn_icon"></i><?php echo __('Preview'); ?></button></li>
    </ul>
    <ul class="btn_area_inner">
      <li>
        <a class="btn_contents touch" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'blog_plugins', 'action'=>'index', 'device_type'=>\Fc2blog\Config::get('DEVICE_SP'))); ?>"><i class="return_icon btn_icon"></i><?php echo __('I Back to List'); ?></a>
      </li>
    </ul>
  </div>
</form>

<h2><span class="h2_inner"><?php echo __('Delete plugin'); ?></span></h2>
<div class="btn_area"><ul class="btn_area_inner"><li>
  <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'delete', 'id'=>$request->get('id'), 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" class="btn_contents touch"
     onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><i class="delete_icon btn_icon"></i><?php echo __('Delete'); ?></a>
</li></ul></div>

<script>
$(function(){
  // form内でEnterしてもsubmitさせない
  common.formEnterNonSubmit('sys-blog-plugin-form');

  // プレビュー処理を行う
  $('#sys-blog-plugin-form-preview').click(function(){
    <?php $device_key = \Fc2blog\Config::get('DEVICE_FC2_KEY.' . $request->get('blog_plugin.device_type')); ?>
    var action = '<?php echo \Fc2blog\App::userURL($request,array('controller'=>'Entries', 'action'=>'preview', 'blog_id'=>\Fc2blog\Web\Session::get('blog_id'), $device_key=>1), false, true); ?>';
    $('#sys-blog-plugin-form').prop('action', action);
    $('#sys-blog-plugin-form').prop('target', '_preview');
    $('#sys-blog-plugin-form').submit();
  });

  // submit処理を行う
  $('#sys-blog-plugin-form-submit').click(function(){
    var action = '<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogPlugins', 'action'=>'edit')); ?>';
    $('#sys-blog-plugin-form').prop('action', action);
    $('#sys-blog-plugin-form').prop('target', '_self');
  });
});
</script>

<?php echo $this->display($request, 'BlogPlugins/form_js.php'); ?>

