<?php

$config = array();

// ループ文の置き換え用
$config['fc2_template_foreach'] = array(
  'topentry'      => '<?php if(!empty($entries) && empty($titlelist_area)) foreach($entries as $entry) { ?>',
  'titlelist'     => '<?php if(!empty($entries) && !empty($titlelist_area)) foreach($entries as $entry) { ?>',
  'comment'       => '<?php if(!empty($comments)) foreach($comments as $comment) { ?>',
  'comment_list'  => '<?php if(!empty($comments)) foreach($comments as $comment) { ?>',
  'category_list' => '<?php if(!empty($entry[\'categories\'])) foreach($entry[\'categories\'] as $category) { ?>',
  'tag_list'      => '<?php if(!empty($entry[\'tags\'])) foreach($entry[\'tags\'] as $tag) { ?>',
  // 最新記事一覧(プラグイン表示用)
  'recent'        => '<?php if(!isset($t_recents)) $t_recents = Model::load(\'Entries\')->getTemplateRecents($blog_id); ?><?php if (!empty($t_recents)) foreach($t_recents as $t_recent) { ?>',
  // カテゴリー(プラグイン表示用)
  'category'      => '<?php if(!isset($t_categories)) $t_categories = Model::load(\'Categories\')->getTemplateCategories($blog_id); ?><?php if (!empty($t_categories)) foreach($t_categories as $t_category) { ?>',
  // アーカイブ(プラグイン表示用)
  'archive'       => '<?php if(!isset($t_archives)) $t_archives = Model::load(\'Entries\')->getArchives($blog_id); ?><?php if (!empty($t_archives)) foreach($t_archives as $t_archive) { ?>',
  // 新着順のコメント
  'rcomment'      => '<?php if(!isset($t_comments)) $t_comments = Model::load(\'Comments\')->getTemplateRecentCommentList($blog_id); ?><?php if (!empty($t_comments)) foreach($t_comments as $t_comment) { ?>',
  // カテゴリー
  'category_multi_sub_end' => '<?php if(!empty($t_category) && isset($t_category[\'climb_hierarchy\'])) for($category_index=0;$category_index<$t_category[\'climb_hierarchy\'];$category_index++) { ?>',
  // カレンダー
  'calendar'      => '<?php if(!isset($t_calendars)) $t_calendars = Model::load(\'Entries\')->getTemplateCalendar($blog_id, date(\'Y\', strtotime($now_date)), date(\'m\', strtotime($now_date))); ?><?php if (!empty($t_calendars)) foreach($t_calendars as $t_calendar) { ?>',
  'calender'      => '<?php if(!isset($t_calendars)) $t_calendars = Model::load(\'Entries\')->getTemplateCalendar($blog_id, date(\'Y\', strtotime($now_date)), date(\'m\', strtotime($now_date))); ?><?php if (!empty($t_calendars)) foreach($t_calendars as $t_calendar) { ?>',
  // プラグイン系
  'plugin_first'   => '<?php if(!isset($t_plugins_1)) $t_plugins_1=Model::load(\'BlogPlugins\')->findByDeviceTypeAndCategory($this->getDeviceType(), Config::get(\'BLOG_PLUGIN.CATEGORY.FIRST\'), $blog_id); ?><?php if (!empty($t_plugins_1)) foreach($t_plugins_1 as $t_plugin) { ?>',
  'plugin_second'  => '<?php if(!isset($t_plugins_2)) $t_plugins_2=Model::load(\'BlogPlugins\')->findByDeviceTypeAndCategory($this->getDeviceType(), Config::get(\'BLOG_PLUGIN.CATEGORY.SECOND\'), $blog_id); ?><?php if (!empty($t_plugins_2)) foreach($t_plugins_2 as $t_plugin) { ?>',
  'plugin_third'   => '<?php if(!isset($t_plugins_3)) $t_plugins_3=Model::load(\'BlogPlugins\')->findByDeviceTypeAndCategory($this->getDeviceType(), Config::get(\'BLOG_PLUGIN.CATEGORY.THIRD\'), $blog_id); ?><?php if (!empty($t_plugins_3)) foreach($t_plugins_3 as $t_plugin) { ?>',
  'spplugin_first' => '<?php if(!isset($t_plugins_1)) $t_plugins_1=Model::load(\'BlogPlugins\')->findByDeviceTypeAndCategory($this->getDeviceType(), Config::get(\'BLOG_PLUGIN.CATEGORY.FIRST\'), $blog_id); ?><?php if (!empty($t_plugins_1)) foreach($t_plugins_1 as $t_plugin) { ?>',
);

