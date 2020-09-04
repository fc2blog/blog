<?php throw new LogicException("Already converted to twig. something wrong."); ?>

<header><h1 class="sh_heading_main_b editor_title"><?php echo __('Edit this entry.'); ?></h1></header>

<form method="POST" id="sys-entry-form" class="admin-form">

  <input type="hidden" name="id" value="<?php echo $request->get('id'); ?>" />
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />

  <?php $this->display($request, 'Entries/form.php'); ?>

</form>

<h2><span class="h2_inner"><?php echo __('Delete entry'); ?></span></h2>
<div class="btn_area"><ul class="btn_area_inner"><li>
  <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'delete', 'id'=>$request->get('id'), 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>" class="btn_contents touch"
     onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><i class="delete_icon btn_icon"></i><?php echo __('Delete'); ?></a>
</li></ul></div>

