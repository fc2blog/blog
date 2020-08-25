<header><h2><?php echo __('Blog Delete'); ?></h2></header>

<?php $this->display($request, 'BlogSettings/tab.php', array('tab'=>'blog_delete')); ?>

<form method="POST" id="sys-blogs-form" class="admin-form">
  <table>
    <tbody>
      <tr>
        <th><?php echo __('Delete confirmation'); ?></th>
        <td>
          <input type="checkbox" name="blog[delete]" id="sys-blog-delete" value="1" />
          <label for="sys-blog-delete"><?php echo __('Remove'); ?></label>
        </td>
      </tr>
      <tr>
        <td class="form-button" colspan="2">
          <input type="button" value="<?php echo __('Delete'); ?>" id="sys-withdrawal" />
        </td>
      </tr>
    </tbody>
  </table>
  <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>">
</form>

<script>
$(function(){
  $('#sys-withdrawal').on('click', function(){
    if($('input[name="blog[delete]"]:checked').length!=1){
      alert('<?php echo __('Please check the box "Remove"'); ?>');
      return ;
    }
    if (confirm('<?php echo __('Can not be undone if you remove, but Are you sure you want to delete really?'); ?>')) {
      $('#sys-blogs-form').submit();
    }
  });
});
</script>
