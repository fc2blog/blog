<ul class="plugin_list">
<?php if(!isset($t_recents)) $t_recents = \Fc2blog\Model\Model::load('Entries')->getTemplateRecents($request, $blog_id); ?><?php if (!empty($t_recents)) foreach($t_recents as $t_recent) { ?>
  <li>
    <a href="<?php if(isset($t_recent['link'])) echo $t_recent['link']; ?>" title="<?php if(isset($t_recent['title'])) echo $t_recent['title']; ?>">
      <em><?php if(isset($t_recent['title'])) echo $t_recent['title']; ?></em><br />
      <span><?php if(isset($t_recent['year'])) echo $t_recent['year']; ?>年<?php if(isset($t_recent['month'])) echo $t_recent['month']; ?>月<?php if(isset($t_recent['day'])) echo $t_recent['day']; ?>日</span>
    </a>
  </li>
<?php } ?>
</ul>