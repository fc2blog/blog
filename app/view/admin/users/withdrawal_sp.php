<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('Withdrawal process'); ?></span></h1></header>

<?php $this->display('Users/tab.php', array('tab'=>'withdrawal')); ?>

<h2 id="blog_settings"><span class="h2_inner"><?php echo __('Unsubscribe confirmation'); ?></span></h2>
<form method="POST" id="sys-users-form" class="admin-form">
  <div class="form_area">
    <div class="form_contents"><?php echo __('If you want to unsubscribe, please tap the "Withdrawal" button and check the "Unsubscribe confirmation".'); ?></div>
    <div class="form_contents">
      <div class="checkbox_btn touch">
        <div class="checkbox_btn_inner">
          <input type="checkbox" name="user[delete]" id="sys-user-delete" class="checkbox_btn_input" value="1" />
          <span class="checkbox_bg"></span>
        </div>
        <label for="sys-user-delete" class="checkbox_btn_label"><?php echo __('Unsubscribe confirmation'); ?></label>
      </div>
    </div>
  </div>
  <div class="form_area">
    <div class="form_contents">
      <div clas="btn">
        <button type="button" class="btn_contents touch" id="sys-withdrawal"><?php echo __('Withdrawal'); ?><i class="delete_icon btn_icon"></i></button>
      </div>
    </div>
  </div>
</form>

<script>
$(function(){
  $('#sys-withdrawal').on('click', function(){
    if($('input[name="user[delete]"]:checked').length!=1){
      alert('<?php echo __('Please check the box "Unsubscribe confirmation"'); ?>');
      return ;
    }
    if (confirm('<?php echo __('Can not be undone if you unsubscribe. Are you sure you want to unsubscribe really?'); ?>')) {
      $('#sys-users-form').submit();
    }
  });
});
</script>
