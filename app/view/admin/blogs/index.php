<header><h2><?php echo __('List of blogs'); ?></h2></header>

<div class="right mb10"><a class="admin_common_btn create_btn" href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Blogs', 'action'=>'create')); ?>"><?php echo __('Create a new blog'); ?></a></div>

<table>
  <thead>
    <tr>
      <th><?php echo __('Blog ID'); ?></th>
      <th><?php echo __('I choose the blog'); ?></th>
      <th>&nbsp;</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($blogs as $blog): ?>
    <tr>
      <td><?php echo $blog['id']; ?></td>
      <td><a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'choice', 'blog_id'=>$blog['id'])); ?>"><?php echo th($blog['name'], 20); ?></a></td>
      <td><a href="<?php echo \Fc2blog\Model\BlogsModel::getFullHostUrlByBlogId($blog['id'], \Fc2blog\Config::get('DOMAIN_USER')); ?>/<?php echo $blog['id']; ?>/" target="_blank"><?php echo __('Checking the blog'); ?></a></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>

