<header><h2><?php echo __('I want to edit the plugin'); ?></h2></header>

<form method="POST" id="sys-blog-plugin-form" class="admin-form">

  <input type="hidden" name="id" value="<?php echo $request->get('id'); ?>" />
  <?php echo \Fc2blog\Web\Html::input('blog_plugin[device_type]', 'hidden'); ?>
  <?php echo \Fc2blog\Web\Html::input('blog_plugin[category]', 'hidden'); ?>
  <?php echo \Fc2blog\Web\Html::input('sig', 'hidden', array('value'=>\Fc2blog\Web\Session::get('sig'))); ?>
  <?php if (isset($errors['blog_plugin']['device_type'])): ?><p class="error"><?php echo $errors['blog_plugin']['device_type']; ?></p><?php endif; ?>
  <?php if (isset($errors['blog_plugin']['category'])): ?><p class="error"><?php echo $errors['blog_plugin']['category']; ?></p><?php endif; ?>

  <table>
    <tr>
      <th><?php echo __('Plugin name'); ?></th>
      <td>
        <?php echo \Fc2blog\Web\Html::input('blog_plugin[title]', 'text'); ?>
        <?php if (isset($errors['blog_plugin']['title'])): ?><p class="error"><?php echo $errors['blog_plugin']['title']; ?></p><?php endif; ?>
      </td>
    </tr>
    <tr>
      <th><?php echo __('Character color setting of title statement'); ?></th>
      <td>
        <?php echo \Fc2blog\Web\Html::input('blog_plugin[title_align]', 'select', array('options'=>\Fc2blog\Model\BlogPluginsModel::getAttributeAlign())); ?>
        <?php if (isset($errors['blog_plugin']['title_align'])): ?><p class="error"><?php echo $errors['blog_plugin']['title_align']; ?></p><?php endif; ?>
        <?php echo \Fc2blog\Web\Html::input('blog_plugin[title_color]', 'radio', array('options'=>\Fc2blog\Model\BlogPluginsModel::getAttributeColor())); ?>
        <?php if (isset($errors['blog_plugin']['title_color'])): ?><p class="error"><?php echo $errors['blog_plugin']['title_color']; ?></p><?php endif; ?>
      </td>
    </tr>
    <tr>
      <th><?php echo __('Character color setting of content statement'); ?></th>
      <td>
        <?php echo \Fc2blog\Web\Html::input('blog_plugin[contents_align]', 'select', array('options'=>\Fc2blog\Model\BlogPluginsModel::getAttributeAlign())); ?>
        <?php if (isset($errors['blog_plugin']['contents_align'])): ?><p class="error"><?php echo $errors['blog_plugin']['contents_align']; ?></p><?php endif; ?>
        <?php echo \Fc2blog\Web\Html::input('blog_plugin[contents_color]', 'radio', array('options'=>\Fc2blog\Model\BlogPluginsModel::getAttributeColor())); ?>
        <?php if (isset($errors['blog_plugin']['contents_color'])): ?><p class="error"><?php echo $errors['blog_plugin']['contents_color']; ?></p><?php endif; ?>
      </td>
    </tr>
    <tr>
      <th><?php echo __('Remodeling of the plug-in'); ?></th>
      <td>
        <div><a href="javascript:void(0);" onclick="$(this).parent().hide().next().show(); return false;"><?php echo __('I will do the editing of [HTML]. (For advanced users)'); ?></a></div>
        <div style="display: none;">
          <?php echo \Fc2blog\Web\Html::input('blog_plugin[contents]', 'textarea'); ?>
          <?php if (isset($errors['blog_plugin']['contents'])): ?><p class="error"><?php echo $errors['blog_plugin']['contents']; ?></p><?php endif; ?>
        </div>
      </td>
    </tr>
  </table>

  <p class="form-button center">
    <input type="submit" value="<?php echo __('Update'); ?>" id="sys-blog-plugin-form-submit" />
    <input type="button" value="<?php echo __('Preview'); ?>" id="sys-blog-plugin-form-preview" />
  </p>

</form>

<script>
$(function(){
  // form内でEnterしてもsubmitさせない
  common.formEnterNonSubmit('sys-blog-plugin-form');

  // プレビュー処理を行う
  $('#sys-blog-plugin-form-preview').click(function(){
    <?php $device_key = \Fc2blog\Config::get('DEVICE_FC2_KEY.' . $request->get('blog_plugin.device_type')); ?>
    var action = '<?php echo \Fc2blog\App::userURL(array('controller'=>'Entries', 'action'=>'preview', 'blog_id'=>\Fc2blog\Web\Session::get('blog_id'), $device_key=>1), false, true); ?>';
    $('#sys-blog-plugin-form').prop('action', action);
    $('#sys-blog-plugin-form').prop('target', '_preview');
    $('#sys-blog-plugin-form').submit();
  });

  // submit処理を行う
  $('#sys-blog-plugin-form-submit').click(function(){
    var action = '<?php echo \Fc2blog\Web\Html::url(array('controller'=>'BlogPlugins', 'action'=>'edit')); ?>';
    $('#sys-blog-plugin-form').prop('action', action);
    $('#sys-blog-plugin-form').prop('target', '_self');
  });
});
</script>

<?php echo $this->display('BlogPlugins/form_js.html'); ?>

