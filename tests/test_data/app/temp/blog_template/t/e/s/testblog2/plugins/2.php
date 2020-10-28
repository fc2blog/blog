<ul>
  <?php if(!isset($t_comments)) $t_comments = \Fc2blog\Model\Model::load('Comments')->getTemplateRecentCommentList($request, $blog_id); ?><?php if (!empty($t_comments)) foreach($t_comments as $t_comment) { ?>
  <li>
    <a href="<?php if(isset($t_comment['link'])) echo $t_comment['link']; ?>#comment<?php if(isset($t_comment['id'])) echo $t_comment['id']; ?>" title="<?php if(isset($t_comment['title'])) echo $t_comment['title']; ?>"><?php if(isset($t_comment['name'])) echo $t_comment['name']; ?>:<?php if(isset($t_comment['entry_title'])) echo $t_comment['entry_title']; ?> (<?php if(isset($t_comment['month'])) echo $t_comment['month']; ?>/<?php if(isset($t_comment['day'])) echo $t_comment['day']; ?>)</a>
  </li>
  <?php } ?>
</ul>