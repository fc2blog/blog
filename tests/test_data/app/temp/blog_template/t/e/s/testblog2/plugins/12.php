<ul class="plugin_list plugin-multi-tree">
  <?php if(!isset($t_categories)) $t_categories = \Fc2blog\Model\Model::load('Categories')->getTemplateCategories($blog_id); ?><?php if (!empty($t_categories)) foreach($t_categories as $t_category) { ?>
  <li><a href="<?php if(!empty($t_category)) echo \Fc2blog\Web\Html::url($request, array('action'=>'category', 'blog_id'=>$t_category['blog_id'], 'cat'=>$t_category['id'])); ?>" title="<?php if(!empty($t_category)) echo $t_category['name']; ?>"><?php if(!empty($t_category)) echo $t_category['name']; ?>(<?php if(!empty($t_category)) echo $t_category['count']; ?>)</a></li>
  <?php if(!empty($t_category) && $t_category['is_parent']) { ?>
    <li><ul>
  <?php } ?>
  <?php if(!empty($t_category) && $t_category['is_nosub']) { ?><?php if(!empty($t_category) && isset($t_category['climb_hierarchy'])) for($category_index=0;$category_index<$t_category['climb_hierarchy'];$category_index++) { ?>
    </ul></li>
  <?php } ?><?php } ?>
  <?php } ?>
</ul>

<style>
ul.plugin-multi-tree li{
  list-style: none;
}
ul.plugin-multi-tree li a:before{
  content: '・';
}
ul.plugin-multi-tree ul{
  margin-left: 15px;
}
</style>