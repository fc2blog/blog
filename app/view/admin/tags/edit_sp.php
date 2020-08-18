<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('I want to edit tags'); ?></span></h1></header>

<h2><span class="h2_inner"><?php echo __('Edit tag'); ?></span></h2>
<form method="POST" id="sys-tag-form" class="admin-form">
  <input type="hidden" name="id" value="<?php echo $request->get('id'); ?>" />
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />
  <?php echo \Fc2blog\Web\Html::input($request, 'back_url', 'hidden', array('default'=>$request->get('back_url'))); ?>
  <div class="form_area">
    <div class="form_contents">
      <h4><?php echo __('Tag name'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'tag[name]', 'text'); ?></div>
      <?php if (isset($errors['tag']['name'])): ?><span class="error"><?php echo $errors['tag']['name']; ?></span><?php endif; ?>
      <div class="btn">
        <button type="submit" class="btn_contents positive touch"><i class="save_icon btn_icon"></i><?php echo __('Update'); ?></button>
      </div>
    </div>
  </div>
</form>
<div class="btn_area">
  <ul class="btn_area_inner">
    <li>
      <a class="btn_contents touch" href="<?php if($request->isArgs('back_url')): ?><?php echo $request->get('back_url'); ?><?php else: ?><?php echo \Fc2blog\Web\Html::url(array('controller'=>'Tags', 'action'=>'index')); ?><?php endif; ?>"><i class="return_icon btn_icon"></i><?php echo __('I Back to List'); ?></a>
    </li>
    <li>
      <a class="btn_contents touch" href="<?php echo \Fc2blog\Model\BlogsModel::getFullHostUrlByBlogId(\Fc2blog\Web\Session::get('blog_id'), \Fc2blog\Config::get('DOMAIN_USER')); ?>/<?php echo \Fc2blog\Web\Session::get('blog_id'); ?>/?tag=<?php echo ue($tag['name']); ?>" target="_blank"><i class="preview_icon btn_icon"></i><?php echo __('Check the article'); ?></a>
    </li>
  </ul>
</div>
<h2><span class="h2_inner"><?php echo __('Delete tag'); ?></span></h2>
<div class="btn_area"><ul class="btn_area_inner"><li>
  <a href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'delete', 'id'=>$request->get('id'), 'back_url'=>ue($request->get('back_url')), 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" class="btn_contents touch"
     onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><i class="delete_icon btn_icon"></i><?php echo __('Delete'); ?></a>
</li></ul></div>