// if文の置き換え用
$config['fc2_template_if'] = array(
  // 各エリア判定用
  'index_area'         => '<?php if(!empty($index_area)) { ?>',
  'not_index_area'     => '<?php if(empty($index_area)) { ?>',
  'titlelist_area'     => '<?php if(!empty($titlelist_area)) { ?>',
  'not_titlelist_area' => '<?php if(empty($titlelist_area)) { ?>',
  'date_area'          => '<?php if(!empty($date_area)) { ?>',
  'not_date_area'      => '<?php if(empty($date_area)) { ?>',
  'category_area'      => '<?php if(!empty($category_area)) { ?>',
  'not_category_area'  => '<?php if(empty($category_area)) { ?>',
  'tag_area'           => '<?php if(!empty($tag_area)) { ?>',
  'not_tag_area'       => '<?php if(empty($tag_area)) { ?>',
  'search_area'        => '<?php if(!empty($search_area)) { ?>',
  'not_search_area'    => '<?php if(empty($search_area)) { ?>',
  'comment_area'       => '<?php if(!empty($comment_area) && isset($entry[\'comment_accepted\']) && $entry[\'comment_accepted\']==Config::get(\'ENTRY.COMMENT_ACCEPTED.ACCEPTED\')) { ?><?php if (!empty($comment_error)) echo $comment_error; ?>',
  'not_comment_area'   => '<?php if(empty($comment_area)) { ?>',
  'form_area'          => '<?php if(!empty($form_area) && isset($entry[\'comment_accepted\']) && $entry[\'comment_accepted\']==Config::get(\'ENTRY.COMMENT_ACCEPTED.ACCEPTED\')) { ?><?php if (!empty($comment_error)) echo $comment_error; ?>',
  'not_form_area'      => '<?php if(empty($form_area)) { ?>',
  'edit_area'          => '<?php if(!empty($edit_area)) { ?><?php if (!empty($comment_error)) echo $comment_error; ?>',
  'not_edit_area'      => '<?php if(empty($edit_area)) { ?>',
  'comment_edit'       => '<?php if(!empty($comment[\'password\'])) { ?>',
  'trackback_area'     => '<?php if(false) { ?>',
  'not_trackback_area' => '<?php if(true) { ?>',
  'permanent_area'     => '<?php if(!empty($permanent_area)) { ?>',
  'not_permanent_area' => '<?php if(empty($permanent_area)) { ?>',
  'spplugin_area'      => '<?php if(!empty($spplugin_area)) { ?><?php if(!empty($s_plugin)) $t_plugin=$s_plugin; ?>',
  'not_spplugin_area'  => '<?php if(empty($spplugin_area)) { ?>',
  // 関連する記事
  'relate_list_area'     => '<?php if(false) { ?>',
  'not_relate_list_area' => '<?php if(true) { ?>',
  // 続きの表示
  'more_link'          => '<?php if(empty($comment_area) && !empty($entry[\'extend\'])) { ?>',
  'more'               => '<?php if(!empty($comment_area) && !empty($entry[\'extend\'])) { ?>',
  // コメントの受付可否
  'allow_comment'      => '<?php if(isset($entry[\'comment_accepted\']) && $entry[\'comment_accepted\']==Config::get(\'ENTRY.COMMENT_ACCEPTED.ACCEPTED\')) { ?>',
  'deny_comment'       => '<?php if(isset($entry[\'comment_accepted\']) && $entry[\'comment_accepted\']==Config::get(\'ENTRY.COMMENT_ACCEPTED.REJECT\')) { ?>',
  // 記事のスレッドテーマも無し判定
  'community'          => '<?php if(false) { ?>',
  // トラックバックは無し判定
  'allow_tb'           => '<?php if(false) { ?>',
  'deny_tb'            => '<?php if(true) { ?>',
  // コメントの返信の有無判定
  'comment_reply'      => '<?php if(!empty($comment[\'reply_body\'])) { ?>',
  // タグが存在するかどうか
  'topentry_tag'       => '<?php if(!empty($entry[\'tags\'])) { ?>',
  'not_topentry_tag'   => '<?php if(empty($entry[\'tags\'])) { ?>',
  // 本文に画像が存在するかどうか
  'body_img'           => '<?php if(!empty($entry[\'first_image\'])) { ?>',
  'body_img_none'      => '<?php if(empty($entry[\'first_image\'])) { ?>',
  // カテゴリーの各条件
  'category_parent'      => '<?php if(!empty($t_category) && $t_category[\'is_parent\']) { ?>',
  'category_nosub'       => '<?php if(!empty($t_category) && $t_category[\'is_nosub\']) { ?>',
  'category_sub_begin'   => '<?php if(!empty($t_category) && $t_category[\'is_sub_begin\']) { ?>',
  'category_sub_hasnext' => '<?php if(!empty($t_category) && $t_category[\'is_sub_hasnext\']) { ?>',
  'category_sub_end'     => '<?php if(!empty($t_category) && $t_category[\'is_sub_end\']) { ?>',
  // プラグイン系
  'plugin'             => '<?php if(true) { ?>',
  'spplugin'           => '<?php if(true) { ?>',
  // ページング系
  'page_area'          => '<?php if(!empty($paging)) { ?>',
  'nextpage'           => '<?php if(!empty($paging) && $paging[\'is_next\']) { ?>',
  'prevpage'           => '<?php if(!empty($paging) && $paging[\'is_prev\']) { ?>',
  'nextentry'          => '<?php if(!empty($next_entry)) { ?>',
  'preventry'          => '<?php if(!empty($prev_entry)) { ?>',
  'firstpage_disp'     => '<?php if(!empty($paging) && $paging[\'is_prev\']) { ?>',
  'lastpage_disp'      => '<?php if(!empty($paging) && $paging[\'is_next\']) { ?>',
  'res_nextpage_area'  => '<?php if(!empty($paging) && $paging[\'is_next\']) { ?>',
  'res_prevpage_area'  => '<?php if(!empty($paging) && $paging[\'is_prev\']) { ?>',
  // デバイスタイプ
  'ios'     => '<?php if(App::isIOS()) { ?>',
  'android' => '<?php if(App::isAndroid()) { ?>',
);

