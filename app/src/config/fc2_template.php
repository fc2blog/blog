<?php /** @noinspection ALL */

$config = [];

// ループ文の置き換え用
$config['fc2_template_foreach'] = [
    'topentry' => '<?php if(!empty($entries) && empty($titlelist_area)) foreach($entries as $entry) { ?>',
    'titlelist' => '<?php if(!empty($entries) && !empty($titlelist_area)) foreach($entries as $entry) { ?>',
    'comment' => '<?php if(!empty($comments)) foreach($comments as $comment) { ?>',
    'comment_list' => '<?php if(!empty($comments)) foreach($comments as $comment) { ?>', // comment のalias
    'category_list' => '<?php if(!empty($entry[\'categories\'])) foreach($entry[\'categories\'] as $category) { ?>',
    'tag_list' => '<?php if(!empty($entry[\'tags\'])) foreach($entry[\'tags\'] as $tag) { ?>',
    // 最新記事一覧(プラグイン表示用)
    'recent' => '<?php if(!isset($t_recents)) $t_recents = \Fc2blog\Model\Model::load(\'Entries\')->getTemplateRecents($request, $blog_id); ?><?php if (!empty($t_recents)) foreach($t_recents as $t_recent) { ?>',
    // カテゴリー(プラグイン表示用)
    'category' => '<?php if(!isset($t_categories)) $t_categories = \Fc2blog\Model\Model::load(\'Categories\')->getTemplateCategories($blog_id); ?><?php if (!empty($t_categories)) foreach($t_categories as $t_category) { ?>',
    // アーカイブ(プラグイン表示用)
    'archive' => '<?php if(!isset($t_archives)) $t_archives = \Fc2blog\Model\Model::load(\'Entries\')->getArchives($blog_id); ?><?php if (!empty($t_archives)) foreach($t_archives as $t_archive) { ?>',
    // 新着順のコメント
    'rcomment' => '<?php if(!isset($t_comments)) $t_comments = \Fc2blog\Model\Model::load(\'Comments\')->getTemplateRecentCommentList($request, $blog_id); ?><?php if (!empty($t_comments)) foreach($t_comments as $t_comment) { ?>',
    // カテゴリー
    'category_multi_sub_end' => '<?php if(!empty($t_category) && isset($t_category[\'climb_hierarchy\'])) for($category_index=0;$category_index<$t_category[\'climb_hierarchy\'];$category_index++) { ?>',
    // カレンダー
    'calendar' => '<?php if(!isset($t_calendars)) $t_calendars = \Fc2blog\Model\Model::load(\'Entries\')->getTemplateCalendar($request, $blog_id, date(\'Y\', strtotime($now_date)), date(\'m\', strtotime($now_date))); ?><?php if (!empty($t_calendars)) foreach($t_calendars as $t_calendar) { ?>',
    // calend"e"r, calendar のエイリアス
    'calender' => '<?php if(!isset($t_calendars)) $t_calendars = \Fc2blog\Model\Model::load(\'Entries\')->getTemplateCalendar($request, $blog_id, date(\'Y\', strtotime($now_date)), date(\'m\', strtotime($now_date))); ?><?php if (!empty($t_calendars)) foreach($t_calendars as $t_calendar) { ?>',
    // タグループ(ctag_existsと組み合わせると指定ブログの全タグ)
    'ctag' => '<?php if (!empty($t_tags)) foreach($t_tags as $t_tag) { ?>',
    // プラグイン系
    'plugin_first' => '<?php if(!isset($t_plugins_1)) $t_plugins_1=\Fc2blog\Model\Model::load(\'BlogPlugins\')->findByDeviceTypeAndCategory(\Fc2blog\App::getDeviceType($request), \Fc2blog\Config::get(\'BLOG_PLUGIN.CATEGORY.FIRST\'), $blog_id); ?><?php if (!empty($t_plugins_1)) foreach($t_plugins_1 as $t_plugin) { ?>',
    'plugin_second' => '<?php if(!isset($t_plugins_2)) $t_plugins_2=\Fc2blog\Model\Model::load(\'BlogPlugins\')->findByDeviceTypeAndCategory(\Fc2blog\App::getDeviceType($request), \Fc2blog\Config::get(\'BLOG_PLUGIN.CATEGORY.SECOND\'), $blog_id); ?><?php if (!empty($t_plugins_2)) foreach($t_plugins_2 as $t_plugin) { ?>',
    'plugin_third' => '<?php if(!isset($t_plugins_3)) $t_plugins_3=\Fc2blog\Model\Model::load(\'BlogPlugins\')->findByDeviceTypeAndCategory(\Fc2blog\App::getDeviceType($request), \Fc2blog\Config::get(\'BLOG_PLUGIN.CATEGORY.THIRD\'), $blog_id); ?><?php if (!empty($t_plugins_3)) foreach($t_plugins_3 as $t_plugin) { ?>',
    'spplugin_first' => '<?php if(!isset($t_plugins_1)) $t_plugins_1=\Fc2blog\Model\Model::load(\'BlogPlugins\')->findByDeviceTypeAndCategory(\Fc2blog\App::getDeviceType($request), \Fc2blog\Config::get(\'BLOG_PLUGIN.CATEGORY.FIRST\'), $blog_id); ?><?php if (!empty($t_plugins_1)) foreach($t_plugins_1 as $t_plugin) { ?>',
];

