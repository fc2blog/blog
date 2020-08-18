<?php \Fc2blog\Web\Html::addCSS('/css/sp/category_sp.css', array('media'=>'all')); ?>

<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('Category management'); ?></span></h1></header>

<h2><span class="h2_inner"><?php echo __('Add category'); ?></span></h2>
<form method="POST" class="admin-form">
  <div class="form_area">
    <div class="form_contents">
      <h4><?php echo __('Parent category'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('category[parent_id]', 'select', array('options' => $category_parents)); ?>
      <?php if (isset($errors['parent_id'])): ?><span class="error"><?php echo $errors['parent_id']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Category name'); ?></h4>
      <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input('category[name]', 'text'); ?></div>
      <?php if (isset($errors['name'])): ?><span class="error"><?php echo $errors['name']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <h4><?php echo __('Sort by category'); ?></h4>
      <?php echo \Fc2blog\Web\Html::input('category[category_order]', 'select', array('options' => \Fc2blog\Model\CategoriesModel::getOrderList())); ?>
      <?php if (isset($errors['category_order'])): ?><span class="error"><?php echo $errors['category_order']; ?></span><?php endif; ?>
    </div>
    <div class="form_contents">
      <div class="btn">
        <button type="submit" class="btn_contents positive touch"><i class="positive_add_icon btn_icon"></i><?php echo __('Add'); ?></button>
      </div>
    </div>
  </div>
  <?php echo \Fc2blog\Web\Html::input('sig', 'hidden', array('value' => \Fc2blog\Web\Session::get('sig'))); ?>
</form>

<?php if (!$request->get('category')) : ?>
  <h2><span class="h2_inner"><?php echo __('Categories'); ?></span></h2>
  <?php $categories = \Fc2blog\Model\Model::load('Categories')->getList($this->getBlogId()); ?>
  <div class="category_list">
    <ul id="sys-category-list">
      <?php $level = 1; ?>
      <?php foreach($categories as $category): ?>

        <?php if ($level<$category['level']): ?>
          <?php $level = $category['level']; ?>
          <li><ul>
        <?php endif; ?>

        <?php if ($level>$category['level']): ?>
          <?php for (;$level>$category['level'];$level--): ?>
            </li></ul>
          <?php endfor; ?>
        <?php endif; ?>

        <?php if ($level==$category['level']): ?>
          <li>
            <p>
              <a href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'edit', 'id'=>$category['id'])); ?>"><?php echo h($category['name']); ?>(<?php echo $category['count']; ?>)</a>
            </p>
          </li>
        <?php endif; ?>

      <?php endforeach; ?>
      <?php for (;$level>1;$level--): ?>
        </li></ul>
      <?php endfor; ?>
    </ul>
  </div>
<?php endif; ?>

