<?php throw new LogicException("Already converted to twig. something wrong."); ?>
<?php \Fc2blog\Web\Html::addCSS('/css/sp/entry_sp.css'); ?>

<script type="text/javascript" src="/js/jquery/jquery.fc2tab.js"></script>
<script type="text/javascript">
$(function() {
  // 記事 or 追記のタブ
  $.fc2Tab({menu: '.list_switch_item', contents: '.content_wrap', classSelected: 'selected'});

  // スマフォ版の日付入力処理
  <?php
    $now = strtotime($request->get('entry.posted_at'));
    $now = $now===false ? time() : $now;
  ?>
  var dates = '<?php echo date('Y/m/d/H/i/s', $now); ?>'.split('/');
  $('#sys-posted_at-year').val(dates[0]);
  $('#sys-posted_at-month').val(dates[1]);
  $('#sys-posted_at-day').val(dates[2]);
  $('#sys-posted_at-hour').val(dates[3]);
  $('#sys-posted_at-minute').val(dates[4]);
  $('#sys-posted_at-second').val(dates[5]);

  // 入力変更があった場合 hiddenへ値を反映
  $('#sys-posted_at-year, #sys-posted_at-month, #sys-posted_at-day, #sys-posted_at-hour, #sys-posted_at-minute, #sys-posted_at-second').on('change', function(){
    posted_at_select_to_input();
  });

  // 公開設定にイベント追加
  $('input[name="entry[open_status]"]').on('click', function(){
    var open_status = $(this).val();
    switch (open_status) {
      case '<?php echo \Fc2blog\Config::get('ENTRY.OPEN_STATUS.OPEN'); ?>':
      case '<?php echo \Fc2blog\Config::get('ENTRY.OPEN_STATUS.PASSWORD'); ?>':
      case '<?php echo \Fc2blog\Config::get('ENTRY.OPEN_STATUS.DRAFT'); ?>':
        $('#sys-input-posted_at').hide();
        $('#sys-select-posted_at').show();
        $('input[name="posted_at_select"]:checked').click();
        break;

      case '<?php echo \Fc2blog\Config::get('ENTRY.OPEN_STATUS.LIMIT'); ?>':
      case '<?php echo \Fc2blog\Config::get('ENTRY.OPEN_STATUS.RESERVATION'); ?>':
        $('#sys-select-posted_at').hide();
        $('#sys-input-posted_at').show();
        break;
    }
  });

  // 保存時の日時
  $('#sys-radio-entry-posted_at-1').on('click', function(){
    $('#sys-input-posted_at').hide();
    $('input[name="entry[posted_at]"]').val('');
  });
  // 日時を指定
  $('#sys-radio-entry-posted_at-2').on('click', function(){
    $('#sys-input-posted_at').show();
    posted_at_select_to_input();
  });

  // 初期表示用
  $('input[name="posted_at_select"]:checked').click();
});
function posted_at_select_to_input(){
  var posted_at = $('#sys-posted_at-year').val() + '-' + $('#sys-posted_at-month').val() + '-' + $('#sys-posted_at-day').val();
  posted_at += ' ' + $('#sys-posted_at-hour').val() + ':' + $('#sys-posted_at-minute').val() + ':' + $('#sys-posted_at-second').val();
  $('input[name="entry[posted_at]"]').val(posted_at);
}
</script>

<div class="form_area">
  <div class="form_contents common_input_text">
    <!--<?php echo __('Title'); ?>-->
    <?php echo \Fc2blog\Web\Html::input($request, 'entry[title]', 'text', array('placeholder'=>__('Article Title'))); ?>
    <?php if (isset($errors['entry']['title'])): ?><p class="error"><?php echo $errors['entry']['title']; ?></p><?php endif; ?>
  </div>
</div>

<ul class="list_switch">
  <li class="list_switch_item selected">
    <i class="article_icon btn_icon"></i><?php echo __('Body'); ?>
  </li>
  <li class="list_switch_item">
    <i class="more_icon btn_icon"></i><?php echo __('Edit a postscript'); ?>
  </li>
</ul>
<div class="content_wrap" style="display: block;">
    <div class="edit_area_box"><?php echo \Fc2blog\Web\Html::input($request, 'entry[body]', 'textarea', array('id'=>'sys-entry-body','placeholder'=>__('Body'))); ?></div>
    <?php if (isset($errors['entry']['body'])): ?><p class="error"><?php echo $errors['entry']['body']; ?></p><?php endif; ?>