// if文の置き換え用
$config['fc2_template_if'] = [
    // 各エリア判定用
    'index_area' => '<?php if(!empty($index_area)) { ?>', // index アクション
    'not_index_area' => '<?php if(empty($index_area)) { ?>',
    'titlelist_area' => '<?php if(!empty($titlelist_area)) { ?>', // archive アクション
    'not_titlelist_area' => '<?php if(empty($titlelist_area)) { ?>',
    'date_area' => '<?php if(!empty($date_area)) { ?>', // date アクション
    'not_date_area' => '<?php if(empty($date_area)) { ?>',
    'category_area' => '<?php if(!empty($category_area)) { ?>', // category アクション
    'not_category_area' => '<?php if(empty($category_area)) { ?>',
    'tag_area' => '<?php if(!empty($tag_area)) { ?>',  // tag_area アクション (存在しない？
    'not_tag_area' => '<?php if(empty($tag_area)) { ?>',
    'ctag_exists' => '<?php if(!isset($t_tags)) $t_tags = \Fc2blog\Model\Model::load(\'Tags\')->getTemplateTags($blog_id); ?><?php if(!empty($t_tags)) { ?>', // タグ一覧が空でな
    'search_area' => '<?php if(!empty($search_area)) { ?>', // search アクション
    'not_search_area' => '<?php if(empty($search_area)) { ?>',
    'comment_area' => '<?php if(!empty($comment_area) && isset($entry[\'comment_accepted\']) && $entry[\'comment_accepted\']==\Fc2blog\Config::get(\'ENTRY.COMMENT_ACCEPTED.ACCEPTED\')) { ?><?php if (!empty($comment_error)) echo $comment_error; ?>', // preview_entry, pc view, sp view/?m2=res, pc comment_regist
    'not_comment_area' => '<?php if(empty($comment_area)) { ?>',
    'form_area' => '<?php if(!empty($form_area) && isset($entry[\'comment_accepted\']) && $entry[\'comment_accepted\']==\Fc2blog\Config::get(\'ENTRY.COMMENT_ACCEPTED.ACCEPTED\')) { ?><?php if (!empty($comment_error)) echo $comment_error; ?>', // view?m2=form, sp comment_regist,
    'not_form_area' => '<?php if(empty($form_area)) { ?>',
    'edit_area' => '<?php if(!empty($edit_area)) { ?><?php if (!empty($comment_error)) echo $comment_error; ?>', // comment_edit, comment_delete アクション
    'not_edit_area' => '<?php if(empty($edit_area)) { ?>',
    'comment_edit' => '<?php if(!empty($comment[\'password\'])) { ?>', // commentインスタンスにパスワードが設定されている（設定されていなければ、コメントが編集不可能）
    'trackback_area' => '<?php if(false) { ?>', // 互換性用タグ、trackback は無効化されている
    'not_trackback_area' => '<?php if(true) { ?>', // 互換性用タグ、trackback は無効化されている
    'permanent_area' => '<?php if(!empty($permanent_area)) { ?>', // preview_entry, view
    'not_permanent_area' => '<?php if(empty($permanent_area)) { ?>',
    'spplugin_area' => '<?php if(!empty($spplugin_area)) { ?><?php if(!empty($s_plugin)) $t_plugin=$s_plugin; ?>', // plugin, sp preview_plugin
    'not_spplugin_area' => '<?php if(empty($spplugin_area)) { ?>',
    // 関連する記事
    'relate_list_area' => '<?php if(false) { ?>', // 互換性用タグ、関連する記事は無効化されている
    'not_relate_list_area' => '<?php if(true) { ?>', // 互換性用タグ、関連する記事は無効化されている
    // 続きの表示
    'more_link' => '<?php if(empty($comment_area) && !empty($entry[\'extend\'])) { ?>', // view, sp view?m2=res, pc comment_regist
    'more' => '<?php if(!empty($comment_area) && !empty($entry[\'extend\'])) { ?>',
    // コメントの受付可否
    'allow_comment' => '<?php if(isset($entry[\'comment_accepted\']) && $entry[\'comment_accepted\']==\Fc2blog\Config::get(\'ENTRY.COMMENT_ACCEPTED.ACCEPTED\')) { ?>',
    'deny_comment' => '<?php if(isset($entry[\'comment_accepted\']) && $entry[\'comment_accepted\']==\Fc2blog\Config::get(\'ENTRY.COMMENT_ACCEPTED.REJECT\')) { ?>',
    // 記事のスレッドテーマも無し判定
    'community' => '<?php if(false) { ?>', // 互換性用タグ、communityは無効化されている
    // トラックバックは無し判定
    'allow_tb' => '<?php if(false) { ?>', // 互換性用タグ、trackback は無効化と思われる
    'deny_tb' => '<?php if(true) { ?>', // 互換性用タグ、trackback は無効化と思われる
    // コメントの返信の有無判定
    'comment_reply' => '<?php if(!empty($comment[\'reply_body\'])) { ?>',
    'comment_has_trip' => '<?php if(isset($comment) && isset($comment[\'trip_hash\']) && strlen($comment[\'trip_hash\'])>0) { ?>',
    // タグが存在するかどうか
    'topentry_tag' => '<?php if(!empty($entry[\'tags\'])) { ?>', // entry(loop内)でtagsがあるか
    'not_topentry_tag' => '<?php if(empty($entry[\'tags\'])) { ?>',
    // 本文に画像が存在するかどうか
    'body_img' => '<?php if(!empty($entry[\'first_image\'])) { ?>', // entry(loop内)でfirst imgが設定されているか（投稿時、更新時にエントリ内にimgタグがあったか
    'body_img_none' => '<?php if(empty($entry[\'first_image\'])) { ?>',
    // カテゴリーの各条件
    'category_parent' => '<?php if(!empty($t_category) && $t_category[\'is_parent\']) { ?>',
    'category_nosub' => '<?php if(!empty($t_category) && $t_category[\'is_nosub\']) { ?>',
    'category_sub_begin' => '<?php if(!empty($t_category) && $t_category[\'is_sub_begin\']) { ?>',
    'category_sub_hasnext' => '<?php if(!empty($t_category) && $t_category[\'is_sub_hasnext\']) { ?>',
    'category_sub_end' => '<?php if(!empty($t_category) && $t_category[\'is_sub_end\']) { ?>',
    // プラグイン系
    'plugin' => '<?php if(true) { ?>', // 互換性用タグ、プラグインは必ず有効
    'spplugin' => '<?php if(true) { ?>', // 互換性用タグ、プラグインは必ず有効
    // ページング系
    'page_area' => '<?php if(!empty($paging)) { ?>', // paging が利用可能か
    'nextpage' => '<?php if(!empty($paging) && $paging[\'is_next\']) { ?>',
    'prevpage' => '<?php if(!empty($paging) && $paging[\'is_prev\']) { ?>',
    'nextentry' => '<?php if(!empty($next_entry)) { ?>', // 次のエントリが設定されているか
    'preventry' => '<?php if(!empty($prev_entry)) { ?>', // 次のエントリが設定されているか
    'firstpage_disp' => '<?php if(!empty($paging) && $paging[\'is_prev\']) { ?>', // prevpageのエイリアス
    'lastpage_disp' => '<?php if(!empty($paging) && $paging[\'is_next\']) { ?>', // nextpageのエイリアス
    'res_nextpage_area' => '<?php if(!empty($paging) && $paging[\'is_next\']) { ?>', // nextpageのエイリアス
    'res_prevpage_area' => '<?php if(!empty($paging) && $paging[\'is_prev\']) { ?>', // prevpageのエイリアス
    // デバイスタイプ
    'ios' => '<?php if(\Fc2blog\App::isIOS($request)) { ?>', // iOSとあるが、iPhone,iPodであるかの判定、iPadはPC扱い
    'android' => '<?php if(\Fc2blog\App::isAndroid($request)) { ?>',

];

