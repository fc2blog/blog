<header><h1 class="in_menu sh_heading_main_b"><span class="h1_title"><?php echo __('List of comments'); ?></span><span class="accordion_btn"><i class="search_icon btn_icon"></i></span></h1></header>

<div id="entry_search" class="accordion_contents" style="display: none;">
  <form method="GET" id="sys-search-form">
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_CONTROLLER'); ?>" value="Comments" />
    <input type="hidden" name="<?php echo \Fc2blog\Config::get('ARGS_ACTION'); ?>" value="index" />
    <dl class="input_search">
      <dt class="lineform_text_wrap common_input_text"><?php echo \Fc2blog\Web\Html::input('keyword', 'text'); ?></dt>
      <dd class="lineform_btn_wrap"><button type="submit" value="<?php echo __('Search'); ?>" class="lineform_btn touch"><?php echo __('Search'); ?></button></dd>
    </dl>
    <div class="select_search">
      <?php echo \Fc2blog\Web\Html::input('entry_id', 'hidden'); ?>
      <?php echo \Fc2blog\Web\Html::input('open_status', 'select', array('options'=>array(''=>__('Public state')) + \Fc2blog\Model\CommentsModel::getOpenStatusList())); ?>
      <?php echo \Fc2blog\Web\Html::input('reply_status', 'select', array('options'=>array(''=>__('Reply state')) + \Fc2blog\Model\CommentsModel::getReplyStatusList())); ?>
      <?php echo \Fc2blog\Web\Html::input('limit', 'hidden', array('default'=>\Fc2blog\Config::get('ENTRY.DEFAULT_LIMIT'))); ?>
      <?php echo \Fc2blog\Web\Html::input('page', 'hidden', array('default'=>0)); ?>
      <?php echo \Fc2blog\Web\Html::input('order', 'hidden', array('default'=>'posted_at_desc')); ?>
    </div>
  </form>
</div>
<script src="/js/admin/search_form.js" type="text/javascript" charset="utf-8"></script>

<?php $reply_status_list = \Fc2blog\Model\CommentsModel::getReplyStatusList(); ?>
<ul class="link_list">
<?php foreach($comments as $comment): ?>
  <li class="link_list_item">
    <a href="<?php echo \Fc2blog\Web\Html::url($request, array('action'=>'reply', 'id'=>$comment['id'])); ?>" class="common_next_link next_bg">
      <dl>
        <dt class="item_title"><?php if ($comment['reply_status']==\Fc2blog\Config::get('COMMENT.REPLY_STATUS.UNREAD')) : ?><span class="red new">New</span><?php endif; ?><?php echo d(th($comment['title'], 20), __('No title')); ?></dt>
        <dd class="item_time"><i class="entry_time detail_icon"></i><time><?php echo df($comment['updated_at'], 'y-m-d'); ?></time></dd>
        <dd class="comment"><i class="entry_user detail_icon"></i>
          <?php if ($comment['name']!=''): ?><?php echo th($comment['name'], 10); ?><?php else: ?><?php echo __('Unknown'); ?><?php endif; ?>
        </dd>
        <dd class="state cm_entry"><i class="entry_state detail_icon"></i><?php echo th($comment['entry_title'], 20); ?></dd>
        <dd class="state">
          <?php if ($comment['reply_status']==\Fc2blog\Config::get('COMMENT.REPLY_STATUS.UNREAD')) : ?>
            <span class="no_reply"><?php echo __('Not yet read'); ?></span>
          <?php endif; ?>
          <?php if ($comment['reply_status']==\Fc2blog\Config::get('COMMENT.REPLY_STATUS.READ')) : ?>
            <?php if ($comment['open_status']==\Fc2blog\Config::get('COMMENT.OPEN_STATUS.PRIVATE')) : ?><span class="private"><?php echo __('Reply not'); ?></span><?php else: ?><span class="no_reply"><?php echo __('Unanswered'); ?></span><?php endif; ?>
          <?php endif; ?>
          <?php if ($comment['reply_status']==\Fc2blog\Config::get('COMMENT.REPLY_STATUS.REPLY')) : ?>
            <span class="replied"><?php echo __('Answered'); ?></span>
          <?php endif; ?>
        </dd>
      </dl>
    </a>
  </li>
<?php endforeach; ?>
</ul>

<?php $this->display($request, 'Common/paging.php', array('paging' => $paging)); ?>

