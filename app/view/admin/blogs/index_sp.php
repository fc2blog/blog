<header><h1 class="sh_heading_main_b"><?php echo __('List of blogs'); ?></h1></header>

<div class="btn_area">
  <div class="btn"><a class="btn_contents positive touch" href="<?php echo \Fc2blog\Web\Html::url(array('controller'=>'Blogs', 'action'=>'create')); ?>"><?php echo __('Create a new blog'); ?></a></div>
</div>

<h2><span class="h2_inner"><?php echo __('Blog ID'); ?></span></h2>

<ul class="link_list">
  <?php $blog_id = $this->getBlogId(); ?>
  <?php foreach($blogs as $blog): ?>
    <li class="link_list_item<?php if ($blog[id]==$blog_id) echo ' selected'; /*TODO 多分[id]はTypo、後で直す*/?>">
      <a href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'choice', 'blog_id'=>$blog['id'])); ?>" class="common_next_link next_bg"><?php echo $blog['id']; ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<?php $this->display('Common/paging.php', array('paging' => $paging)); ?>