$template_vars = [
    '<%server_url>' => '<?php echo \Fc2blog\Web\Html::getServerUrl($request) . \'/\'; ?>',
    '<%blog_id>' => '<?php echo $blog_id; ?>',
    // タイトルリスト一覧
    '<%titlelist_eno>' => '<?php if(isset($entry[\'id\'])) echo $entry[\'id\']; ?>',
    '<%titlelist_title>' => '<?php if(isset($entry[\'title\'])) echo $entry[\'title\']; ?>',
    '<%titlelist_url>' => '<?php if(isset($entry[\'link\'])) echo $entry[\'link\']; ?>',
    '<%titlelist_body>' => '<?php if(isset($entry[\'body\'])) echo th($entry[\'body\'], 20); ?>',
    '<%titlelist_year>' => '<?php if(isset($entry[\'year\'])) echo $entry[\'year\']; ?>',
    '<%titlelist_month>' => '<?php if(isset($entry[\'month\'])) echo $entry[\'month\']; ?>',
    '<%titlelist_day>' => '<?php if(isset($entry[\'day\'])) echo $entry[\'day\']; ?>',
    '<%titlelist_hour>' => '<?php if(isset($entry[\'hour\'])) echo $entry[\'hour\']; ?>',
    '<%titlelist_minute>' => '<?php if(isset($entry[\'minute\'])) echo $entry[\'minute\']; ?>',
    '<%titlelist_second>' => '<?php if(isset($entry[\'second\'])) echo $entry[\'second\']; ?>',
    '<%titlelist_youbi>' => '<?php if(isset($entry[\'youbi\'])) echo $entry[\'youbi\']; ?>',
    '<%titlelist_wayoubi>' => '<?php if(isset($entry[\'wayoubi\'])) echo $entry[\'wayoubi\']; ?>',
    '<%titlelist_comment_num>' => '<?php if(isset($entry[\'comment_count\'])) echo $entry[\'comment_count\']; ?>',
    '<%titlelist_tb_num>' => '',
    // タイトルリストのカテゴリー系
    '<%titlelist_category_no>' => '<?php if(!isset($entry[\'categories\'][0][\'id\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
        . ' echo $category[\'id\'];}else{echo $entry[\'categories\'][0][\'id\'];} ?>',
    '<%titlelist_category_url>' => '<?php if(!isset($entry[\'categories\'][0][\'id\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
        . ' echo \Fc2blog\Web\Html::url($request, array(\'action\'=>\'category\', \'blog_id\'=>$entry[\'blog_id\'], \'cat\'=>$category[\'id\']));}else{'
        . ' echo \Fc2blog\Web\Html::url($request, array(\'action\'=>\'category\', \'blog_id\'=>$entry[\'blog_id\'], \'cat\'=>$entry[\'categories\'][0][\'id\']));} ?>',
    '<%titlelist_category>' => '<?php if(!isset($entry[\'categories\'][0][\'name\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
        . ' echo h($category[\'name\']);}else{echo h($entry[\'categories\'][0][\'name\']);} ?>',
    // 記事一覧
    '<%topentry_no>' => '<?php if(isset($entry[\'id\'])) echo $entry[\'id\']; ?>',
    '<%topentry_title>' => '<?php if(isset($entry[\'title\'])) echo $entry[\'title\']; ?>',
    '<%topentry_title_w_img>' => '<?php if(isset($entry[\'title_w_img\'])) echo $entry[\'title_w_img\']; ?>',
    '<%topentry_enc_title>' => '<?php if(isset($entry[\'enc_title\'])) echo $entry[\'enc_title\']; ?>',
    '<%topentry_enc_utftitle>' => '<?php if(isset($entry[\'enc_utftitle\'])) echo $entry[\'enc_utftitle\']; ?>',
    '<%topentry_body>' => <<<PHP
                                      <?php
                                        if (isset(\$entry['body'])) {
                                          if (!\$self_blog && \$entry['open_status']==\Fc2blog\Config::get('ENTRY.OPEN_STATUS.PASSWORD') && !\Fc2blog\Web\Session::get('entry_password.' . \$entry['blog_id'] . '.' . \$entry['id'])) {
                                            \$__str__1 = __("This contents is password protected.<br>You need a input password to view this.");
                                            \$__str__2 = __("Password");
                                            \$__str__3 = __("Submit");
                                            echo <<<HTML
                                            <form method="POST">
                                              <input type="hidden" name="mode" value="Entries" />
                                              <input type="hidden" name="process" value="password" />
                                              <input type="hidden" name="id" value="{\$entry['id']}" />
                                              <p>
                                              {\$__str__1}
                                              </p>
                                              <p>{\$__str__2} <input type="password" name="password" /><input type="submit" value="{\$__str__3}" /></p>
                                            </form>
                                            HTML;
                                            unset(\$__str__1,\$__str__2,\$__str__3);
                                          } else {
                                            echo \$entry['body'];
                                          }
                                        }
                                      ?>
                                      PHP
    ,
    '<%topentry_discription>' => getTopentryDiscription(),
    '<%topentry_description>' => getTopentryDiscription(),
    '<%topentry_desc>' => '',
    '<%topentry_link>' => '<?php if(isset($entry[\'link\'])) echo $entry[\'link\']; ?>',
    '<%topentry_enc_link>' => '<?php if(isset($entry[\'enc_link\'])) echo $entry[\'enc_link\']; ?>',
    '<%topentry_more>' => '<?php if(!empty($entry[\'extend\']) && ($entry[\'open_status\']!=\Fc2blog\Config::get(\'ENTRY.OPEN_STATUS.PASSWORD\') || \Fc2blog\Web\Session::get(\'entry_password.\' . $entry[\'blog_id\'] . \'.\' . $entry[\'id\']))) echo $entry[\'extend\']; ?>',
    '<%topentry_year>' => '<?php if(isset($entry[\'year\'])) echo $entry[\'year\']; ?>',
    '<%topentry_month>' => '<?php if(isset($entry[\'month\'])) echo $entry[\'month\']; ?>',
    '<%topentry_month:short>' => '<?php if(isset($entry[\'month_short\'])) echo $entry[\'month_short\']; ?>',
    '<%topentry_day>' => '<?php if(isset($entry[\'day\'])) echo $entry[\'day\']; ?>',
    '<%topentry_hour>' => '<?php if(isset($entry[\'hour\'])) echo $entry[\'hour\']; ?>',
    '<%topentry_minute>' => '<?php if(isset($entry[\'minute\'])) echo $entry[\'minute\']; ?>',
    '<%topentry_second>' => '<?php if(isset($entry[\'second\'])) echo $entry[\'second\']; ?>',
    '<%topentry_youbi>' => '<?php if(isset($entry[\'youbi\'])) echo $entry[\'youbi\']; ?>',
    '<%topentry_wayoubi>' => '<?php if(isset($entry[\'wayoubi\'])) echo $entry[\'wayoubi\']; ?>',
    '<%topentry_tb_num>' => '',
    '<%topentry_tb_no>' => '',
    '<%topentry_jointtag>' => '<?php if(!empty($entry[\'tags\'])) foreach($entry[\'tags\'] as $tag) echo \'<a href="\' . $url . \'?tag=\' . ue($tag[\'name\']) . \'">\' . h($tag[\'name\']) . \'</a>\'; ?>',
    '<%topentry_image>' => '<?php if(!empty($entry[\'first_image\'])) echo \'<img src="\' . $entry[\'first_image\'] . \'" />\'; ?>',
    '<%topentry_image_72>' => '<?php if(!empty($entry[\'first_image\'])) echo \'<img src="\' . \Fc2blog\App::getThumbnailPath($entry[\'first_image\'], 72) . \'" />\'; ?>',
    '<%topentry_image_w300>' => '<?php if(!empty($entry[\'first_image\'])) echo \'<img src="\' . \Fc2blog\App::getThumbnailPath($entry[\'first_image\'], 300, \'w\') . \'" />\'; ?>',
    '<%topentry_image_url>' => '<?php if(!empty($entry[\'first_image\'])) echo \Fc2blog\Web\Html::getServerUrl($request) . $entry[\'first_image\']; ?>',
    '<%topentry_image_url_760x420>' => '<?php if(!empty($entry[\'first_image\'])) echo \Fc2blog\Web\Html::getServerUrl($request) . \Fc2blog\App::getCenterThumbnailPath($entry[\'first_image\'], 760, 420, \'wh\'); ?>',
    '<%topentry_comment_num>' => '<?php if(isset($entry[\'comment_count\'])) echo $entry[\'comment_count\']; ?>',
    // 記事のカテゴリー系
    '<%topentry_category_no>' => '<?php if(!isset($entry[\'categories\'][0][\'id\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
        . ' echo $category[\'id\'];}else{echo $entry[\'categories\'][0][\'id\'];} ?>',
    '<%topentry_category_link>' => '<?php if(!isset($entry[\'categories\'][0][\'id\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
        . ' echo \Fc2blog\Web\Html::url($request, array(\'action\'=>\'category\', \'blog_id\'=>$entry[\'blog_id\'], \'cat\'=>$category[\'id\']));}else{'
        . ' echo \Fc2blog\Web\Html::url($request, array(\'action\'=>\'category\', \'blog_id\'=>$entry[\'blog_id\'], \'cat\'=>$entry[\'categories\'][0][\'id\']));} ?>',
    '<%topentry_category>' => '<?php if(!isset($entry[\'categories\'][0][\'name\'])){}else if(!empty($category) && $category[\'entry_id\']==$entry[\'categories\'][0][\'entry_id\']){'
        . ' echo h($category[\'name\']);}else{echo h($entry[\'categories\'][0][\'name\']);} ?>',
    // 記事のタグ系
    '<%topentry_tag_list_name>' => '<?php if(isset($tag[\'name\'])) echo h($tag[\'name\']); ?>',
    '<%topentry_tag_list_parsename>' => '<?php if(isset($tag[\'name\'])) echo ue($tag[\'name\']); ?>',
    '<%ctag_name>' => '<?php if(isset($t_tag[\'name\'])) echo h($t_tag[\'name\']); ?>', // TODO $t_tag がコード中にみつからない
    '<%ctag_url>' => '<?php if(isset($t_tag[\'name\'])) echo $url . \'?tag=\' . ue($t_tag[\'name\']); ?>', // TODO $t_tag がコード中にみつからない
    // 記事一覧のコメント表示
    '<%topentry_comment_list_name>' => '<?php if(isset($comment[\'name\'])) echo h($comment[\'name\']); ?>',
    '<%topentry_comment_list_title>' => '<?php if(isset($comment[\'title\'])) echo h($comment[\'title\']); ?>',
    '<%topentry_comment_list_body>' => '<?php if(isset($comment[\'body\'])) echo h($comment[\'body\']); ?>',
    '<%topentry_comment_list_brbody>' => '<?php if(isset($comment[\'body\'])) echo nl2br(h($comment[\'body\'])); ?>',
    '<%topentry_comment_list_date>' => '<?php if(isset($comment[\'created_at\'])) echo $comment[\'created_at\']; ?>',
    // コメント一覧
    '<%comment_no>' => '<?php if(isset($comment[\'id\'])) echo $comment[\'id\']; ?>',
    '<%comment_title>' => '<?php if(isset($comment[\'title\'])) echo h($comment[\'title\']); ?>',
    '<%comment_body>' => '<?php if(isset($comment[\'body\'])) echo nl2br(h($comment[\'body\'])); ?>',
    '<%comment_year>' => '<?php if(isset($comment[\'year\'])) echo $comment[\'year\']; ?>',
    '<%comment_month>' => '<?php if(isset($comment[\'month\'])) echo $comment[\'month\']; ?>',
    '<%comment_day>' => '<?php if(isset($comment[\'day\'])) echo $comment[\'day\']; ?>',
    '<%comment_hour>' => '<?php if(isset($comment[\'hour\'])) echo $comment[\'hour\']; ?>',
    '<%comment_minute>' => '<?php if(isset($comment[\'minute\'])) echo $comment[\'minute\']; ?>',
    '<%comment_second>' => '<?php if(isset($comment[\'second\'])) echo $comment[\'second\']; ?>',
    '<%comment_youbi>' => '<?php if(isset($comment[\'youbi\'])) echo $comment[\'youbi\']; ?>',
    '<%comment_wayoubi>' => '<?php if(isset($comment[\'wayoubi\'])) echo $comment[\'wayoubi\']; ?>',
    '<%comment_edit_link>' => '<?php if(isset($comment[\'edit_link\'])) echo $comment[\'edit_link\']; ?>',
    '<%comment_name>' => '<?php if(isset($comment[\'name\'])) echo h($comment[\'name\']); ?>',
    '<%comment_mail>' => '<?php if(isset($comment[\'mail\'])) echo $comment[\'mail\']; ?>',
    '<%comment_url>' => '<?php if(isset($comment[\'url\'])) echo $comment[\'url\']; ?>',
    '<%comment_url+str>' => '<?php if(isset($comment[\'url\'])) echo \'<a href="\' . $comment[\'url\'] . \'">\' . $comment[\'url\'] . \'</a>\'; ?>',
    '<%comment_mail+name>' => '<?php if(!isset($comment[\'name\'])){}else if(!empty($comment[\'mail\'])){ echo \'<a href="mailto:\' . $comment[\'mail\'] . \'">\' . h($comment[\'name\']) . \'</a>\'; }else{ echo h($comment[\'name\']); } ?>',
    '<%comment_url+name>' => '<?php if(!isset($comment[\'name\'])){}else if(!empty($comment[\'url\'])){ echo \'<a href="\' . $comment[\'url\'] . \'">\' . h($comment[\'name\']) . \'</a>\'; }else{ echo h($comment[\'name\']); } ?>',
    '<%comment_trip>' => '<?php if(isset($comment[\'trip_hash\'])) echo $comment[\'trip_hash\']; ?>',
    // コメント一覧の返信分
    '<%comment_reply_body>' => '<?php if(isset($comment[\'reply_body\'])) echo $comment[\'reply_body\']; ?>',
    '<%comment_reply_year>' => '<?php if(isset($comment[\'reply_year\'])) echo $comment[\'reply_year\']; ?>',
    '<%comment_reply_month>' => '<?php if(isset($comment[\'reply_month\'])) echo $comment[\'reply_month\']; ?>',
    '<%comment_reply_day>' => '<?php if(isset($comment[\'reply_day\'])) echo $comment[\'reply_day\']; ?>',
    '<%comment_reply_hour>' => '<?php if(isset($comment[\'reply_hour\'])) echo $comment[\'reply_hour\']; ?>',
    '<%comment_reply_minute>' => '<?php if(isset($comment[\'reply_minute\'])) echo $comment[\'reply_minute\']; ?>',
    '<%comment_reply_second>' => '<?php if(isset($comment[\'reply_second\'])) echo $comment[\'reply_second\']; ?>',
    '<%comment_reply_youbi>' => '<?php if(isset($comment[\'reply_youbi\'])) echo $comment[\'reply_youbi\']; ?>',
    '<%comment_reply_wayoubi>' => '<?php if(isset($comment[\'reply_wayoubi\'])) echo $comment[\'reply_wayoubi\']; ?>',
    // コメントのクッキー情報
    '<%cookie_name>' => '<?php if(empty($comment_error) && $request->getCookie(\'comment_name\')) echo $request->getCookie(\'comment_name\'); ?>',
    '<%cookie_mail>' => '<?php if(empty($comment_error) && $request->getCookie(\'comment_mail\')) echo $request->getCookie(\'comment_mail\'); ?>',
    '<%cookie_url>' => '<?php if(empty($comment_error) && $request->getCookie(\'comment_url\')) echo $request->getCookie(\'comment_url\'); ?>',
    // コメント編集
    '<%eno>' => '<?php if(isset($edit_comment[\'id\'])) echo $edit_comment[\'id\']; ?>',
    '<%edit_name>' => '<?php if(isset($edit_comment[\'name\'])) echo $edit_comment[\'name\']; ?>',
    '<%edit_title>' => '<?php if(isset($edit_comment[\'title\'])) echo $edit_comment[\'title\']; ?>',
    '<%edit_mail>' => '<?php if(isset($edit_comment[\'mail\'])) echo $edit_comment[\'mail\']; ?>',
    '<%edit_url>' => '<?php if(isset($edit_comment[\'url\'])) echo $edit_comment[\'url\']; ?>',
    '<%edit_body>' => '<?php if(isset($edit_comment[\'body\'])) echo $edit_comment[\'body\']; ?>',
    '<%edit_message>' => '<?php if(isset($edit_comment[\'message\'])) echo $edit_comment[\'message\']; ?>',
    '<%edit_entry_no>' => '<?php if(isset($edit_entry[\'id\'])) echo $edit_entry[\'id\']; ?>',
    '<%edit_entry_title>' => '<?php if(isset($edit_entry[\'title\'])) echo $edit_entry[\'title\']; ?>',
    // プラグイン
    '<%plugin_first_title>' => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
    '<%plugin_second_title>' => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
    '<%plugin_third_title>' => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
    '<%plugin_first_content>' => '<?php if(isset($t_plugin[\'id\'])) include(\Fc2blog\App::getPluginFilePath($blog_id, $t_plugin[\'id\'])); ?>',
    '<%plugin_second_content>' => '<?php if(isset($t_plugin[\'id\'])) include(\Fc2blog\App::getPluginFilePath($blog_id, $t_plugin[\'id\'])); ?>',
    '<%plugin_third_content>' => '<?php if(isset($t_plugin[\'id\'])) include(\Fc2blog\App::getPluginFilePath($blog_id, $t_plugin[\'id\'])); ?>',
    '<%plugin_first_description>' => '',
    '<%plugin_first_description2>' => '',
    '<%plugin_second_description>' => '',
    '<%plugin_second_description2>' => '',
    '<%plugin_third_description>' => '',
    '<%plugin_third_description2>' => '',
    '<%plugin_first_talign>' => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
    '<%plugin_second_talign>' => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
    '<%plugin_third_talign>' => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
    '<%plugin_first_tcolor>' => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
    '<%plugin_second_tcolor>' => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
    '<%plugin_third_tcolor>' => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
    '<%plugin_first_align>' => '<?php if(isset($t_plugin[\'contents_align\'])) echo $t_plugin[\'contents_align\']; ?>',
    '<%plugin_second_align>' => '<?php if(isset($t_plugin[\'contents_align\'])) echo $t_plugin[\'contents_align\']; ?>',
    '<%plugin_third_align>' => '<?php if(isset($t_plugin[\'contents_align\'])) echo $t_plugin[\'contents_align\']; ?>',
    '<%plugin_first_color>' => '<?php if(isset($t_plugin[\'contents_color\'])) echo $t_plugin[\'contents_color\']; ?>',
    '<%plugin_second_color>' => '<?php if(isset($t_plugin[\'contents_color\'])) echo $t_plugin[\'contents_color\']; ?>',
    '<%plugin_third_color>' => '<?php if(isset($t_plugin[\'contents_color\'])) echo $t_plugin[\'contents_color\']; ?>',
    '<%plugin_third_ialign>' => '',
    '<%plugin_first_ialign>' => '',
    '<%plugin_second_ialign>' => '',

// プラグイン(スマフォ用)
    '<%spplugin_first_no>' => '<?php if(isset($t_plugin[\'id\'])) echo $t_plugin[\'id\']; ?>',
    '<%spplugin_first_title>' => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>',
    '<%spplugin_title>' => '<?php if(isset($t_plugin[\'title\'])) echo $t_plugin[\'title\']; ?>', // spplugin_first_titleのエイリアス
    '<%spplugin_content>' => '<?php if(isset($t_plugin[\'id\'])) include(\Fc2blog\App::getPluginFilePath($blog_id, $t_plugin[\'id\'])); ?>',
    '<%spplugin_talign>' => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
    '<%spplugin_tcolor>' => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
    '<%spplugin_first_talign>' => '<?php if(isset($t_plugin[\'title_align\'])) echo $t_plugin[\'title_align\']; ?>',
    '<%spplugin_first_tcolor>' => '<?php if(isset($t_plugin[\'title_color\'])) echo $t_plugin[\'title_color\']; ?>',
    '<%spplugin_align>' => '<?php if(isset($t_plugin[\'contents_align\'])) echo $t_plugin[\'contents_align\']; ?>',
    '<%spplugin_color>' => '<?php if(isset($t_plugin[\'contents_color\'])) echo $t_plugin[\'contents_color\']; ?>',
    // 最新の記事一覧(プラグイン系)
    '<%recent_no>' => '<?php if(isset($t_recent[\'id\'])) echo $t_recent[\'id\']; ?>',
    '<%recent_title>' => '<?php if(isset($t_recent[\'title\'])) echo $t_recent[\'title\']; ?>',
    '<%recent_link>' => '<?php if(isset($t_recent[\'link\'])) echo $t_recent[\'link\']; ?>',
    '<%recent_body>' => '<?php if(isset($t_recent[\'body\'])) echo th($t_recent[\'body\'], 50); ?>',
    '<%recent_year>' => '<?php if(isset($t_recent[\'year\'])) echo $t_recent[\'year\']; ?>',
    '<%recent_month>' => '<?php if(isset($t_recent[\'month\'])) echo $t_recent[\'month\']; ?>',
    '<%recent_day>' => '<?php if(isset($t_recent[\'day\'])) echo $t_recent[\'day\']; ?>',
    '<%recent_hour>' => '<?php if(isset($t_recent[\'hour\'])) echo $t_recent[\'hour\']; ?>',
    '<%recent_minute>' => '<?php if(isset($t_recent[\'minute\'])) echo $t_recent[\'minute\']; ?>',
    '<%recent_second>' => '<?php if(isset($t_recent[\'second\'])) echo $t_recent[\'second\']; ?>',
    '<%recent_youbi>' => '<?php if(isset($t_recent[\'youbi\'])) echo $t_recent[\'youbi\']; ?>',
    '<%recent_wayoubi>' => '<?php if(isset($t_recent[\'wayoubi\'])) echo $t_recent[\'wayoubi\']; ?>',
    '<%recent_image_w300>' => '<?php if(!empty($t_recent[\'first_image\'])) echo \'<img src="\' . \Fc2blog\App::getThumbnailPath($t_recent[\'first_image\'], 300, \'w\') . \'" />\'; ?>',
    // カテゴリー一覧(プラグイン系)
    '<%category_no>' => '<?php if(!empty($t_category)) echo $t_category[\'id\']; ?>',
    '<%category_number>' => '<?php if(!empty($t_category)) echo $t_category[\'id\']; ?>',
    '<%category_link>' => '<?php if(!empty($t_category)) echo \Fc2blog\Web\Html::url($request, array(\'action\'=>\'category\', \'blog_id\'=>$t_category[\'blog_id\'], \'cat\'=>$t_category[\'id\'])); ?>',
    '<%category_name>' => '<?php if(!empty($t_category)) echo $t_category[\'name\']; ?>',
    '<%category_count>' => '<?php if(!empty($t_category)) echo $t_category[\'count\']; ?>',
    // アーカイブ一覧(プラグイン系)
    '<%archive_link>' => '<?php if(!empty($t_archive)) echo \Fc2blog\Web\Html::url($request, array(\'blog_id\'=>$blog_id, \'action\'=>\'date\', \'date\'=>$t_archive[\'year\'] . $t_archive[\'month\'])); ?>',
    '<%archive_count>' => '<?php if(!empty($t_archive)) echo $t_archive[\'count\']; ?>',
    '<%archive_year>' => '<?php if(!empty($t_archive)) echo $t_archive[\'year\']; ?>',
    '<%archive_month>' => '<?php if(!empty($t_archive)) echo $t_archive[\'month\']; ?>',
    // カレンダー一覧(プラグイン系)
    '<%calendar_sun>' => '<?php if(isset($t_calendar[0])) echo $t_calendar[0]; ?>',
    '<%calendar_mon>' => '<?php if(isset($t_calendar[1])) echo $t_calendar[1]; ?>',
    '<%calendar_tue>' => '<?php if(isset($t_calendar[2])) echo $t_calendar[2]; ?>',
    '<%calendar_wed>' => '<?php if(isset($t_calendar[3])) echo $t_calendar[3]; ?>',
    '<%calendar_thu>' => '<?php if(isset($t_calendar[4])) echo $t_calendar[4]; ?>',
    '<%calendar_fri>' => '<?php if(isset($t_calendar[5])) echo $t_calendar[5]; ?>',
    '<%calendar_sat>' => '<?php if(isset($t_calendar[6])) echo $t_calendar[6]; ?>',
    '<%calender_sun>' => '<?php if(isset($t_calendar[0])) echo $t_calendar[0]; ?>',
    '<%calender_mon>' => '<?php if(isset($t_calendar[1])) echo $t_calendar[1]; ?>',
    '<%calender_tue>' => '<?php if(isset($t_calendar[2])) echo $t_calendar[2]; ?>',
    '<%calender_wed>' => '<?php if(isset($t_calendar[3])) echo $t_calendar[3]; ?>',
    '<%calender_thu>' => '<?php if(isset($t_calendar[4])) echo $t_calendar[4]; ?>',
    '<%calender_fri>' => '<?php if(isset($t_calendar[5])) echo $t_calendar[5]; ?>',
    '<%calender_sat>' => '<?php if(isset($t_calendar[6])) echo $t_calendar[6]; ?>',
    // コメント一覧(テンプレート変数系)
    '<%rcomment_keyno>' => '<?php if(isset($t_comment[\'entry_id\'])) echo $t_comment[\'entry_id\']; ?>',
    '<%rcomment_etitle>' => '<?php if(isset($t_comment[\'entry_title\'])) echo $t_comment[\'entry_title\']; ?>',
    '<%rcomment_link>' => '<?php if(isset($t_comment[\'link\'])) echo $t_comment[\'link\']; ?>',
    '<%rcomment_no>' => '<?php if(isset($t_comment[\'id\'])) echo $t_comment[\'id\']; ?>',
    '<%rcomment_title>' => '<?php if(isset($t_comment[\'title\'])) echo $t_comment[\'title\']; ?>',
    '<%rcomment_name>' => '<?php if(isset($t_comment[\'name\'])) echo $t_comment[\'name\']; ?>',
    '<%rcomment_body>' => '<?php if(isset($t_comment[\'body\'])) echo $t_comment[\'body\']; ?>',
    '<%rcomment_year>' => '<?php if(isset($t_comment[\'year\'])) echo $t_comment[\'year\']; ?>',
    '<%rcomment_month>' => '<?php if(isset($t_comment[\'month\'])) echo $t_comment[\'month\']; ?>',
    '<%rcomment_day>' => '<?php if(isset($t_comment[\'day\'])) echo $t_comment[\'day\']; ?>',
    '<%rcomment_hour>' => '<?php if(isset($t_comment[\'hour\'])) echo $t_comment[\'hour\']; ?>',
    '<%rcomment_minute>' => '<?php if(isset($t_comment[\'minute\'])) echo $t_comment[\'minute\']; ?>',
    '<%rcomment_second>' => '<?php if(isset($t_comment[\'second\'])) echo $t_comment[\'second\']; ?>',
    '<%rcomment_youbi>' => '<?php if(isset($t_comment[\'youbi\'])) echo $t_comment[\'youbi\']; ?>',
    '<%rcomment_wayoubi>' => '<?php if(isset($t_comment[\'wayoubi\'])) echo $t_comment[\'wayoubi\']; ?>',
    '<%rcomment_mail>' => '<?php if(isset($t_comment[\'mail\'])) echo $t_comment[\'mail\']; ?>',
    '<%rcomment_url>' => '<?php if(isset($t_comment[\'url\'])) echo $t_comment[\'url\']; ?>',
    '<%rcomment_url+str>' => '<?php if(isset($t_comment[\'url\'])) echo \'<a href="\' . $t_comment[\'url\'] . \'">\' . $t_comment[\'url\'] . \'</a>\'; ?>',
    '<%rcomment_mail+name>' => '<?php if(!isset($t_comment[\'name\'])){}else if(!empty($t_comment[\'mail\'])){ echo \'<a href="mailto:\' . $t_comment[\'mail\'] . \'">\' . $t_comment[\'name\'] . \'</a>\'; }else{ echo $t_comment[\'name\']; } ?>',
    '<%rcomment_url+name>' => '<?php if(!isset($t_comment[\'name\'])){}else if(!empty($t_comment[\'url\'])){ echo \'<a href="\' . $t_comment[\'url\'] . \'">\' . $t_comment[\'name\'] . \'</a>\'; }else{ echo $t_comment[\'name\']; } ?>',
    // ページング系
    '<%nextpage_url>' => '<?php if(!empty($paging) && $paging[\'is_next\']) echo \Fc2blog\Web\Html::url($request, array(\'page\'=>$paging[\'page\']+1, \'blog_id\'=>$blog_id), true); ?>',
    '<%prevpage_url>' => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo \Fc2blog\Web\Html::url($request, array(\'page\'=>$paging[\'page\']-1, \'blog_id\'=>$blog_id), true); ?>',
    '<%days>' => '<?php echo date(\'d\', strtotime($now_date)); ?>',
    '<%now_year>' => '<?php echo date(\'Y\', strtotime($now_date)); ?>',
    '<%now_month>' => '<?php echo date(\'m\', strtotime($now_date)); ?>',
    '<%prev_month>' => '<?php echo date(\'m\', strtotime($prev_month_date)); ?>',
    '<%prev_year>' => '<?php echo date(\'Y\', strtotime($prev_month_date)); ?>',
    '<%next_month>' => '<?php echo date(\'m\', strtotime($next_month_date)); ?>',
    '<%next_year>' => '<?php echo date(\'Y\', strtotime($next_month_date)); ?>',
    '<%prev_month_link>' => '<?php echo \Fc2blog\Web\Html::url($request, array(\'blog_id\'=>$blog_id, \'action\'=>\'date\', \'date\'=>date(\'Ym\', strtotime($prev_month_date)))); ?>',
    '<%next_month_link>' => '<?php echo \Fc2blog\Web\Html::url($request, array(\'blog_id\'=>$blog_id, \'action\'=>\'date\', \'date\'=>date(\'Ym\', strtotime($next_month_date)))); ?>',
    '<%nextentry_url>' => '<?php if(!empty($next_entry)) echo \Fc2blog\App::userURL($request,array(\'id\'=>$next_entry[\'id\'], \'blog_id\'=>$blog_id)); ?>',
    '<%nextentry_title>' => '<?php if(!empty($next_entry)) echo h($next_entry[\'title\']); ?>',
    '<%preventry_url>' => '<?php if(!empty($prev_entry)) echo \Fc2blog\App::userURL($request,array(\'id\'=>$prev_entry[\'id\'], \'blog_id\'=>$blog_id)); ?>',
    '<%preventry_title>' => '<?php if(!empty($prev_entry)) echo h($prev_entry[\'title\']); ?>',
    '<%firstpage_num>' => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo 1; ?>',
    '<%lastpage_num>' => '<?php if(!empty($paging) && $paging[\'is_next\']) echo $paging[\'max_page\']; ?>',
    '<%firstpage_url>' => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo \Fc2blog\Web\Html::url($request, array(\'page\'=>0, \'blog_id\'=>$blog_id), true); ?>',
    '<%lastpage_url>' => '<?php if(!empty($paging) && $paging[\'is_next\']) echo \Fc2blog\Web\Html::url($request, array(\'page\'=>$paging[\'max_page\']-1, \'blog_id\'=>$blog_id), true); ?>',
    '<%current_page_num>' => '<?php if(!empty($paging)) echo $paging[\'page\']+1; ?>',
    '<%total_pages>' => '<?php if(!empty($paging)) echo $paging[\'max_page\']; ?>',
    '<%total_num>' => '<?php if(!empty($paging)) echo $paging[\'count\']; ?>',
    '<%tail_url>' => '',
    '<%template_pager1>' => getFc2PagingPHP(1),
    '<%template_pager2>' => getFc2PagingPHP(2),
    '<%template_pager3>' => getFc2PagingPHP(3),
    '<%template_pager4>' => getFc2PagingPHP(4),
    '<%template_pager5>' => getFc2PagingPHP(5),
    '<%res_nextpage_url>' => '<?php if(!empty($paging) && $paging[\'is_next\']) echo \Fc2blog\Web\Html::url($request, array(\'page\'=>$paging[\'page\']+1, \'blog_id\'=>$blog_id), true); ?>',
    '<%res_prevpage_url>' => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo \Fc2blog\Web\Html::url($request, array(\'page\'=>$paging[\'page\']-1, \'blog_id\'=>$blog_id), true); ?>',
    '<%res_firstpage_url>' => '<?php if(!empty($paging) && $paging[\'is_prev\']) echo \Fc2blog\Web\Html::url($request, array(\'page\'=>0, \'blog_id\'=>$blog_id), true); ?>',
    '<%res_lastpage_url>' => '<?php if(!empty($paging) && $paging[\'is_next\']) echo \Fc2blog\Web\Html::url($request, array(\'page\'=>$paging[\'max_page\']-1, \'blog_id\'=>$blog_id), true); ?>',
    '<%res_template_pager1>' => getFc2PagingPHP(1),
    '<%res_template_pager2>' => getFc2PagingPHP(2),
    '<%res_template_pager3>' => getFc2PagingPHP(3),
    '<%res_template_pager4>' => getFc2PagingPHP(4),
    '<%res_template_pager5>' => getFc2PagingPHP(5),
    '<%rapid_templates_autopager>' => '!function(){if(\'undefined\'!== typeof window.Autopager){return;}window.Autopager = {};window.Autopager.onPageChangeLister = null;const SCROLL_MARGIN = 1080;const DELAY_TIME = 30;const STATE = {WAIT: 0,RUNNING: 1,COMPLETE: 2};let timerId = null;let handlerId = null;let state = STATE.WAIT;let precache = null;const getUserTagQuery=()=>{const query=location.search.replace(\'?\',\'\');if(!query){return \'\';};const queries=query.split(\'&\');let userTag=\'\';queries.forEach(q=>{const matched=q.match(/^tag=.+$/);if(matched){userTag=q;}});return userTag;};const getSearchQuery=()=>{const query=location.search.replace(\'?\',\'\');if(!query){return \'\';};const queries=query.split(\'&\');let searchQuery=\'\';queries.forEach(q=>{const matched=q.match(/^q=.+$/);if(matched){searchQuery=q;}});return searchQuery;};const getSelector=()=>{return getSearchQuery()?\'.search_list\':\'.entryList\';};const scrollingElement = document.scrollingElement ? document.scrollingElement : document.documentElement;const initialize=()=>{fetchPage(1).then(()=>{autopager();}).catch(error=>{});};const onChangePage=()=>{state = STATE.WAIT;nextPage = 0;precache = null;if (window.Autopager.onPageChangeLister) {window.Autopager.onPageChangeLister();}};const autopager=()=>{if (!/^\/(?:blog-(category|date)-[0-9]+?\.html|[ec]\/.+)?$/.test(location.pathname)) {return false;};clearTimeout(timerId);timerId=setTimeout(()=>{if (!isExceededScrollBottom()) {return;};let nextPage = 0;try{nextPage = findLastIndexKey()+1;if(precache && getPrecacheLastIndexKey() === nextPage){showPrecache();nextPage = findLastIndexKey()+1;}}catch(ex) {return Promise.reject(\'there is no more\');};fetchPage(nextPage).catch(error=>{});}, DELAY_TIME);};const findLastIndexKey=()=>{let pageKey = 0;if (!document.querySelector(getSelector())) {throw Exception(getSelector()+\' is not found\');};document.querySelector(getSelector()).childNodes.forEach(node=>{if(undefined!==node.dataset){if(undefined!==node.dataset.pageKey){pageKey=parseInt(node.dataset.pageKey);}}});return pageKey;};const fetchPage=async (num)=>{if (state!==STATE.WAIT) {return Promise.reject(\'autopager is running\');};state=STATE.RUNNING;let params=[];if (getSearchQuery()) {params.push(getSearchQuery());} else if (getUserTagQuery()) {params.push(getUserTagQuery());}params.push(\'page=\'+num);params.push(\'more\');try {const response = await fetch(location.pathname + \'?\' + params.join(\'&\')).then(response=>{if (!response.ok) {return Promise.reject(\'status: \' + response.status + \', msg: \' + response.statusText);};return response;}).catch(error=>{state = STATE.COMPLETE;return Promise.reject(error);});const text = await response.text();if (!text) {state = STATE.COMPLETE;return Promise.reject(\'there is no content\');}let div = document.createElement(\'div\');let flagment = document.createDocumentFragment();div.innerHTML = text;div.childNodes.forEach((node, i) => {if (node.nodeType === Node.ELEMENT_NODE) {node = checkNewMark(node);flagment.appendChild(node);}});div = null;if (flagment.firstChild) {flagment.firstChild.dataset.pageKey = num;};precache = flagment;state = STATE.WAIT;return Promise.resolve();} catch (error) {state = STATE.COMPLETE;}};const checkNewMark=(node)=>{const elements = node.querySelectorAll(""span[data-fc2-newmark]"");if(elements){const date = new Date();Array.prototype.forEach.call(elements, function(element){Math.abs(date-element.getAttribute(""data-time-post-entry"")*1e3)/36e5<24?element.innerHTML=element.getAttribute(""data-fc2-newmark""):element.parentNode.removeChild(element);});};return node;};const showPrecache=()=>{if(!precache){return false;}document.querySelector(getSelector()).appendChild(precache);precache=null;};const getPrecacheLastIndexKey=()=>{if(!precache){return 0;}return parseInt(precache.firstChild.dataset.pageKey, 10);};const isExceededScrollBottom=()=>{return (scrollingElement.querySelector(getSelector()).scrollHeight-document.documentElement.querySelector(getSelector()).clientHeight-scrollingElement.scrollTop<=SCROLL_MARGIN);};const Handler=(()=>{var i=1,listeners={};return {addListener: function(element,event,handler,capture=false){element.addEventListener(event, handler, capture);listeners[i]={element, event, handler, capture};return i++;},removeListener:id=>{if(id in listeners) {var h=listeners[id];h.element.removeEventListener(h.event, h.handler, h.capture);delete listeners[id];}}};})();window.Autopager.pageChangeLister=(callback)=>{if (""function"" !== typeof callback) {return false;}window.Autopager.onPageChangeLister = callback;};if(handlerId){Handler.removeListener(handlerId);};handlerId=Handler.addListener(window, ""scroll"", autopager);Handler.addListener(window, ""popstate"", ()=>{if (state===STATE.COMPLETE) {state=STATE.WAIT;}});let timer=setInterval(()=>{if(\'object\'!==typeof InstantClick){return;};clearInterval(timer);InstantClick.on(\'change\', onChangePage);},200);initialize();}();',
    // 全体変数
    '<%css_link>' => '<?php if(isset($css_link)) echo $css_link; ?>',
    '<%url>' => '<?php if(isset($url)) echo $url; ?>',
    '<%blog_name>' => '<?php if(isset($blog[\'name\'])) echo h($blog[\'name\']); ?>',
    '<%author_name>' => '<?php if(isset($blog[\'nickname\'])) echo h($blog[\'nickname\']); ?>',
    '<%introduction>' => '<?php if(isset($blog[\'introduction\'])) echo h($blog[\'introduction\']); ?>',
    '<%pno>' => '<?php if(isset($entry[\'id\'])) echo $entry[\'id\']; ?>',
    '<%sub_title>' => '<?php if(isset($sub_title)) echo h($sub_title); ?>',
    '<%template_comment_js>' => ''/* OSS版では未実装となります ref: https://github.com/fc2blog/blog/issues/92 */,
    '<%template_copyright_date>' => date('Y'),
    '<%ad>' => '',
    '<%ad2>' => '',
    '<%ad_overlay>' => '',
    // i18n系
    '<%template_fc2blog>' => '<?php echo __(\'FC2 BLOG\'); ?>',
    '<%template_extend>' => '<?php echo __(\'Read more\'); ?>',
    '<%template_theme>' => '<?php echo __(\'Theme\'); ?>',
    '<%template_genre>' => '<?php echo __(\'Genre\'); ?>',
    '<%template_trackback>' => '<?php echo __(\'Trackbacks\'); ?>',
    '<%template_comment>' => '<?php echo __(\'Comments\'); ?>',
    '<%template_abs_link>' => '<?php echo __(\'Entry Absolute link\'); ?>',
    '<%template_category>' => '<?php echo __(\'Entry category\'); ?>',
    '<%template_view_category>' => '<?php echo __(\'View category list\'); ?>',
    '<%template_edit>' => '<?php echo __(\'Edit\'); ?>',
    '<%template_title>' => '<?php echo __(\'Title\'); ?>',
    '<%template_name>' => '<?php echo __(\'Name\'); ?>',
    '<%template_address>' => '<?php echo __(\'E-mail address\'); ?>',
    '<%template_body>' => '<?php echo __(\'Body\'); ?>',
    '<%template_post_comment>' => '<?php echo __(\'Post a comment\'); ?>',
    '<%template_private>' => '<?php echo __(\'Private message\'); ?>',
    '<%template_private_check>' => '<?php echo __(\'Only the blog author may view the message.\'); ?>',
    '<%template_password>' => '<?php echo __(\'Edit password\'); ?>',
    '<%template_send>' => '<?php echo __(\'Send\'); ?>',
    '<%template_delete>' => '<?php echo __(\'Delete\'); ?>',
    '<%template_edit_comment>' => '<?php echo __(\'Edit a comment\'); ?>',
    '<%template_trackback_this>' => '<?php echo __(\'Use trackback for this entry.\'); ?>',
    '<%template_home>' => '<?php echo __(\'Home\'); ?>',
    '<%template_index>' => '<?php echo __(\'Index\'); ?>',
    '<%template_firstentry>' => '<?php echo __(\'First entry\'); ?>',
    '<%template_search_entry>' => '<?php echo __(\'Search entries\'); ?>',
    '<%template_prevpage>' => '<?php echo __(\'Previous page\'); ?>',
    '<%template_nextpage>' => '<?php echo __(\'Next page\'); ?>',
    '<%template_preventry>' => '<?php echo __(\'Previous entry\'); ?>',
    '<%template_nextentry>' => '<?php echo __(\'Next entry\'); ?>',
    '<%template_go_top>' => '<?php echo __(\'Top of page\'); ?>',
    "<%template_charset>" => '<?php echo __(\'utf-8\'); ?>',
    "<%template_return_post>" => '<?php echo __(\'Return to post\'); ?>',
    "<%template_write_cm>" => '<?php echo __(\'Write Comment\'); ?>',
    "<%template_month>" => '<?php echo __(\'Date Separator Month\'); ?>',
    "<%template_year>" => '<?php echo __(\'Date Separator Year\'); ?>',
    "<%template_date>" => '<?php echo \Fc2blog\Lib\WordTag::replace(__(\'Date Separator Day\')); ?>',
    "<%template_language>" => '<?php echo __(\'en\'); ?>',
    "<%template_privacy_set>" => '<?php echo __(\'Privacy Settings\'); ?>',
    "<%template_privacy_secret>" => '<?php echo __(\'Secret comment to blog author\'); ?>',
    "<%template_privacy_public>" => '<?php echo __(\'Public Comment\'); ?>',
    "<%template_cm_body>" => '<?php echo __(\'Comment Body\'); ?>',
    "<%template_required>" => '<?php echo __(\'Required\'); ?>',
    "<%template_next>" => '<?php echo __(\'Next\'); ?>',
    "<%template_prev>" => '<?php echo __(\'Prev\'); ?>',
    "<%template_tell_friend>" => '<?php echo __(\'Tell a friend\'); ?>',
    "<%template_login>" => '<?php echo __(\'Login\'); ?>',
    "<%template_show_pc>" => '<?php echo __(\'PC Version\'); ?>',
    "<%template_update>" => '<?php echo __(\'To Update\'); ?>',
    "<%template_sp_delete>" => '<?php echo __(\'To Delete\'); ?>',
    "<%template_tb_list>" => '<?php echo __(\'Trackback List\'); ?>',
    "<%template_edit_cm>" => '<?php echo __(\'Edit comment\'); ?>',
    "<%template_page_top>" => '<?php echo __(\'Top of Page\'); ?>',
    "<%template_sp_post>" => '<?php echo __(\'To Post\'); ?>',
    "<%template_cm_list>" => '<?php echo __(\'Read comments\'); ?>',
    "<%template_tb_close>" => '<?php echo __(\'Trackback closed\'); ?>',
    "<%template_show_pic>" => '<?php echo __(\'Photos\'); ?>',
    "<%template_cm_close>" => '<?php echo __(\'Comments closed\'); ?>',
    "<%template_pc_view>" => '<?php echo __(\'PC View\'); ?>',
    "<%template_category_word>" => '<?php echo __(\'Category (Pattern2)\'); ?>',
    "<%template_write_cm_2lines>" => '<?php echo __(\'Write a<br>Comment\'); ?>',
    "<%template_last_page>" => '<?php echo __(\'Last Page\'); ?>',
    "<%template_pgof_1>" => '<?php echo __(\'pg /\'); ?>',
    "<%template_pgof_2>" => '<?php echo __(\'pgs\'); ?>',
    "<%template_first_page>" => '<?php echo __(\'First Page\'); ?>',
    "<%template_cm_post>" => '<?php echo __(\'Post a Comment\'); ?>',
    "<%template_newest_entries>" => '<?php echo __(\'Newest Entry\'); ?>',
    "<%template_cm_list_of>" => '<?php echo __(\'\\\'s Comment List\'); ?>',
    "<%template_write_cm_to>" => '<?php echo __(\'-&gt; Write a Comment\'); ?>',
    "<%template_album>" => '<?php echo __(\'Album\'); ?>',
    "<%template_posted>" => '<?php echo __(\'Post\'); ?>',
    "<%template_load_more>" => '<?php echo __(\'Load more...\'); ?>',
    "<%template_view_cm>" => '<?php echo __(\'View Comments\'); ?>',
    "<%template_newest_comments>" => '<?php echo __(\'Newest Comment\'); ?>',
    "<%template_san>" => '<?php echo \Fc2blog\Lib\WordTag::replace(__(\'End Of Name\')); ?>',
    "<%template_month_archive>" => '<?php echo __(\'Monthly Archive\'); ?>',
    "<%template_noplugin>" => '<?php echo __(\'By adding plug-ins you can add more features and functions.\'); ?>',
    "<%template_goto_preventry>" => '<?php echo __(\'Prev Entry\'); ?>',
    "<%template_goto_nextentry>" => '<?php echo __(\'Next Entry\'); ?>',
    "<%template_secret>" => '<?php echo __(\'Private comment\'); ?>',
    "<%template_css_text>" => '<?php if(isset($css_link)) echo file_get_contents(\Fc2blog\App::WWW_DIR . substr($css_link, 1)); ?>',
    "<%template_list_of_articles>" => '<?php echo __(\'List of articles\'); ?>',
    "<%template_list_view>" => '<?php echo __(\'List view\'); ?>',
    "<%template_grid_view>" => '<?php echo __(\'Grid view\'); ?>',

    // HTML変換 （テンプレートファイル互換性のため、ゆらぎ対応は無し）
    'name="mode" value="regist"' => 'name="process" value="comment_regist"',
    'name="mode" value="edit"' => 'name="process" value="comment_edit"',
];

$config['fc2_template_var_search'] = $config['fc2_template_var_replace'] = [];
foreach ($template_vars as $key => $value) {
    $config['fc2_template_var_search'][] = $key;
    $config['fc2_template_var_replace'][] = $value;
}

/**
 * ページング用のPHPコードを取得する
 * @param int $page_num
 * @return string
 */
function getFc2PagingPHP(int $page_num): string
{
    $html =
        '<?php ' . PHP_EOL .
        'if(!empty($paging)){' . PHP_EOL .
        '  for ($i=max(0, $paging[\'page\']-' . $page_num . ');$i<$paging[\'page\'];$i++){ ' . PHP_EOL .
        '    echo \'<a href="\' . \Fc2blog\Web\Html::url($request, array(\'page\'=>$i, \'blog_id\'=>$blog_id), true) . \'">\' . ($i+1) . \'</a>\'; ' . PHP_EOL .
        '  }' . PHP_EOL .
        '  ?><strong><?php echo $paging[\'page\']+1; ?></strong><?php' . PHP_EOL .
        '  for ($i=$paging[\'page\']+1;$i<$paging[\'max_page\'] && $i<$paging[\'page\']+1+' . $page_num . ';$i++) {' . PHP_EOL .
        '    echo \'<a href="\' . \Fc2blog\Web\Html::url($request, array(\'page\'=>$i, \'blog_id\'=>$blog_id), true) . \'">\' . ($i+1) . \'</a>\'; ' . PHP_EOL .
        '  }' . PHP_EOL .
        '}' . PHP_EOL .
        '?>' . PHP_EOL;
    return $html;
}

function getTopentryDiscription(): string
{
    return <<<PHP
  <?php
    if (isset(\$entry['body'])) {
      if (!\$self_blog && \$entry['open_status']==\Fc2blog\Config::get('ENTRY.OPEN_STATUS.PASSWORD') && !\Fc2blog\Web\Session::get('entry_password.' . \$entry['blog_id'] . '.' . \$entry['id'])) {
        \$__str__1 = __("This contents is password protected.<br>You need a input password to view this.");
        \$__str__2 = __("Password");
        \$__str__3 = __("Submit");
        echo <<<HTML
        <form method="POST">
          <input type="hidden" name="mode" value="Entries" />
          <input type="hidden" name="process" value="password" />
          <input type="hidden" name="id" value="{\$entry['id']}" />
          <p>
          {\$__str__1}
          </p>
          <p>{\$__str__2} <input type="password" name="password" /><input type="submit" value="{\$__str__3}" /></p>
        </form>
        HTML;
        unset(\$__str__1,\$__str__2,\$__str__3);
      } else {
        echo th(strip_tags(\$entry['body']), 200);
      }
    }
  ?>
  PHP;
}

return $config;
