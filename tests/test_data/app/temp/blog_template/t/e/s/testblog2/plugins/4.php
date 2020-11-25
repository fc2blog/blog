<div>
  <?php if(!isset($t_categories)) $t_categories = \Fc2blog\Model\Model::load('Categories')->getTemplateCategories($blog_id); ?><?php if (!empty($t_categories)) foreach($t_categories as $t_category) { ?>
  <div>
    <?php if(!empty($t_category) && $t_category['is_sub_hasnext']) { ?>┣<?php } ?>
    <?php if(!empty($t_category) && $t_category['is_sub_end']) { ?>┗<?php } ?>
    <a href="<?php if(!empty($t_category)) echo \Fc2blog\Web\Html::url($request, array('action'=>'category', 'blog_id'=>$t_category['blog_id'], 'cat'=>$t_category['id'])); ?>" title="<?php if(!empty($t_category)) echo $t_category['name']; ?>"><?php if(!empty($t_category)) echo $t_category['name']; ?> (<?php if(!empty($t_category)) echo $t_category['count']; ?>)</a>
  </div>
  <?php } ?>
</div>