$template_vars = array(
  // タイトルリスト一覧
  '<%titlelist_eno>'          => '<?php if(isset($entry[\'id\'])) echo $entry[\'id\']; ?>',
  '<%titlelist_title>'        => '<?php if(isset($entry[\'title\'])) echo $entry[\'title\']; ?>',
  '<%titlelist_url>'          => '<?php if(isset($entry[\'link\'])) echo $entry[\'link\']; ?>',
  '<%titlelist_body>'         => '<?php if(isset($entry[\'body\'])) echo th($entry[\'body\'], 20); ?>',
  '<%titlelist_year>'         => '<?php if(isset($entry[\'year\'])) echo $entry[\'year\']; ?>',
  '<%titlelist_month>'        => '<?php if(isset($entry[\'month\'])) echo $entry[\'month\']; ?>',
  '<%titlelist_day>'          => '<?php if(isset($entry[\'day\'])) echo $entry[\'day\']; ?>',
  '<%titlelist_hour>'         => '<?php if(isset($entry[\'hour\'])) echo $entry[\'hour\']; ?>',
  '<%titlelist_minute>'       => '<?php if(isset($entry[\'minute\'])) echo $entry[\'minute\']; ?>',
  '<%titlelist_second>'       => '<?php if(isset($entry[\'second\'])) echo $entry[\'second\']; ?>',
  '<%titlelist_youbi>'        => '<?php if(isset($entry[\'youbi\'])) echo $entry[\'youbi\']; ?>',
  '<%titlelist_wayoubi>'      => '<?php if(isset($entry[\'wayoubi\'])) echo $entry[\'wayoubi\']; ?>',
  '<%titlelist_comment_num>'  => '<?php if(isset($entry[\'comment_count\'])) echo $entry[\'comment_count\']; ?>',
  '<%titlelist_tb_num>'       => '',
  // タイトルリストのカテゴリー系
  '<%titlelist_category_no>'  => '<?php if(!isset($entry[\'categories\'][0][\'id\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
                                .' echo $category[\'id\'];}else{echo $entry[\'categories\'][0][\'id\'];} ?>',
  '<%titlelist_category_url>' => '<?php if(!isset($entry[\'categories\'][0][\'id\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
                                .' echo Html::url(array(\'action\'=>\'category\', \'blog_id\'=>$entry[\'blog_id\'], \'cat\'=>$category[\'id\']));}else{'
                                .' echo Html::url(array(\'action\'=>\'category\', \'blog_id\'=>$entry[\'blog_id\'], \'cat\'=>$entry[\'categories\'][0][\'id\']));} ?>',
  '<%titlelist_category>'     => '<?php if(!isset($entry[\'categories\'][0][\'name\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
                                .' echo h($category[\'name\']);}else{echo h($entry[\'categories\'][0][\'name\']);} ?>',
  // 記事一覧
  '<%topentry_no>'                 => '<?php if(isset($entry[\'id\'])) echo $entry[\'id\']; ?>',
  '<%topentry_title>'              => '<?php if(isset($entry[\'title\'])) echo $entry[\'title\']; ?>',
  '<%topentry_title_w_img>'        => '<?php if(isset($entry[\'title_w_img\'])) echo $entry[\'title_w_img\']; ?>',
  '<%topentry_enc_title>'          => '<?php if(isset($entry[\'enc_title\'])) echo $entry[\'enc_title\']; ?>',
  '<%topentry_enc_utftitle>'       => '<?php if(isset($entry[\'enc_utftitle\'])) echo $entry[\'enc_utftitle\']; ?>',
  '<%topentry_body>'               => <<<PHP
<?php
  if (isset(\$entry['body'])) {
    if (!\$self_blog && \$entry['open_status']==Config::get('ENTRY.OPEN_STATUS.PASSWORD') && !Session::get('entry_password.' . \$entry['blog_id'] . '.' . \$entry['id'])) {
      echo <<<HTML
<form method="POST">
  <input type="hidden" name="mode" value="Entries" />
  <input type="hidden" name="process" value="password" />
  <input type="hidden" name="id" value="{\$entry['id']}" />
  <p>
    このコンテンツはパスワードで保護されています。<br />
    閲覧するには以下にパスワードを入力してください。
  </p>
  <p>パスワード <input type="password" name="password" /><input type="submit" value="送信" /></p>
</form>
HTML;
    } else {
      echo \$entry['body'];
    }
  }
?>
PHP
,
  '<%topentry_discription>'        => getTopentryDiscription(),
  '<%topentry_description>'        => getTopentryDiscription(),
  '<%topentry_desc>'               => '',
  '<%topentry_link>'               => '<?php if(isset($entry[\'link\'])) echo $entry[\'link\']; ?>',
  '<%topentry_enc_link>'           => '<?php if(isset($entry[\'enc_link\'])) echo $entry[\'enc_link\']; ?>',
  '<%topentry_more>'               => '<?php if(!empty($entry[\'extend\']) && ($entry[\'open_status\']!=Config::get(\'ENTRY.OPEN_STATUS.PASSWORD\') || Session::get(\'entry_password.\' . $entry[\'blog_id\'] . \'.\' . $entry[\'id\']))) echo $entry[\'extend\']; ?>',
  '<%topentry_year>'               => '<?php if(isset($entry[\'year\'])) echo $entry[\'year\']; ?>',
  '<%topentry_month>'              => '<?php if(isset($entry[\'month\'])) echo $entry[\'month\']; ?>',
  '<%topentry_month:short>'        => '<?php if(isset($entry[\'month_short\'])) echo $entry[\'month_short\']; ?>',
  '<%topentry_day>'                => '<?php if(isset($entry[\'day\'])) echo $entry[\'day\']; ?>',
  '<%topentry_hour>'               => '<?php if(isset($entry[\'hour\'])) echo $entry[\'hour\']; ?>',
  '<%topentry_minute>'             => '<?php if(isset($entry[\'minute\'])) echo $entry[\'minute\']; ?>',
  '<%topentry_second>'             => '<?php if(isset($entry[\'second\'])) echo $entry[\'second\']; ?>',
  '<%topentry_youbi>'              => '<?php if(isset($entry[\'youbi\'])) echo $entry[\'youbi\']; ?>',
  '<%topentry_wayoubi>'            => '<?php if(isset($entry[\'wayoubi\'])) echo $entry[\'wayoubi\']; ?>',
  '<%topentry_tb_num>'             => '',
  '<%topentry_tb_no>'              => '',
  '<%topentry_jointtag>'           => '<?php if(!empty($entry[\'tags\'])) foreach($entry[\'tags\'] as $tag) echo \'<a href="\' . $url . \'?tag=\' . ue($tag[\'name\']) . \'">\' . h($tag[\'name\']) . \'</a>\'; ?>',
  '<%topentry_image>'              => '<?php if(!empty($entry[\'first_image\'])) echo \'<img src="\' . $entry[\'first_image\'] . \'" />\'; ?>',
  '<%topentry_image_72>'           => '<?php if(!empty($entry[\'first_image\'])) echo \'<img src="\' . App::getThumbnailPath($entry[\'first_image\'], 72) . \'" />\'; ?>',
  '<%topentry_image_w300>'         => '<?php if(!empty($entry[\'first_image\'])) echo \'<img src="\' . App::getThumbnailPath($entry[\'first_image\'], 300, \'w\') . \'" />\'; ?>',
  '<%topentry_comment_num>'        => '<?php if(isset($entry[\'comment_count\'])) echo $entry[\'comment_count\']; ?>',
  // 記事のカテゴリー系
  '<%topentry_category_no>'        => '<?php if(!isset($entry[\'categories\'][0][\'id\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
                                     .' echo $category[\'id\'];}else{echo $entry[\'categories\'][0][\'id\'];} ?>',
  '<%topentry_category_link>'      => '<?php if(!isset($entry[\'categories\'][0][\'id\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
                                     .' echo Html::url(array(\'action\'=>\'category\', \'blog_id\'=>$entry[\'blog_id\'], \'cat\'=>$category[\'id\']));}else{'
                                     .' echo Html::url(array(\'action\'=>\'category\', \'blog_id\'=>$entry[\'blog_id\'], \'cat\'=>$entry[\'categories\'][0][\'id\']));} ?>',
  '<%topentry_category>'           => '<?php if(!isset($entry[\'categories\'][0][\'name\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
                                     .' echo h($category[\'name\']);}else{echo h($entry[\'categories\'][0][\'name\']);} ?>',
  // 記事のタグ系
  '<%topentry_tag_list_name>'      => '<?php if(isset($tag[\'name\'])) echo h($tag[\'name\']); ?>',
  '<%topentry_tag_list_parsename>' => '<?php if(isset($tag[\'name\'])) echo ue($tag[\'name\']); ?>',
  // 記事一覧のコメント表示
  '<%topentry_comment_list_name>'   => '<?php if(isset($comment[\'name\'])) echo h($comment[\'name\']); ?>',
  '<%topentry_comment_list_title>'  => '<?php if(isset($comment[\'title\'])) echo h($comment[\'title\']); ?>',
  '<%topentry_comment_list_body>'   => '<?php if(isset($comment[\'body\'])) echo h($comment[\'body\']); ?>',
  '<%topentry_comment_list_brbody>' => '<?php if(isset($comment[\'body\'])) echo nl2br(h($comment[\'body\'])); ?>',
  '<%topentry_comment_list_date>'   => '<?php if(isset($comment[\'created_at\'])) echo $comment[\'created_at\']; ?>',
  // コメント一覧
  '<%comment_no>'        => '<?php if(isset($comment[\'id\'])) echo $comment[\'id\']; ?>',
  '<%comment_title>'     => '<?php if(isset($comment[\'title\'])) echo h($comment[\'title\']); ?>',
  '<%comment_body>'      => '<?php if(isset($comment[\'body\'])) echo nl2br(h($comment[\'body\'])); ?>',
  '<%comment_year>'      => '<?php if(isset($comment[\'year\'])) echo $comment[\'year\']; ?>',
  '<%comment_month>'     => '<?php if(isset($comment[\'month\'])) echo $comment[\'month\']; ?>',
  '<%comment_day>'       => '<?php if(isset($comment[\'day\'])) echo $comment[\'day\']; ?>',
  '<%comment_hour>'      => '<?php if(isset($comment[\'hour\'])) echo $comment[\'hour\']; ?>',
  '<%comment_minute>'    => '<?php if(isset($comment[\'minute\'])) echo $comment[\'minute\']; ?>',
  '<%comment_second>'    => '<?php if(isset($comment[\'second\'])) echo $comment[\'second\']; ?>',
  '<%comment_youbi>'     => '<?php if(isset($comment[\'youbi\'])) echo $comment[\'youbi\']; ?>',
  '<%comment_wayoubi>'   => '<?php if(isset($comment[\'wayoubi\'])) echo $comment[\'wayoubi\']; ?>',
  '<%comment_edit_link>' => '<?php if(isset($comment[\'edit_link\'])) echo $comment[\'edit_link\']; ?>',
  '<%comment_name>'      => '<?php if(isset($comment[\'name\'])) echo h($comment[\'name\']); ?>',
  '<%comment_mail>'      => '<?php if(isset($comment[\'mail\'])) echo $comment[\'mail\']; ?>',
  '<%comment_url>'       => '<?php if(isset($comment[\'url\'])) echo $comment[\'url\']; ?>',
  '<%comment_url+str>'   => '<?php if(isset($comment[\'url\'])) echo \'<a href="\' . $comment[\'url\'] . \'">\' . $comment[\'url\'] . \'</a>\'; ?>',
  '<%comment_mail+name>' => '<?php if(!isset($comment[\'name\'])){}else if(!empty($comment[\'mail\'])){ echo \'<a href="mailto:\' . $comment[\'mail\'] . \'">\' . h($comment[\'name\']) . \'</a>\'; }else{ echo h($comment[\'name\']); } ?>',
  '<%comment_url+name>'  => '<?php if(!isset($comment[\'name\'])){}else if(!empty($comment[\'url\'])){ echo \'<a href="\' . $comment[\'url\'] . \'">\' . h($comment[\'name\']) . \'</a>\'; }else{ echo h($comment[\'name\']); } ?>',
  '<%comment_trip>'      => '<?php if(isset($comment[\'trip\'])) echo $comment[\'trip\']; ?>',
  // コメント一覧の返信分
  '<%comment_reply_body>'    => '<?php if(isset($comment[\'reply_body\'])) echo $comment[\'reply_body\']; ?>',
  '<%comment_reply_year>'    => '<?php if(isset($comment[\'reply_year\'])) echo $comment[\'reply_year\']; ?>',
  '<%comment_reply_month>'   => '<?php if(isset($comment[\'reply_month\'])) echo $comment[\'reply_month\']; ?>',
  '<%comment_reply_day>'     => '<?php if(isset($comment[\'reply_day\'])) echo $comment[\'reply_day\']; ?>',
  '<%comment_reply_hour>'    => '<?php if(isset($comment[\'reply_hour\'])) echo $comment[\'reply_hour\']; ?>',
  '<%comment_reply_minute>'  => '<?php if(isset($comment[\'reply_minute\'])) echo $comment[\'reply_minute\']; ?>',
  '<%comment_reply_second>'  => '<?php if(isset($comment[\'reply_second\'])) echo $comment[\'reply_second\']; ?>',
  '<%comment_reply_youbi>'   => '<?php if(isset($comment[\'reply_youbi\'])) echo $comment[\'reply_youbi\']; ?>',
  '<%comment_reply_wayoubi>' => '<?php if(isset($comment[\'reply_wayoubi\'])) echo $comment[\'reply_wayoubi\']; ?>',
  // コメントのクッキー情報
  '<%cookie_name>'       => '<?php if(empty($comment_error) && Cookie::get(\'comment_name\')) echo Cookie::get(\'comment_name\'); ?>',
  '<%cookie_mail>'       => '<?php if(empty($comment_error) && Cookie::get(\'comment_mail\')) echo Cookie::get(\'comment_mail\'); ?>',
  '<%cookie_url>'        => '<?php if(empty($comment_error) && Cookie::get(\'comment_url\')) echo Cookie::get(\'comment_url\'); ?>',
  // コメント編集
  '<%eno>'              => '<?php if(isset($edit_comment[\'id\'])) echo $edit_comment[\'id\']; ?>',
  '<%edit_name>'        => '<?php if(isset($edit_comment[\'name\'])) echo $edit_comment[\'name\']; ?>',
  '<%edit_title>'       => '<?php if(isset($edit_comment[\'title\'])) echo $edit_comment[\'title\']; ?>',
  '<%edit_mail>'        => '<?php if(isset($edit_comment[\'mail\'])) echo $edit_comment[\'mail\']; ?>',
  '<%edit_url>'         => '<?php if(isset($edit_comment[\'url\'])) echo $edit_comment[\'url\']; ?>',
  '<%edit_body>'        => '<?php if(isset($edit_comment[\'body\'])) echo $edit_comment[\'body\']; ?>',
  '<%edit_message>'     => '<?php if(isset($edit_comment[\'message\'])) echo $edit_comment[\'message\']; ?>',
  '<%edit_entry_no>'    => '<?php if(isset($edit_entry[\'id\'])) echo $edit_entry[\'id\']; ?>',
  '<%edit_entry_title>' => '<?php if(isset($edit_entry[\'title\'])) echo $edit_entry[\'title\']; ?>',
  // プラグイン
  '<%plugin_first_title>'         => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
  '<%plugin_second_title>'        => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
  '<%plugin_third_title>'         => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
  '<%plugin_first_content>'       => '<?php if(isset($t_plugin[\'id\'])) include(App::getPluginFilePath($blog_id, $t_plugin[\'id\'])); ?>',
  '<%plugin_second_content>'      => '<?php if(isset($t_plugin[\'id\'])) include(App::getPluginFilePath($blog_id, $t_plugin[\'id\'])); ?>',
  '<%plugin_third_content>'       => '<?php if(isset($t_plugin[\'id\'])) include(App::getPluginFilePath($blog_id, $t_plugin[\'id\'])); ?>',
  '<%plugin_first_description>'   => '',
  '<%plugin_first_description2>'  => '',
  '<%plugin_second_description>'  => '',
  '<%plugin_second_description2>' => '',
  '<%plugin_third_description>'   => '',
  '<%plugin_third_description2>'  => '',
  '<%plugin_first_talign>'        => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
  '<%plugin_second_talign>'       => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
  '<%plugin_third_talign>'        => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
  '<%plugin_first_tcolor>'        => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
  '<%plugin_second_tcolor>'       => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
  '<%plugin_third_tcolor>'        => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
  '<%plugin_first_align>'         => '<?php if(isset($t_plugin[\'contents_align\'])) echo $t_plugin[\'contents_align\']; ?>',
  '<%plugin_second_align>'        => '<?php if(isset($t_plugin[\'contents_align\'])) echo $t_plugin[\'contents_align\']; ?>',
  '<%plugin_third_align>'         => '<?php if(isset($t_plugin[\'contents_align\'])) echo $t_plugin[\'contents_align\']; ?>',
  '<%plugin_first_color>'         => '<?php if(isset($t_plugin[\'contents_color\'])) echo $t_plugin[\'contents_color\']; ?>',
  '<%plugin_second_color>'        => '<?php if(isset($t_plugin[\'contents_color\'])) echo $t_plugin[\'contents_color\']; ?>',
  '<%plugin_third_color>'         => '<?php if(isset($t_plugin[\'contents_color\'])) echo $t_plugin[\'contents_color\']; ?>',
  // プラグイン(スマフォ用)
  '<%spplugin_first_no>'     => '<?php if(isset($t_plugin[\'id\'])) echo $t_plugin[\'id\']; ?>',
  '<%spplugin_first_title>'  => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
  '<%spplugin_title>'        => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
  '<%spplugin_content>'      => '<?php if(isset($t_plugin[\'id\'])) include(App::getPluginFilePath($blog_id, $t_plugin[\'id\'])); ?>',
  '<%spplugin_talign>'       => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
  '<%spplugin_tcolor>'       => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
  '<%spplugin_align>'        => '<?php if(isset($t_plugin[\'contents_align\'])) echo $t_plugin[\'contents_align\']; ?>',
  '<%spplugin_color>'        => '<?php if(isset($t_plugin[\'contents_color\'])) echo $t_plugin[\'contents_color\']; ?>',
  // 最新の記事一覧(プラグイン系)
  '<%recent_no>'         => '<?php if(isset($t_recent[\'id\'])) echo $t_recent[\'id\']; ?>',
  '<%recent_title>'      => '<?php if(isset($t_recent[\'title\'])) echo $t_recent[\'title\']; ?>',
  '<%recent_link>'       => '<?php if(isset($t_recent[\'link\'])) echo $t_recent[\'link\']; ?>',
  '<%recent_body>'       => '<?php if(isset($t_recent[\'body\'])) echo th($t_recent[\'body\'], 50); ?>',
  '<%recent_year>'       => '<?php if(isset($t_recent[\'year\'])) echo $t_recent[\'year\']; ?>',
  '<%recent_month>'      => '<?php if(isset($t_recent[\'month\'])) echo $t_recent[\'month\']; ?>',
  '<%recent_day>'        => '<?php if(isset($t_recent[\'day\'])) echo $t_recent[\'day\']; ?>',
  '<%recent_hour>'       => '<?php if(isset($t_recent[\'hour\'])) echo $t_recent[\'hour\']; ?>',
  '<%recent_minute>'     => '<?php if(isset($t_recent[\'minute\'])) echo $t_recent[\'minute\']; ?>',
  '<%recent_second>'     => '<?php if(isset($t_recent[\'second\'])) echo $t_recent[\'second\']; ?>',
  '<%recent_youbi>'      => '<?php if(isset($t_recent[\'youbi\'])) echo $t_recent[\'youbi\']; ?>',
  '<%recent_wayoubi>'    => '<?php if(isset($t_recent[\'wayoubi\'])) echo $t_recent[\'wayoubi\']; ?>',
  '<%recent_image_w300>' => '<?php if(!empty($t_recent[\'first_image\'])) echo \'<img src="\' . App::getThumbnailPath($t_recent[\'first_image\'], 300, \'w\') . \'" />\'; ?>',
  // カテゴリー一覧(プラグイン系)
  '<%category_no>'     => '<?php if(!empty($t_category)) echo $t_category[\'id\']; ?>',
  '<%category_number>' => '<?php if(!empty($t_category)) echo $t_category[\'id\']; ?>',
  '<%category_link>'   => '<?php if(!empty($t_category)) echo Html::url(array(\'action\'=>\'category\', \'blog_id\'=>$t_category[\'blog_id\'], \'cat\'=>$t_category[\'id\'])); ?>',
  '<%category_name>'   => '<?php if(!empty($t_category)) echo $t_category[\'name\']; ?>',
  '<%category_count>'  => '<?php if(!empty($t_category)) echo $t_category[\'count\']; ?>',
  // アーカイブ一覧(プラグイン系)
  '<%archive_link>'     => '<?php if(!empty($t_archive)) echo Html::url(array(\'blog_id\'=>$blog_id, \'action\'=>\'date\', \'date\'=>$t_archive[\'year\'] . $t_archive[\'month\'])); ?>',
  '<%archive_count>'    => '<?php if(!empty($t_archive)) echo $t_archive[\'count\']; ?>',
  '<%archive_year>'     => '<?php if(!empty($t_archive)) echo $t_archive[\'year\']; ?>',
  '<%archive_month>'    => '<?php if(!empty($t_archive)) echo $t_archive[\'month\']; ?>',
  // カレンダー一覧(プラグイン系)
  '<%calendar_sun>'     => '<?php if(isset($t_calendar[0])) echo $t_calendar[0]; ?>',
  '<%calendar_mon>'     => '<?php if(isset($t_calendar[1])) echo $t_calendar[1]; ?>',
  '<%calendar_tue>'     => '<?php if(isset($t_calendar[2])) echo $t_calendar[2]; ?>',
  '<%calendar_wed>'     => '<?php if(isset($t_calendar[3])) echo $t_calendar[3]; ?>',
  '<%calendar_thu>'     => '<?php if(isset($t_calendar[4])) echo $t_calendar[4]; ?>',
  '<%calendar_fri>'     => '<?php if(isset($t_calendar[5])) echo $t_calendar[5]; ?>',
  '<%calendar_sat>'     => '<?php if(isset($t_calendar[6])) echo $t_calendar[6]; ?>',
  '<%calender_sun>'     => '<?php if(isset($t_calendar[0])) echo $t_calendar[0]; ?>',
  '<%calender_mon>'     => '<?php if(isset($t_calendar[1])) echo $t_calendar[1]; ?>',
  '<%calender_tue>'     => '<?php if(isset($t_calendar[2])) echo $t_calendar[2]; ?>',
  '<%calender_wed>'     => '<?php if(isset($t_calendar[3])) echo $t_calendar[3]; ?>',
  '<%calender_thu>'     => '<?php if(isset($t_calendar[4])) echo $t_calendar[4]; ?>',
  '<%calender_fri>'     => '<?php if(isset($t_calendar[5])) echo $t_calendar[5]; ?>',
  '<%calender_sat>'     => '<?php if(isset($t_calendar[6])) echo $t_calendar[6]; ?>',
  // コメント一覧(テンプレート変数系)
  '<%rcomment_keyno>'     => '<?php if(isset($t_comment[\'entry_id\'])) echo $t_comment[\'entry_id\']; ?>',
  '<%rcomment_etitle>'    => '<?php if(isset($t_comment[\'entry_title\'])) echo $t_comment[\'entry_title\']; ?>',
  '<%rcomment_link>'      => '<?php if(isset($t_comment[\'link\'])) echo $t_comment[\'link\']; ?>',
  '<%rcomment_no>'        => '<?php if(isset($t_comment[\'id\'])) echo $t_comment[\'id\']; ?>',
  '<%rcomment_title>'     => '<?php if(isset($t_comment[\'title\'])) echo $t_comment[\'title\']; ?>',
  '<%rcomment_name>'      => '<?php if(isset($t_comment[\'name\'])) echo $t_comment[\'name\']; ?>',
  '<%rcomment_body>'      => '<?php if(isset($t_comment[\'body\'])) echo $t_comment[\'body\']; ?>',
  '<%rcomment_year>'      => '<?php if(isset($t_comment[\'year\'])) echo $t_comment[\'year\']; ?>',
  '<%rcomment_month>'     => '<?php if(isset($t_comment[\'month\'])) echo $t_comment[\'month\']; ?>',
  '<%rcomment_day>'       => '<?php if(isset($t_comment[\'day\'])) echo $t_comment[\'day\']; ?>',
  '<%rcomment_hour>'      => '<?php if(isset($t_comment[\'hour\'])) echo $t_comment[\'hour\']; ?>',
  '<%rcomment_minute>'    => '<?php if(isset($t_comment[\'minute\'])) echo $t_comment[\'minute\']; ?>',
  '<%rcomment_second>'    => '<?php if(isset($t_comment[\'second\'])) echo $t_comment[\'second\']; ?>',
  '<%rcomment_youbi>'     => '<?php if(isset($t_comment[\'youbi\'])) echo $t_comment[\'youbi\']; ?>',
  '<%rcomment_wayoubi>'   => '<?php if(isset($t_comment[\'wayoubi\'])) echo $t_comment[\'wayoubi\']; ?>',
  '<%rcomment_mail>'      => '<?php if(isset($t_comment[\'mail\'])) echo $t_comment[\'mail\']; ?>',
  '<%rcomment_url>'       => '<?php if(isset($t_comment[\'url\'])) echo $t_comment[\'url\']; ?>',
  '<%rcomment_url+str>'   => '<?php if(isset($t_comment[\'url\'])) echo \'<a href="\' . $t_comment[\'url\'] . \'">\' . $t_comment[\'url\'] . \'</a>\'; ?>',
  '<%rcomment_mail+name>' => '<?php if(!isset($t_comment[\'name\'])){}else if(!empty($t_comment[\'mail\'])){ echo \'<a href="mailto:\' . $t_comment[\'mail\'] . \'">\' . $t_comment[\'name\'] . \'</a>\'; }else{ echo $t_comment[\'name\']; } ?>',
  '<%rcomment_url+name>'  => '<?php if(!isset($t_comment[\'name\'])){}else if(!empty($t_comment[\'url\'])){ echo \'<a href="\' . $t_comment[\'url\'] . \'">\' . $t_comment[\'name\'] . \'</a>\'; }else{ echo $t_comment[\'name\']; } ?>',
  // ページング系
  '<%nextpage_url>'         => '<?php if(!empty($paging) && $paging[\'is_next\']) echo Html::url(array(\'page\'=>$paging[\'page\']+1, \'blog_id\'=>$blog_id), true); ?>',
  '<%prevpage_url>'         => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo Html::url(array(\'page\'=>$paging[\'page\']-1, \'blog_id\'=>$blog_id), true); ?>',
  '<%days>'                 => '<?php echo date(\'d\', strtotime($now_date)); ?>',
  '<%now_year>'             => '<?php echo date(\'Y\', strtotime($now_date)); ?>',
  '<%now_month>'            => '<?php echo date(\'m\', strtotime($now_date)); ?>',
  '<%prev_month>'           => '<?php echo date(\'m\', strtotime($prev_month_date)); ?>',
  '<%prev_year>'            => '<?php echo date(\'Y\', strtotime($prev_month_date)); ?>',
  '<%next_month>'           => '<?php echo date(\'m\', strtotime($next_month_date)); ?>',
  '<%next_year>'            => '<?php echo date(\'Y\', strtotime($next_month_date)); ?>',
  '<%prev_month_link>'      => '<?php echo Html::url(array(\'blog_id\'=>$blog_id, \'action\'=>\'date\', \'date\'=>date(\'Ym\', strtotime($prev_month_date)))); ?>',
  '<%next_month_link>'      => '<?php echo Html::url(array(\'blog_id\'=>$blog_id, \'action\'=>\'date\', \'date\'=>date(\'Ym\', strtotime($next_month_date)))); ?>',
  '<%nextentry_url>'        => '<?php if(!empty($next_entry)) echo App::userURL(array(\'id\'=>$next_entry[\'id\'], \'blog_id\'=>$blog_id)); ?>',
  '<%nextentry_title>'      => '<?php if(!empty($next_entry)) echo h($next_entry[\'title\']); ?>',
  '<%preventry_url>'        => '<?php if(!empty($prev_entry)) echo App::userURL(array(\'id\'=>$prev_entry[\'id\'], \'blog_id\'=>$blog_id)); ?>',
  '<%preventry_title>'      => '<?php if(!empty($prev_entry)) echo h($prev_entry[\'title\']); ?>',
  '<%firstpage_num>'        => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo 1; ?>',
  '<%lastpage_num>'         => '<?php if(!empty($paging) && $paging[\'is_next\']) echo $paging[\'max_page\']; ?>',
  '<%firstpage_url>'        => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo Html::url(array(\'page\'=>0, \'blog_id\'=>$blog_id), true); ?>',
  '<%lastpage_url>'         => '<?php if(!empty($paging) && $paging[\'is_next\']) echo Html::url(array(\'page\'=>$paging[\'max_page\']-1, \'blog_id\'=>$blog_id), true); ?>',
  '<%current_page_num>'     => '<?php if(!empty($paging)) echo $paging[\'page\']+1; ?>',
  '<%total_pages>'          => '<?php if(!empty($paging)) echo $paging[\'max_page\']; ?>',
  '<%tail_url>'             => '',
  '<%template_pager1>'      => getFc2PagingPHP(1),
  '<%template_pager2>'      => getFc2PagingPHP(2),
  '<%template_pager3>'      => getFc2PagingPHP(3),
  '<%template_pager4>'      => getFc2PagingPHP(4),
  '<%template_pager5>'      => getFc2PagingPHP(5),
  '<%res_nextpage_url>'     => '<?php if(!empty($paging) && $paging[\'is_next\']) echo Html::url(array(\'page\'=>$paging[\'page\']+1, \'blog_id\'=>$blog_id), true); ?>',
  '<%res_prevpage_url>'     => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo Html::url(array(\'page\'=>$paging[\'page\']-1, \'blog_id\'=>$blog_id), true); ?>',
  '<%res_firstpage_url>'    => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo Html::url(array(\'page\'=>0, \'blog_id\'=>$blog_id), true); ?>',
  '<%res_lastpage_url>'     => '<?php if(!empty($paging) && $paging[\'is_next\']) echo Html::url(array(\'page\'=>$paging[\'max_page\']-1, \'blog_id\'=>$blog_id), true); ?>',
  '<%res_template_pager1>'  => getFc2PagingPHP(1),
  '<%res_template_pager2>'  => getFc2PagingPHP(2),
  '<%res_template_pager3>'  => getFc2PagingPHP(3),
  '<%res_template_pager4>'  => getFc2PagingPHP(4),
  '<%res_template_pager5>'  => getFc2PagingPHP(5),
  // 全体変数
  '<%css_link>'                => '<?php if(isset($css_link)) echo $css_link; ?>',
  '<%url>'                     => '<?php if(isset($url)) echo $url; ?>',
  '<%blog_name>'               => '<?php if(isset($blog[\'name\'])) echo h($blog[\'name\']); ?>',
  '<%author_name>'             => '<?php if(isset($blog[\'nickname\'])) echo h($blog[\'nickname\']); ?>',
  '<%introduction>'            => '<?php if(isset($blog[\'introduction\'])) echo h($blog[\'introduction\']); ?>',
  '<%pno>'                     => '<?php if(isset($entry[\'id\'])) echo $entry[\'id\']; ?>',
  '<%sub_title>'               => '<?php if(isset($sub_title)) echo h($sub_title); ?>',
  '<%template_comment_js>'     => '<?php echo \'/js/template_comment.js\' ?>',
  '<%template_copyright_date>' => date('Y', $_SERVER['REQUEST_TIME']),
  '<%ad>'                      => '',
  '<%ad2>'                     => '',
  '<%ad_overlay>'              => '',
  // i18n系
  '<%template_fc2blog>'        => '<?php echo __(\'FC2 BLOG\'); ?>',
  '<%template_extend>'         => '<?php echo __(\'Read more\'); ?>',
  '<%template_theme>'          => '<?php echo __(\'Theme\'); ?>',
  '<%template_genre>'          => '<?php echo __(\'Genre\'); ?>',
  '<%template_trackback>'      => '<?php echo __(\'Trackbacks\'); ?>',
  '<%template_comment>'        => '<?php echo __(\'Comments\'); ?>',
  '<%template_abs_link>'       => '<?php echo __(\'Entry Absolute link\'); ?>',
  '<%template_category>'       => '<?php echo __(\'Entry category\'); ?>',
  '<%template_view_category>'  => '<?php echo __(\'View category list\'); ?>',
  '<%template_edit>'           => '<?php echo __(\'Edit\'); ?>',
  '<%template_title>'          => '<?php echo __(\'Title\'); ?>',
  '<%template_name>'           => '<?php echo __(\'Name\'); ?>',
  '<%template_address>'        => '<?php echo __(\'E-mail address\'); ?>',
  '<%template_body>'           => '<?php echo __(\'Body\'); ?>',
  '<%template_post_comment>'   => '<?php echo __(\'Post a comment\'); ?>',
  '<%template_private>'        => '<?php echo __(\'Private message\'); ?>',
  '<%template_private_check>'  => '<?php echo __(\'Only the blog author may view the message.\'); ?>',
  '<%template_password>'       => '<?php echo __(\'Edit password\'); ?>',
  '<%template_send>'           => '<?php echo __(\'Send\'); ?>',
  '<%template_delete>'         => '<?php echo __(\'Delete\'); ?>',
  '<%template_edit_comment>'   => '<?php echo __(\'Edit a comment\'); ?>',
  '<%template_trackback_this>' => '<?php echo __(\'Use trackback for this entry.\'); ?>',
  '<%template_home>'           => '<?php echo __(\'Home\'); ?>',
  '<%template_index>'          => '<?php echo __(\'Index\'); ?>',
  '<%template_firstentry>'     => '<?php echo __(\'First entry\'); ?>',
  '<%template_search_entry>'   => '<?php echo __(\'Search entries\'); ?>',
  '<%template_prevpage>'       => '<?php echo __(\'Previous page\'); ?>',
  '<%template_nextpage>'       => '<?php echo __(\'Next page\'); ?>',
  '<%template_preventry>'      => '<?php echo __(\'Previous entry\'); ?>',
  '<%template_nextentry>'      => '<?php echo __(\'Next entry\'); ?>',
  '<%template_go_top>'         => '<?php echo __(\'Top of page\'); ?>',
  // HTML変換
  'name="mode" value="regist"' => 'name="process" value="comment_regist"',
  'name="mode" value="edit"'   => 'name="process" value="comment_edit"',
);

$config['fc2_template_var_search'] = $config['fc2_template_var_replace'] = array();
foreach ($template_vars as $key => $value) {
  $config['fc2_template_var_search'][] = $key;
  $config['fc2_template_var_replace'][] = $value;;
}

/**
* ページング用のPHPコードを取得する
*/
function getFc2PagingPHP($page_num){
  global $blog_id;
  $html  = '<?php if(!empty($paging)): ?>';
  $html .= '<?php for ($i=max(0, $paging[\'page\']-' . $page_num . ');$i<$paging[\'page\'];$i++) ';
  $html .= 'echo \'<a href="\' . Html::url(array(\'page\'=>$i, \'blog_id\'=>$blog_id), true) . \'">\' . ($i+1) . \'</a>\'; ?>';
  $html .= '<strong><?php echo $paging[\'page\']+1; ?></strong>';
  $html .= '<?php for ($i=$paging[\'page\']+1;$i<$paging[\'max_page\'] && $i<$paging[\'page\']+1+' . $page_num . ';$i++) ';
  $html .= 'echo \'<a href="\' . Html::url(array(\'page\'=>$i, \'blog_id\'=>$blog_id), true) . \'">\' . ($i+1) . \'</a>\'; ?>';
  $html .= '<?php endif; ?>';
  return $html;
}

function getTopentryDiscription(): string
{
  return <<<PHP
<?php
  if (isset(\$entry['body'])) {
    if (!\$self_blog && \$entry['open_status']==Config::get('ENTRY.OPEN_STATUS.PASSWORD') && !Session::get('entry_password.' . \$entry['blog_id'] . '.' . \$entry['id'])) {
      echo <<<HTML
<form method="POST">
  <input type="hidden" name="mode" value="Entries" />
  <input type="hidden" name="process" value="password" />
  <input type="hidden" name="id" value="{\$entry['id']}" />
  <p>
    このコンテンツはパスワードで保護されています。<br />
    閲覧するには以下にパスワードを入力してください。
  </p>
  <p>パスワード <input type="password" name="password" /><input type="submit" value="送信" /></p>
</form>
HTML;
    } else {
      echo th(strip_tags(\$entry['body']), 200);
    }
  }
?>
PHP;
}

return $config;
