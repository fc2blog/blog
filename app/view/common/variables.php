<?php
  $vars = get_defined_vars();
  // 表示不要分を解除
  unset($vars['request']);
  unset($vars['fw_template']);
  unset($vars['fw_is_prefix']);
?>
<p style="cursor: pointer;" onclick="$(this).next().slideToggle('fast');">使用可能変数一覧[<?php echo $fw_template; ?>]</p>
<pre style="display: none;"><?php echo htmlspecialchars(var_export($vars, true), ENT_NOQUOTES); ?></pre>
<?php unset($vars); ?>
