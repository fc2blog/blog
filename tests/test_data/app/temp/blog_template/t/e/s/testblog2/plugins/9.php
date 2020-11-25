<ul class="plugin_list">
<?php if(!isset($t_comments)) $t_comments = \Fc2blog\Model\Model::load('Comments')->getTemplateRecentCommentList($request, $blog_id); ?><?php if (!empty($t_comments)) foreach($t_comments as $t_comment) { ?>
  <li>
    <a href="/blog-entry-<?php if(isset($t_comment['entry_id'])) echo $t_comment['entry_id']; ?>.html?m2=res">
      <em><?php if(isset($t_comment['title'])) echo $t_comment['title']; ?></em><br />
      <span><?php if(isset($t_comment['year'])) echo $t_comment['year']; ?>年<?php if(isset($t_comment['month'])) echo $t_comment['month']; ?>月<?php if(isset($t_comment['day'])) echo $t_comment['day']; ?>日</span>
    </a>
  </li>
<?php } ?>
</ul>