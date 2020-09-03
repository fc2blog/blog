<?php throw new LogicException("Already converted to twig. something wrong."); ?>
<header><h2 class="editor_title"><?php echo __('Write a new entry'); ?></h2></header>

<form method="POST" id="sys-entry-form" class="admin-form">

  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />

  <?php $this->display($request, 'Entries/form.php'); ?>

</form>

