<header><h2><?php echo __('Withdrawal process'); ?></h2></header>

<?php $this->display($request, 'Users/tab.php', array('tab'=>'withdrawal')); ?>

<form method="POST" id="sys-users-form" class="admin-form">
<input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />

<table>
  <tbody>
    <tr>
      <th><?php echo __('Unsubscribe confirmation'); ?></th>
      <td>
        <input type="checkbox" name="user[delete]" id="sys-user-delete" />
        <label for="sys-user-delete"><?php echo __('Unsubscribe'); ?></label>
      </td>
    </tr>
    <tr>
      <td class="form-button" colspan="2">
        <input type="button" value="<?php echo __('Withdrawal'); ?>" id="sys-withdrawal" />
      </td>
    </tr>
  </tbody>
</table>

</form>

<script>
$(function(){
  $('#sys-withdrawal').on('click', function(){
    if($('input[name="user[delete]"]:checked').length!=1){
      alert('<?php echo __('Please check the box "unsubscribe"'); ?>');
      return ;
    }
    if (confirm('<?php echo __('Can not be undone if you unsubscribe. Are you sure you want to unsubscribe really?'); ?>')) {
      $('#sys-users-form').submit();
    }
  });
});
</script>
