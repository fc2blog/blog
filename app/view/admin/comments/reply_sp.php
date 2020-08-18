<header><h1 class="detail sh_heading_main_b"><?php echo __('Comment'); ?><a href="#jamp1" class="btn_contents page_scroll touch"><?php echo __('Reply'); ?></a></h1></header>

<h2><span class="h2_inner"><?php echo __('Details of comment'); ?></span></h2>
<p class="output_contents">
  <span class="comment_title"><?php echo $comment['title']; ?></span>
  <span class="comment_text"><?php echo nl2br(h($comment['body'])); ?></span>
</p>
<h3><span class="h3_inner"><?php echo __('Summary'); ?></span></h3>
<dl class="output_contents comment_about">
  <dt class="about_title"><?php echo __('Public state'); ?></dt>
  <dd class="about_content">
    <?php if ($comment['open_status']==\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PUBLIC')) : ?>
      <span class="published"><?php echo __('Published'); ?></span>
    <?php elseif ($comment['open_status']==\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PENDING')) : ?>
      <span class="approval"><?php echo __('Approval pending'); ?></span>
    <?php elseif ($comment['open_status']==\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PRIVATE')) : ?>
      <span class="private"><?php echo __('Only exposed administrator'); ?></span>
    <?php endif; ?>
  </dd>
  <?php if ($comment['reply_status']==\Fc2blog\Config::get('COMMENT.REPLY_STATUS.REPLY')) : ?>
    <dt class="about_title"><?php echo __('Response time'); ?></dt>
    <dd class="about_content"><?php echo df($comment['reply_updated_at']); ?></dd>
  <?php endif; ?>
  <dt class="about_title"><?php echo __('Contributor'); ?></dt>
  <dd class="about_content"><?php echo $comment['name']; ?></dd>
  <?php if(!empty($comment['mail'])): ?>
  <dt class="about_title"><?php echo __('E-mail address'); ?></dt>
  <dd class="about_content"><?php echo $comment['mail']; ?></dd>
  <?php endif; ?>
  <?php if (!empty($comment['url'])) : ?>
  <dt class="about_title">URL</dt>
  <dd class="about_content"><a href="<?php echo $comment['url']; ?>" target="_blank"><?php echo $comment['url']; ?></a></dd>
  <?php endif; ?>
  <dt class="about_title"><?php echo __('Article name'); ?></dt>
  <dd class="about_content"><a href="<?php echo \Fc2blog\App::userURL(array('controller'=>'entries', 'action'=>'view', 'blog_id'=>$comment['blog_id'], 'id'=>$comment['entry_id'], 'sp'=>1)); ?>" target="_blank"><?php echo $comment['entry_title']; ?></a></dd>
</dl>

<div class="btn_area">
  <?php if ($comment['open_status']==\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PENDING')) : ?>
    <div class="btn">
      <a href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'approval', 'id'=>$comment['id'], 'back_url'=>ue($request->get('back_url')))); ?>"
       onclick="return confirm('<?php echo __('Are you sure you want to be approved?'); ?>');" class="btn_contents positive touch"><i class="check_icon btn_icon"></i><?php echo __('I moderate comments'); ?></a>
     </div>
  <?php endif; ?>
  <ul class="btn_area_inner">
    <li><a class="btn_contents touch" href="<?php if($request->isArgs('back_url')): ?><?php echo $request->get('back_url'); ?><?php else: ?><?php echo \Fc2blog\Web\Html::url(array('action'=>'index')); ?><?php endif; ?>"><i class="return_icon btn_icon"></i><?php echo __('I Back to List'); ?></a></li>
    <li><a class="btn_contents touch" href="<?php echo \Fc2blog\Web\Html::url(array('action'=>'delete', 'id'=>$comment['id'], 'back_url'=>ue($request->get('back_url')))); ?>" onclick="return confirm('<?php echo __('Are you sure you want to delete?'); ?>');"><i class="delete_icon btn_icon"></i><?php echo __('Delete'); ?></a></li>
  </ul>
</div>

<?php if ($comment['open_status']!=\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PRIVATE')) : ?>
  <form method="POST" id="sys-comment-form" class="admin-form">
    <h2 id="jamp1"><span class="h2_inner"><?php echo __('I will reply to comments'); ?></span></h2>
    <div class="form_area">
      <?php echo \Fc2blog\Web\Html::input('back_url', 'hidden', array('default'=>$request->get('back_url'))); ?>
      <p class="form_contents"><?php echo \Fc2blog\Web\Html::input('comment[reply_body]', 'textarea', array('class'=>'common_textarea')); ?></p>
      <?php if (isset($errors['comment']['reply_body'])): ?><p class="error"><?php echo $errors['comment']['reply_body']; ?></p><?php endif; ?>
    </div>
    <div class="btn_area">
      <?php if($comment['reply_status']==\Fc2blog\Config::get('COMMENT.REPLY_STATUS.REPLY')): ?>
        <div class="btn"><button type="submit" class="btn_contents positive touch"><i class="save_icon btn_icon"></i><?php echo __('Update'); ?></button></div>
      <?php else: ?>
        <div class="btn"><button type="submit" class="btn_contents positive touch"><i class="save_icon btn_icon"></i><?php echo __('Reply'); ?></button></div>
        <?php if($comment['open_status']==\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PENDING')): ?>
          ※<?php echo __('When you press the reply button, the message will be approved'); ?>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </form>
<?php endif; ?>

