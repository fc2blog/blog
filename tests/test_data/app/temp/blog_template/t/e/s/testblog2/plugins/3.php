<ul>
  <?php if(!isset($t_archives)) $t_archives = \Fc2blog\Model\Model::load('Entries')->getArchives($blog_id); ?><?php if (!empty($t_archives)) foreach($t_archives as $t_archive) { ?>
  <li>
    <a href="<?php if(!empty($t_archive)) echo \Fc2blog\Web\Html::url($request, array('blog_id'=>$blog_id, 'action'=>'date', 'date'=>$t_archive['year'] . $t_archive['month'])); ?>" title="<?php if(!empty($t_archive)) echo $t_archive['year']; ?>年<?php if(!empty($t_archive)) echo $t_archive['month']; ?>月"><?php if(!empty($t_archive)) echo $t_archive['year']; ?>年<?php if(!empty($t_archive)) echo $t_archive['month']; ?>月 (<?php if(!empty($t_archive)) echo $t_archive['count']; ?>)</a>
  </li>
  <?php } ?>
</ul>