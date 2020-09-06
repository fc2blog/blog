<?php throw new LogicException("Already converted to twig. something wrong."); ?>
<header><h2><?php echo __('Category management'); ?></h2></header>

<?php if (!$is_limit_create_category) : ?>
  <h3><?php echo __('Add category'); ?></h3>
  <form method="POST" class="admin-form">

    <table>
      <tbody>
        <tr>
          <th><?php echo __('Parent category'); ?></th>
          <td>
            <?php echo \Fc2blog\Web\Html::input($request, 'category[parent_id]', 'select', array('options' => $category_parents)); ?>
            <?php if (isset($errors['parent_id'])): ?><span class="error"><?php echo $errors['parent_id']; ?></span><?php endif; ?>
          </td>
        </tr>
        <tr>
          <th><?php echo __('Category name'); ?></th>
          <td>
            <?php echo \Fc2blog\Web\Html::input($request, 'category[name]', 'text'); ?>
            <?php if (isset($errors['name'])): ?><span class="error"><?php echo $errors['name']; ?></span><?php endif; ?>
          </td>
        </tr>
        <tr>
          <th><?php echo __('Sort by category'); ?></th>
          <td>
            <?php echo \Fc2blog\Web\Html::input($request, 'category[category_order]', 'select', array('options'=>\Fc2blog\Model\CategoriesModel::getOrderList())); ?>
            <?php if (isset($errors['category_order'])): ?><span class="error"><?php echo $errors['category_order']; ?></span><?php endif; ?>
          </td>
        </tr>
        <tr>
          <td class="form-button" colspan="2">
            <input type="submit" value="<?php echo __('Add'); ?>" />
          </td>
        </tr>
      </tbody>
    </table>
    <input type="hidden" name="sig" value="<?php echo \Fc2blog\Web\Session::get('sig'); ?>" />

  </form>
<?php endif; ?>

<?php if (!$request->get('category')) : ?>
  <h3><?php echo __('Categories'); ?></h3>
  <?php $categories = \Fc2blog\Model\Model::load('Categories')->getList($this->getBlogId($request)); ?>
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
          <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'edit', 'id'=>$category['id'])); ?>"><?php echo h($category['name']); ?>(<?php echo $category['count']; ?>)</a>
          <?php if ($category['id']!=1) : ?>
            <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'delete', 'id'=>$category['id'], 'sig'=>\Fc2blog\Web\Session::get('sig'))); ?>"
               onclick="return confirm('<?php echo __('If the child category exists\nRemove all along with the child category, but do you really want?'); ?>');"><?php echo __('Delete'); ?></a>
          <?php endif; ?>
        </li>
      <?php endif; ?>

    <?php endforeach; ?>
    <?php for (;$level>1;$level--): ?>
      </li></ul>
    <?php endfor; ?>
  </ul>
<?php endif; ?>