</div>

<div class="content_wrap nondisplay" style="display: none; ">
  <div class="edit_area_box"><?php echo \Fc2blog\Web\Html::input($request, 'entry[extend]', 'textarea', array('id'=>'sys-entry-extend','placeholder'=>__('Edit a postscript'))); ?></div>
  <?php if (isset($errors['entry']['extend'])): ?><p class="error"><?php echo $errors['entry']['extend']; ?></p><?php endif; ?>
</div>

<section>
  <h2 class="accordion_head"><i class="accordion_icon btn_icon"></i><?php echo __('Entry settings'); ?></h2>
  <div class="accordion_inner" style="display: none;">
    <div class="form_area">
      <h3><span class="h3_inner"><?php echo __('Post type'); ?></span></h3>
      <div class="form_contents">
        <div class="radio_vertical_box">
          <?php echo \Fc2blog\Web\Html::input($request, 'entry[open_status]', 'radio', array('options'=>\Fc2blog\Model\EntriesModel::getOpenStatusList(), 'default'=>\Fc2blog\Config::get('ENTRY.OPEN_STATUS.OPEN'),'class'=>'radio_vertical_input')); ?>
          <?php if (isset($errors['entry']['open_status'])): ?><p class="error"><?php echo $errors['entry']['open_status']; ?></p><?php endif; ?>
        </div>
        <div class="sys-entry-password">
          <h4><?php echo __('Set Password'); ?></h4>
          <div class="common_input_text"><?php echo \Fc2blog\Web\Html::input($request, 'entry[password]', 'text'); ?></div>
          <p>
            <?php echo __('They are authenticated with a password of the entire If empty');  ?><br />
            <a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogSettings', 'action'=>'entry_edit')); ?>" target="_blank"><?php echo __('Passwords in the whole place'); ?></a><br />
          </p>
          <?php if (isset($errors['entry']['password'])): ?><p class="error"><?php echo $errors['entry']['password']; ?></p><?php endif; ?>
        </div>
      </div><!--/form_contents-->
    </div><!--/form_area-->
    <div class="form_area">
      <h3><span class="h3_inner"><?php echo __('Date and time'); ?></span></h3>
      <div class="form_contents">
        <div class="radio_horizontal_box">
          <ul class="form-radio-list" id="sys-select-posted_at">
            <li>
              <input type="radio" name="posted_at_select" value="1" class="common_input_radio" id="sys-radio-entry-posted_at-1" <?php if (!$request->get('entry.posted_at')) echo 'checked="checked"'; ?> />
              <label for="sys-radio-entry-posted_at-1"><?php echo __('Date and time when saving'); ?></label>
            </li>
            <li>
              <input type="radio" name="posted_at_select" value="2" class="common_input_radio" id="sys-radio-entry-posted_at-2" <?php if ($request->get('entry.posted_at')) echo 'checked="checked"'; ?> />
              <label for="sys-radio-entry-posted_at-2"><?php echo __('Specify the date and time'); ?></label>
            </li>
          </ul>
        </div>
      </div><!--/form_contents-->
      <div class="form_contents">
          <div id="sys-input-posted_at">
            <table class="entry_time_set">
              <tbody>
                <tr>
                  <td class="cell"><input type="text" id="sys-posted_at-year" class="common_input_text" /><span class="attr"><?php echo __('Year'); ?></span></td>
                  <td class="cell"><select id="sys-posted_at-month" class="common_input_select"><?php for($i=1;$i<=12;$i++): ?><option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option><?php endfor; ?></select><span class="attr"><?php echo __('Month'); ?></span></td>
                  <td class="cell"><select id="sys-posted_at-day" class="common_input_select"><?php for($i=1;$i<=31;$i++): ?><option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option><?php endfor; ?></select><span class="attr"><?php echo __('Day'); ?></span></td>
                </tr>
                <tr>
                  <td class="cell"><select id="sys-posted_at-hour" class="common_input_select"><?php for($i=0;$i<=23;$i++): ?><option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option><?php endfor; ?></select><span class="attr"><?php echo __('Hour'); ?></span></td>
                  <td class="cell"><select id="sys-posted_at-minute" class="common_input_select"><?php for($i=0;$i<=59;$i++): ?><option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option><?php endfor; ?></select><span class="attr"><?php echo __('Minute'); ?></span></td>
                  <td class="cell"><select id="sys-posted_at-second" class="common_input_select"><?php for($i=0;$i<=59;$i++): ?><option value="<?php echo sprintf('%02d', $i); ?>"><?php echo sprintf('%02d', $i); ?></option><?php endfor; ?></select><span class="attr"><?php echo __('Second'); ?></span></td>
                </tr>
            </table>
          </div>
        <?php echo \Fc2blog\Web\Html::input($request, 'entry[posted_at]', 'hidden'); ?>
        <?php if (isset($errors['entry']['posted_at'])): ?><p class="error"><?php echo $errors['entry']['posted_at']; ?></p><?php endif; ?>
      </div><!--/form_contents-->
    </div><!--/form_area-->
    <div class="form_area">
      <h3><span class="h3_inner"><?php echo __('Accept comments'); ?></span></h3>
      <div class="form_contents">
        <div class="radio_horizontal_box">
          <?php echo \Fc2blog\Web\Html::input($request, 'entry[comment_accepted]', 'radio', array('options'=>\Fc2blog\Model\EntriesModel::getCommentAcceptedList(), 'default'=>\Fc2blog\Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED'),'class'=>'common_input_radio')); ?>
        </div>
        <?php if (isset($errors['entry']['comment_accepted'])): ?><p class="error"><?php echo $errors['entry']['comment_accepted']; ?></p><?php endif; ?>
      </div><!--/form_contents-->
    </div><!--/formarea-->
    <div class="form_area">
      <h3><span class="h3_inner"><?php echo __('New paragraph'); ?></span></h3>
      <div class="form_contents">
        <div class="radio_horizontal_box">
          <?php echo \Fc2blog\Web\Html::input($request, 'entry[auto_linefeed]', 'radio', array('options'=>\Fc2blog\Model\EntriesModel::getAutoLinefeedList(), 'default'=>\Fc2blog\Config::get('ENTRY.AUTO_LINEFEED.USE'))); ?>
        </div>
        <?php if (isset($errors['entry']['auto_linefeed'])): ?><p class="error"><?php echo $errors['entry']['auto_linefeed']; ?></p><?php endif; ?>
      </div><!--/form_contents-->
    </div><!--/formarea-->
  </div><!--/accordion_inner-->
  <h2 class="accordion_head"><i class="accordion_icon btn_icon"></i><?php echo __('Category'); ?></h2>
  <div class="accordion_inner" style="display: none;">
    <div class="form_area">
      <?php $this->display($request, 'Categories/ajax_add.php', array()); ?>
    </div><!--/form_area-->
  </div><!--/accordion_inner-->
  <h2 class="accordion_head"><i class="accordion_icon btn_icon"></i><?php echo __('User tags'); ?></h2>
  <div class="accordion_inner" style="display: none;">
    <div class="form_area">
      <div class="form_contents">
        <div class="common_input_text"><input type="text" id="sys-add-tag-text" /></div>
        <div class="btn"><button type="submit" id="sys-add-tag-button" class="btn_contents positive touch" /><i class="positive_add_icon btn_icon"></i><?php echo __('Add'); ?></button></div>
        <ul id="sys-add-tags"></ul>
        <hr id="add-tag-line" />
        <ul id="sys-use-well-tags">
          <?php $tags = \Fc2blog\Model\Model::load('Tags')->getWellUsedTags($this->getBlogId($request)); ?>
          <?php foreach($tags as $key => $tag): ?><li><?php echo h($tag); ?></li><?php endforeach; ?>
        </ul>
      </div><!--/form_contents-->
    </div><!--/form_area-->
  </div><!--/accordion_inner-->
</section>

<div class="form-button btn_area">
  <ul class="btn_area_inner">
    <li><button type="submit" id="sys-entry-form-submit" class="btn_contents touch positive touch"><i class="save_icon btn_icon"></i><?php echo __('Save this entry'); ?></button></li>
    <li><button type="button" id="sys-entry-form-preview" class="btn_contents touch"><i class="preview_icon btn_icon"></i><?php echo __('Preview'); ?></button></li>
  </ul>
</div>

<?php $this->display($request, 'Entries/editor_js.php', array()); ?>

