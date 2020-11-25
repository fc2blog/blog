<ul>
  <?php if(!isset($t_recents)) $t_recents = \Fc2blog\Model\Model::load('Entries')->getTemplateRecents($request, $blog_id); ?><?php if (!empty($t_recents)) foreach($t_recents as $t_recent) { ?>
    <li>
      <a href="<?php if(isset($t_recent['link'])) echo $t_recent['link']; ?>" title="<?php if(isset($t_recent['title'])) echo $t_recent['title']; ?>"><?php if(isset($t_recent['title'])) echo $t_recent['title']; ?> (<?php if(isset($t_recent['month'])) echo $t_recent['month']; ?>/<?php if(isset($t_recent['day'])) echo $t_recent['day']; ?>)</a>
    </li>
  <?php } ?>
</ul>