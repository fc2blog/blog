<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo __('en'); ?>" lang="<?php echo __('en'); ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo __('utf-8'); ?>" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="author" content="<?php if(isset($blog['nickname'])) echo h($blog['nickname']); ?>" />
<meta name="description" content="<?php if(isset($blog['introduction'])) echo h($blog['introduction']); ?>" />
<title><?php if(empty($index_area)) { ?><?php if(isset($sub_title)) echo h($sub_title); ?> - <?php } ?><?php if(isset($blog['name'])) echo h($blog['name']); ?></title>
<link rel="icon" href="https://static.fc2.com/share/image/favicon.ico">
<link rel="stylesheet" type="text/css" href="<?php if(isset($css_link)) echo $css_link; ?>" media="all" />
<link rel="alternate" type="application/rss+xml" href="<?php if(isset($url)) echo $url; ?>?xml" title="RSS" />
<link rel="top" href="<?php if(isset($url)) echo $url; ?>" title="Top" />
<link rel="index" href="<?php if(isset($url)) echo $url; ?>?all" title="<?php echo __('Index'); ?>" />
<?php if(!empty($paging) && $paging['is_prev']) { ?><link rel="prev" href="<?php if(!empty($paging) && $paging['is_prev']) echo \Fc2blog\Web\Html::url($request, array('page'=>$paging['page']-1, 'blog_id'=>$blog_id), true); ?>" title="<?php echo __('Previous page'); ?>" /><?php } ?>
<?php if(!empty($paging) && $paging['is_next']) { ?><link rel="next" href="<?php if(!empty($paging) && $paging['is_next']) echo \Fc2blog\Web\Html::url($request, array('page'=>$paging['page']+1, 'blog_id'=>$blog_id), true); ?>" title="<?php echo __('Next page'); ?>" /><?php } ?>
<?php if(!empty($prev_entry)) { ?><link rel="next" href="<?php if(!empty($prev_entry)) echo \Fc2blog\App::userURL($request,array('id'=>$prev_entry['id'], 'blog_id'=>$blog_id)); ?>" title="<?php if(!empty($prev_entry)) echo h($prev_entry['title']); ?>" /><?php } ?>
<?php if(!empty($next_entry)) { ?><link rel="prev" href="<?php if(!empty($next_entry)) echo \Fc2blog\App::userURL($request,array('id'=>$next_entry['id'], 'blog_id'=>$blog_id)); ?>" title="<?php if(!empty($next_entry)) echo h($next_entry['title']); ?>" /><?php } ?>
<script type="text/javascript" src="https://static.fc2.com/js/lib/jquery.js"></script>
<script type="text/javascript">
 
 jQuery.noConflict();
 jQuery(function(){
   
   // デフォルトで表示するレイアウト  glid or list
   var DEFAULT_LAYOUT    = "glid";
   
   // 左カラム(記事)のID
   var LEFT_COLUMN_ID    = "#main_contents";
   
   // 右カラム(メニュー)のID
   var RIGHT_COLUMN_ID   = "#sidemenu";
   
   // クッキーのキー名
   var COOKIE_KEY_NAME   = "template_blog_fc2";
   
   // クッキーのオプション
   // var COOKIE_OPTION  = { expire: 30, domain: "myblog.blog.fc2.com", path: "/" };
   var COOKIE_OPTION = {
     expires: 30,
     sameSite: 'Lax'
   };

   /** フッタープラグイン配置 **/
   jQuery( "#footer_plg .plg" ).equalbox();
   
   /** トップへ移動 **/
   jQuery( "#toTop" ).hide();
   jQuery( "#pagetop" ).click(function() {
     jQuery("body, html").animate({scrollTop: 0}, 800);
     return false;
   });
   
   // レイアウト切り替えフラグ 連続でレイアウト切り替えを行わせないためのもの
   var layoutFlag = true;
   
   /** 表示切替 **/
   // list表示
   function showListLayout() {
   
     if ( !layoutFlag ) return;
     
     jQuery( LEFT_COLUMN_ID ).css( "height", "" );
     jQuery( RIGHT_COLUMN_ID ).css( "height", "" );
     
     var t = setTimeout(function(){
     
       layoutFlag = false;
       
       jQuery( LEFT_COLUMN_ID )
         .css( "opacity", "0" )
         .show()
         .fadeTo( "slow", 1, function(){ layoutFlag = true; } );
       
       jQuery( ".switch .list a" ).addClass( "selected" );
       jQuery( ".entry" ).addClass( "list_content" );
       
       jQuery( ".switch .grid a" ).removeClass( "selected" );
       jQuery( ".entry" ).removeClass( "grid_content" );
     
       Cookies.set( COOKIE_KEY_NAME, "list", COOKIE_OPTION );
       
       equalizeBoxHeight();
       
       clearTimeout(t);
     }, 100);
     
     return false;
   };
   
   // glid表示
   function showGridLayout() {
     
     if (!layoutFlag) return;
     
     jQuery( LEFT_COLUMN_ID ).css( "height", "" );
     jQuery( RIGHT_COLUMN_ID ).css( "height", "" );
     
     var t = setTimeout(function(){
     
       layoutFlag = false;
       
       jQuery( LEFT_COLUMN_ID )
         .css( "opacity", "0" )
         .show()
         .fadeTo( "slow", 1, function(){ layoutFlag = true; } );
       
       jQuery( ".switch .grid a" ).addClass( "selected" );
       jQuery( ".entry" ).addClass( "grid_content" );
       
       jQuery( ".switch .list a" ).removeClass( "selected" );
       jQuery( ".entry" ).removeClass( "list_content" );

       Cookies.set( COOKIE_KEY_NAME, "glid", COOKIE_OPTION );
       
       equalizeBoxHeight();
       
       clearTimeout(t);
     }, 100);
     
     return false;
   };
   
   jQuery( ".switch .list" ).click( showListLayout );
   jQuery( ".switch .grid" ).click( showGridLayout );
   
   // 左カラムと右カラムの高さを合わせる
   function equalizeBoxHeight() {
     var leftHeight  = jQuery( LEFT_COLUMN_ID ).height();
     var rightHeight = jQuery( RIGHT_COLUMN_ID ).height();
     
     var height = (leftHeight > rightHeight)? leftHeight: rightHeight;
     
     jQuery( LEFT_COLUMN_ID ).height(height + "px");
     jQuery( RIGHT_COLUMN_ID ).height(height + "px");
   };
   
   function initialize() {
     var layout = Cookies.get( COOKIE_KEY_NAME );
     if ( !checkCookieValue( layout ) ) {
       layout = DEFAULT_LAYOUT;
     }
     
     if ( "list" == layout ) {
       showListLayout();
     } else if ( "glid" == layout ) {
       showGridLayout();
     } else {
       showGridLayout();
     }
   };
   
   var layoutList = ["glid", "list"];
   function checkCookieValue(str) {
     if ("string" == typeof str) {
       for (var i in layoutList) {
         if (layoutList[i] == str) return true;
       }
     };
     return false;
   };
   
   initialize();
 });
</script>
</head>
<body>
<div id="container">
  <div id="header">
    <h1><a href="<?php if(isset($url)) echo $url; ?>" accesskey="0" title="<?php if(isset($blog['name'])) echo h($blog['name']); ?>"><?php if(isset($blog['name'])) echo h($blog['name']); ?></a></h1>
    <p><?php if(isset($blog['introduction'])) echo h($blog['introduction']); ?></p>
  </div><!-- /header -->
  <div id="headermenu">
    <p class="archives"><a href="<?php if(isset($url)) echo $url; ?>archives.html">記事一覧</a></p>
    <?php if(empty($titlelist_area)) { ?>
    <?php if(empty($search_area)) { ?>
    <?php if(empty($permanent_area)) { ?>
    <ul class="switch">
      <li class="list"><a href="#" title="リスト表示">リスト表示</a></li>
      <li class="grid"><a href="#" title="グリッド表示">グリッド表示</a></li>
    </ul>
    <?php } ?>
    <?php } ?>
    <?php } ?>
  </div>
  <div id="wrap">
    <div id="main">
      <div id="main_contents" style="display: none">
        <?php if(empty($titlelist_area)) { ?>
        <?php if(empty($search_area)) { ?>
        <?php if(!empty($entries) && empty($titlelist_area)) foreach($entries as $entry) { ?>
        <div class="content entry grid_content<?php if(!empty($permanent_area)) { ?> p_area<?php } ?><?php if(empty($permanent_area)) { ?> no_br<?php } ?>" id="e<?php if(isset($entry['id'])) echo $entry['id']; ?>">
          <h2 class="entry_header"><?php if(empty($permanent_area)) { ?><a href="<?php if(isset($entry['link'])) echo $entry['link']; ?>" title="<?php echo __('Entry Absolute link'); ?>"><?php } ?><?php if(isset($entry['title'])) echo $entry['title']; ?><?php if(empty($permanent_area)) { ?></a><?php } ?></h2>
          <ul class="entry_date">
            <li><?php if(isset($entry['year'])) echo $entry['year']; ?>/<?php if(isset($entry['month'])) echo $entry['month']; ?>/<?php if(isset($entry['day'])) echo $entry['day']; ?></li>
            <li><?php if(isset($entry['hour'])) echo $entry['hour']; ?>:<?php if(isset($entry['minute'])) echo $entry['minute']; ?></li>
          </ul>
          <?php if(empty($permanent_area)) { ?>
          <ul class="entry_state">
            <?php if(isset($entry['comment_accepted']) && $entry['comment_accepted']==\Fc2blog\Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED')) { ?>
            <li><a href="<?php if(isset($entry['link'])) echo $entry['link']; ?>#cm" title="<?php echo __('Post a comment'); ?>">CM:<?php if(isset($entry['comment_count'])) echo $entry['comment_count']; ?></a></li>
            <?php } ?>
            <?php if(isset($entry['comment_accepted']) && $entry['comment_accepted']==\Fc2blog\Config::get('ENTRY.COMMENT_ACCEPTED.REJECT')) { ?><?php } ?>
          </ul>
          <div class="entry_body">
            <?php if(!empty($entry['first_image'])) { ?>
            <div class="entry_image"><?php if(!empty($entry['first_image'])) echo '<img src="' . $entry['first_image'] . '" />'; ?></div>
            <div class="entry_discription"><?php
  if (isset($entry['body'])) {
    if (!$self_blog && $entry['open_status']==\Fc2blog\Config::get('ENTRY.OPEN_STATUS.PASSWORD') && !\Fc2blog\Web\Session::get('entry_password.' . $entry['blog_id'] . '.' . $entry['id'])) {
      echo <<<HTML
      <form method="POST">
        <input type="hidden" name="mode" value="Entries" />
        <input type="hidden" name="process" value="password" />
        <input type="hidden" name="id" value="{$entry['id']}" />
        <p>
          このコンテンツはパスワードで保護されています。<br />
          閲覧するには以下にパスワードを入力してください。
        </p>
        <p>パスワード <input type="password" name="password" /><input type="submit" value="送信" /></p>
      </form>
      HTML;
    } else {
      echo th(strip_tags($entry['body']), 200);
    }
  }
?></div>
            <?php } ?>
            <?php if(empty($entry['first_image'])) { ?><?php
  if (isset($entry['body'])) {
    if (!$self_blog && $entry['open_status']==\Fc2blog\Config::get('ENTRY.OPEN_STATUS.PASSWORD') && !\Fc2blog\Web\Session::get('entry_password.' . $entry['blog_id'] . '.' . $entry['id'])) {
      echo <<<HTML
      <form method="POST">
        <input type="hidden" name="mode" value="Entries" />
        <input type="hidden" name="process" value="password" />
        <input type="hidden" name="id" value="{$entry['id']}" />
        <p>
          このコンテンツはパスワードで保護されています。<br />
          閲覧するには以下にパスワードを入力してください。
        </p>
        <p>パスワード <input type="password" name="password" /><input type="submit" value="送信" /></p>
      </form>
      HTML;
    } else {
      echo th(strip_tags($entry['body']), 200);
    }
  }
?><?php } ?>
            <p class="entry_more"><a href="<?php if(isset($entry['link'])) echo $entry['link']; ?>" title="<?php echo __('Entry Absolute link'); ?>"><?php echo __('Read more'); ?></a></p>
          </div>
          <?php } ?>
          <?php if(!empty($permanent_area)) { ?>
          <div class="entry_body">
            <?php
  if (isset($entry['body'])) {
    if (!$self_blog && $entry['open_status']==\Fc2blog\Config::get('ENTRY.OPEN_STATUS.PASSWORD') && !\Fc2blog\Web\Session::get('entry_password.' . $entry['blog_id'] . '.' . $entry['id'])) {
      echo <<<HTML
      <form method="POST">
        <input type="hidden" name="mode" value="Entries" />
        <input type="hidden" name="process" value="password" />
        <input type="hidden" name="id" value="{$entry['id']}" />
        <p>
          このコンテンツはパスワードで保護されています。<br />
          閲覧するには以下にパスワードを入力してください。
        </p>
        <p>パスワード <input type="password" name="password" /><input type="submit" value="送信" /></p>
      </form>
      HTML;
    } else {
      echo $entry['body'];
    }
  }
?>
            <?php if(!empty($comment_area) && !empty($entry['extend'])) { ?><div class="more"><?php if(!empty($entry['extend']) && ($entry['open_status']!=\Fc2blog\Config::get('ENTRY.OPEN_STATUS.PASSWORD') || \Fc2blog\Web\Session::get('entry_password.' . $entry['blog_id'] . '.' . $entry['id']))) echo $entry['extend']; ?></div><?php } ?>
          </div>
          <div class="entry_footer">
            <ul class="entry_state">
              <?php if(false) { ?>
              <li><?php echo __('Theme'); ?>:<a href="" title=""></a></li>
              <li><?php echo __('Genre'); ?>:<a href="" title=""></a></li>
              <?php } ?>
              <li><a href="<?php if(!isset($entry['categories'][0]['id'])){}else if(!empty($category) && $category['entry_id']==$entry['categories'][0]['entry_id']){ echo \Fc2blog\Web\Html::url($request, array('action'=>'category', 'blog_id'=>$entry['blog_id'], 'cat'=>$category['id']));}else{ echo \Fc2blog\Web\Html::url($request, array('action'=>'category', 'blog_id'=>$entry['blog_id'], 'cat'=>$entry['categories'][0]['id']));} ?>" title="<?php echo __('View category list'); ?>">カテゴリ:<?php if(!isset($entry['categories'][0]['name'])){}else if(!empty($category) && $category['entry_id']==$entry['categories'][0]['entry_id']){ echo h($category['name']);}else{echo h($entry['categories'][0]['name']);} ?></a></li>
              <?php if(isset($entry['comment_accepted']) && $entry['comment_accepted']==\Fc2blog\Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED')) { ?>
              <li><a href="<?php if(isset($entry['link'])) echo $entry['link']; ?>#cm" title="<?php echo __('Post a comment'); ?>">CM:<?php if(isset($entry['comment_count'])) echo $entry['comment_count']; ?></a></li>
              <?php } ?>
              <?php if(isset($entry['comment_accepted']) && $entry['comment_accepted']==\Fc2blog\Config::get('ENTRY.COMMENT_ACCEPTED.REJECT')) { ?><?php } ?>
            </ul>
          </div>
          <?php } ?>
        </div>
        <?php } ?>
        <?php } ?>
        <?php } ?>

        <?php if(!empty($titlelist_area)) { ?>
        <div class="content" id="titlelist">
          <h2 class="sub_header"><?php echo __('Index'); ?></h2>
          <ul class="list_body">
            <?php if(!empty($entries) && !empty($titlelist_area)) foreach($entries as $entry) { ?>
            <li><?php if(isset($entry['year'])) echo $entry['year']; ?>/<?php if(isset($entry['month'])) echo $entry['month']; ?>/<?php if(isset($entry['day'])) echo $entry['day']; ?>：<a href="<?php if(isset($entry['link'])) echo $entry['link']; ?>" title="<?php if(isset($entry['body'])) echo th($entry['body'], 20); ?>"><?php if(isset($entry['title'])) echo $entry['title']; ?></a>：<a href="<?php if(!isset($entry['categories'][0]['id'])){}else if(!empty($category) && $category['entry_id']==$entry['categories'][0]['entry_id']){ echo \Fc2blog\Web\Html::url($request, array('action'=>'category', 'blog_id'=>$entry['blog_id'], 'cat'=>$category['id']));}else{ echo \Fc2blog\Web\Html::url($request, array('action'=>'category', 'blog_id'=>$entry['blog_id'], 'cat'=>$entry['categories'][0]['id']));} ?>" title="<?php echo __('View category list'); ?>"><?php if(!isset($entry['categories'][0]['name'])){}else if(!empty($category) && $category['entry_id']==$entry['categories'][0]['entry_id']){ echo h($category['name']);}else{echo h($entry['categories'][0]['name']);} ?></a></li>
            <?php } ?>
          </ul>
        </div><!--/titlelist-->
        <?php } ?>

        <?php if(!empty($search_area)) { ?>
        <div class="content" id="search">
          <h2 class="sub_header"><?php echo __('Search entries'); ?> : <?php if(isset($sub_title)) echo h($sub_title); ?></h2>
          <ul class="list_body">
            <?php if(!empty($entries) && empty($titlelist_area)) foreach($entries as $entry) { ?><li><?php if(isset($entry['year'])) echo $entry['year']; ?>/<?php if(isset($entry['month'])) echo $entry['month']; ?>/<?php if(isset($entry['day'])) echo $entry['day']; ?>(<?php if(isset($entry['hour'])) echo $entry['hour']; ?>:<?php if(isset($entry['minute'])) echo $entry['minute']; ?>) ： <a href="<?php if(!isset($entry['categories'][0]['id'])){}else if(!empty($category) && $category['entry_id']==$entry['categories'][0]['entry_id']){ echo \Fc2blog\Web\Html::url($request, array('action'=>'category', 'blog_id'=>$entry['blog_id'], 'cat'=>$category['id']));}else{ echo \Fc2blog\Web\Html::url($request, array('action'=>'category', 'blog_id'=>$entry['blog_id'], 'cat'=>$entry['categories'][0]['id']));} ?>" title="<?php echo __('View category list'); ?>"><?php if(!isset($entry['categories'][0]['name'])){}else if(!empty($category) && $category['entry_id']==$entry['categories'][0]['entry_id']){ echo h($category['name']);}else{echo h($entry['categories'][0]['name']);} ?></a> ： <a href="<?php if(isset($entry['link'])) echo $entry['link']; ?>" title="<?php
  if (isset($entry['body'])) {
    if (!$self_blog && $entry['open_status']==\Fc2blog\Config::get('ENTRY.OPEN_STATUS.PASSWORD') && !\Fc2blog\Web\Session::get('entry_password.' . $entry['blog_id'] . '.' . $entry['id'])) {
      echo <<<HTML
      <form method="POST">
        <input type="hidden" name="mode" value="Entries" />
        <input type="hidden" name="process" value="password" />
        <input type="hidden" name="id" value="{$entry['id']}" />
        <p>
          このコンテンツはパスワードで保護されています。<br />
          閲覧するには以下にパスワードを入力してください。
        </p>
        <p>パスワード <input type="password" name="password" /><input type="submit" value="送信" /></p>
      </form>
      HTML;
    } else {
      echo th(strip_tags($entry['body']), 200);
    }
  }
?>"><?php if(isset($entry['title'])) echo $entry['title']; ?></a></li><?php } ?>
          </ul>
        </div><!--/search-->
        <?php } ?>

        <?php if(!empty($permanent_area)) { ?>
        <div class="page_navi">
          <?php if(!empty($prev_entry)) { ?><a href="<?php if(!empty($prev_entry)) echo \Fc2blog\App::userURL($request,array('id'=>$prev_entry['id'], 'blog_id'=>$blog_id)); ?>" title="<?php if(!empty($prev_entry)) echo h($prev_entry['title']); ?>" class="prev preventry"><?php if(!empty($prev_entry)) echo h($prev_entry['title']); ?></a><?php } ?>
          <a href="<?php if(isset($url)) echo $url; ?>" title="<?php echo __('Home'); ?>" class="home"><?php echo __('Home'); ?></a>
          <?php if(!empty($next_entry)) { ?><a href="<?php if(!empty($next_entry)) echo \Fc2blog\App::userURL($request,array('id'=>$next_entry['id'], 'blog_id'=>$blog_id)); ?>" title="<?php if(!empty($next_entry)) echo h($next_entry['title']); ?>" class="next nextentry"><?php if(!empty($next_entry)) echo h($next_entry['title']); ?></a><?php } ?>
        </div><!--/page_navi-->
        <?php } ?>

        <?php if(!empty($comment_area) && isset($entry['comment_accepted']) && $entry['comment_accepted']==\Fc2blog\Config::get('ENTRY.COMMENT_ACCEPTED.ACCEPTED')) { ?><?php if (!empty($comment_error)) echo $comment_error; ?>  
        <div id="cm" class="content">
          <h3 class="sub_header"><?php echo __('Comments'); ?></h3>
          <?php if(!empty($comments)) foreach($comments as $comment) { ?>
          <div class="sub_content" id="comment<?php if(isset($comment['id'])) echo $comment['id']; ?>">
            <h4 class="sub_title"><?php if(isset($comment['title'])) echo h($comment['title']); ?></h4>
            <div class="sub_body"><?php if(isset($comment['body'])) echo nl2br(h($comment['body'])); ?></div>
            <ul class="sub_footer">
              <li><?php if(isset($comment['year'])) echo $comment['year']; ?>/<?php if(isset($comment['month'])) echo $comment['month']; ?>/<?php if(isset($comment['day'])) echo $comment['day']; ?>(<?php if(isset($comment['hour'])) echo $comment['hour']; ?>:<?php if(isset($comment['minute'])) echo $comment['minute']; ?>)</li>
              <li><?php if(!isset($comment['name'])){}else if(!empty($comment['mail'])){ echo '<a href="mailto:' . $comment['mail'] . '">' . h($comment['name']) . '</a>'; }else{ echo h($comment['name']); } ?> <?php if(isset($comment['url'])) echo '<a href="' . $comment['url'] . '">' . $comment['url'] . '</a>'; ?></li>
              <li><a href="<?php if(isset($comment['edit_link'])) echo $comment['edit_link']; ?>" title="<?php echo __('Edit a comment'); ?>"><?php echo __('Edit'); ?></a></li>
            </ul>
            <?php if(!empty($comment['reply_body'])) { ?>
              <div style="background-color: #ffffdd; border: 1px solid #d7d7d7; padding: 5px; margin: 5px 0 0 5px;">
                <?php if(isset($comment['reply_body'])) echo $comment['reply_body']; ?>
                <ul class="sub_footer">
                  <li><?php if(isset($comment['reply_year'])) echo $comment['reply_year']; ?>/<?php if(isset($comment['reply_month'])) echo $comment['reply_month']; ?>/<?php if(isset($comment['reply_day'])) echo $comment['reply_day']; ?>(<?php if(isset($comment['reply_hour'])) echo $comment['reply_hour']; ?>:<?php if(isset($comment['reply_minute'])) echo $comment['reply_minute']; ?>)</li>
                </ul>
              </div>
            <?php } ?>
          </div>
          <?php } ?>
          <div class="form">
            <h4 class="sub_title"><?php echo __('Post a comment'); ?></h4>
            <form action="" method="post" name="comment_form" id="comment_form">
              <dl>
                <dt>
                  <input type="hidden" name="process" value="comment_regist" />
                  <input type="hidden" name="comment[no]" value="<?php if(isset($entry['id'])) echo $entry['id']; ?>" />
                  <label for="name"><?php echo __('Name'); ?></label>
                </dt>
                <dd><input id="name" type="text" name="comment[name]" size="30" value="<?php if(empty($comment_error) && \Fc2blog\Web\Cookie::get('comment_name')) echo \Fc2blog\Web\Cookie::get('comment_name'); ?>" /></dd>
                <dt><label for="subject"><?php echo __('Title'); ?></label></dt>
                <dd><input id="subject" name="comment[title]" type="text" size="30" value="No title" onblur="if(this.value == '') this.value='No title';" onfocus="if(this.value == 'No title') this.value='';" /></dd>
                <dt><label for="mail"><?php echo __('E-mail address'); ?></label></dt>
                <dd><input id="mail" type="text" name="comment[mail]" size="30" value="<?php if(empty($comment_error) && \Fc2blog\Web\Cookie::get('comment_mail')) echo \Fc2blog\Web\Cookie::get('comment_mail'); ?>" /></dd>
                <dt><label for="url">URL</label></dt>
                <dd><input id="url" type="text" name="comment[url]" size="30" value="<?php if(empty($comment_error) && \Fc2blog\Web\Cookie::get('comment_url')) echo \Fc2blog\Web\Cookie::get('comment_url'); ?>" /></dd>
                <dt><label for="comment"><?php echo __('Body'); ?></label></dt>
                <dd><script type="text/javascript" src="<?php echo '/js/template_comment.js' ?>"></script></dd>
                <dd><textarea id="comment" cols="50" rows="5" name="comment[body]"></textarea></dd>
                <dt><label for="pass"><?php echo __('Edit password'); ?></label></dt>
                <dd><input id="pass" type="password" name="comment[pass]" size="20" /></dd>
                <dt><?php echo __('Private message'); ?></dt>
                <dd><input id="himitu" type="checkbox" name="comment[himitu]" /><label for="himitu"><?php echo __('Only the blog author may view the message.'); ?></label></dd>
              </dl>
              <p class="form_btn"><input type="submit" value="<?php echo __('Send'); ?>" /></p>
            </form>
          </div><!--/form-->
        </div><!--/cm-->
        <?php } ?>

        <?php if(!empty($edit_area)) { ?><?php if (!empty($comment_error)) echo $comment_error; ?>
        <div class="content" id="edit">
          <h3 class="sub_header"><?php echo __('Edit a comment'); ?></h3>
          <div class="form">
            <form action="../../config" method="post" name="comment_form" id="comment_form">
              <dl>
                <dt>
                  <input type="hidden" name="process" value="comment_edit" />
                  <input type="hidden" name="mode2" value="edited" />
                  <input type="hidden" name="edit[rno]" value="<?php if(isset($edit_comment['id'])) echo $edit_comment['id']; ?>" />
                  <label for="name"><?php echo __('Name'); ?></label>
                </dt>
                <dd><input id="edit[name]" type="text" name="edit[name]" size="30" value="<?php if(isset($edit_comment['name'])) echo $edit_comment['name']; ?>" /></dd>
                <dt><label for="subject"><?php echo __('Title'); ?></label></dt>
                <dd><input id="subject" type="text" name="edit[title]" size="30" value="<?php if(isset($edit_comment['title'])) echo $edit_comment['title']; ?>" /></dd>
                <dt><label for="mail"><?php echo __('E-mail address'); ?></label></dt>
                <dd><input id="mail" type="text" name="edit[mail]" size="30" value="<?php if(isset($edit_comment['mail'])) echo $edit_comment['mail']; ?>" /></dd>
                <dt><label for="url">URL</label></dt>
                <dd><input id="url" type="text" name="edit[url]" size="30" value="<?php if(isset($edit_comment['url'])) echo $edit_comment['url']; ?>" /></dd>
                <dt><label for="comment"><?php echo __('Body'); ?></label></dt>
                <dd><script type="text/javascript" src="<?php echo '/js/template_comment.js' ?>"></script></dd>
                <dd><textarea id="comment" cols="50" rows="5" name="edit[body]"><?php if(isset($edit_comment['body'])) echo $edit_comment['body']; ?></textarea></dd>
                <dt><label for="pass"><?php echo __('Edit password'); ?></label></dt>
                <dd><input id="pass" type="password" name="edit[pass]" size="20" /></dd>
                <dt><?php echo __('Private message'); ?></dt>
                <dd><input id="himitu" type="checkbox" name="edit[himitu]" /><label for="himitu"><?php echo __('Only the blog author may view the message.'); ?></label></dd>
              </dl>
              <p class="form_btn"><input type="submit" value="<?php echo __('Send'); ?>" /><input type="submit" name="edit[delete]" value="<?php echo __('Delete'); ?>" /></p>
            </form>
          </div><!--/form-->
        </div><!--/edit-->
        <?php } ?>

        <?php if(empty($permanent_area)) { ?>
        <div class="page_navi">
          <?php if(!empty($paging) && $paging['is_prev']) { ?><a href="<?php if(!empty($paging) && $paging['is_prev']) echo \Fc2blog\Web\Html::url($request, array('page'=>$paging['page']-1, 'blog_id'=>$blog_id), true); ?>" title="<?php echo __('Previous page'); ?>" class="prev prevpage"><?php echo __('Previous page'); ?></a><?php } ?>
          <a href="<?php if(isset($url)) echo $url; ?>" title="<?php echo __('Home'); ?>" class="home"><?php echo __('Home'); ?></a>
          <?php if(!empty($paging) && $paging['is_next']) { ?><a href="<?php if(!empty($paging) && $paging['is_next']) echo \Fc2blog\Web\Html::url($request, array('page'=>$paging['page']+1, 'blog_id'=>$blog_id), true); ?>" title="<?php echo __('Next page'); ?>" class="next nextpage"><?php echo __('Next page'); ?></a><?php } ?>
        </div><!--/page_navi-->
        <?php } ?>

        <?php if(true) { ?>
        <?php if(!isset($t_plugins_3)) $t_plugins_3=\Fc2blog\Model\Model::load('BlogPlugins')->findByDeviceTypeAndCategory(\Fc2blog\App::getDeviceType($request), \Fc2blog\Config::get('BLOG_PLUGIN.CATEGORY.THIRD'), $blog_id); ?><?php if (!empty($t_plugins_3)) foreach($t_plugins_3 as $t_plugin) { ?>
        <div class="content plg">
          <h3 class="plg_header" style="text-align:<?php if(isset($t_plugin['title_align'])) echo $t_plugin['title_align']; ?>; color:<?php if(isset($t_plugin['title_color'])) echo $t_plugin['title_color']; ?>"><?php if(isset($t_plugin['title'])) echo $t_plugin['title']; ?></h3>
          <!--plugin_third_description--><div class="plg_description" style="text-align:"></div><!--/plugin_third_description-->
          <div class="plg_body" style="text-align:<?php if(isset($t_plugin['contents_align'])) echo $t_plugin['contents_align']; ?>; color:<?php if(isset($t_plugin['contents_color'])) echo $t_plugin['contents_color']; ?>"><?php if(isset($t_plugin['id'])) include(\Fc2blog\App::getPluginFilePath($blog_id, $t_plugin['id'])); ?></div>
          <!--plugin_third_description2--><div class="plg_footer" style="text-align:"></div><!--/plugin_third_description2-->
        </div>
        <?php } ?>
        <?php } ?>
      </div><!--/main_contents-->
    </div><!--/main-->

    <div id="sidemenu">
      <?php if(true) { ?>
      <?php if(!isset($t_plugins_1)) $t_plugins_1=\Fc2blog\Model\Model::load('BlogPlugins')->findByDeviceTypeAndCategory(\Fc2blog\App::getDeviceType($request), \Fc2blog\Config::get('BLOG_PLUGIN.CATEGORY.FIRST'), $blog_id); ?><?php if (!empty($t_plugins_1)) foreach($t_plugins_1 as $t_plugin) { ?>
      <div class="sidemenu_content plg">
        <h3 class="plg_header" style="text-align:<?php if(isset($t_plugin['title_align'])) echo $t_plugin['title_align']; ?>; color:<?php if(isset($t_plugin['title_color'])) echo $t_plugin['title_color']; ?>"><?php if(isset($t_plugin['title'])) echo $t_plugin['title']; ?></h3>
        <!--plugin_first_description--><div class="plg_description" style="text-align:"></div><!--/plugin_first_description-->
        <div class="plg_body" style="text-align:<?php if(isset($t_plugin['contents_align'])) echo $t_plugin['contents_align']; ?>; color:<?php if(isset($t_plugin['contents_color'])) echo $t_plugin['contents_color']; ?>"><?php if(isset($t_plugin['id'])) include(\Fc2blog\App::getPluginFilePath($blog_id, $t_plugin['id'])); ?></div>
        <!--plugin_first_description2--><div class="plg_footer" style="text-align:"></div><!--/plugin_first_description2-->
      </div>
      <?php } ?>
      <?php } ?>
    </div><!--/sidemenu-->

    <div id="pagetop"><a href="#container" title="<?php echo __('Top of page'); ?>"><?php echo __('Top of page'); ?></a></div>
  </div><!--/wrap-->
  <div id="footer">
    <?php if(true) { ?>
    <div id="footer_plg">
      <?php if(!isset($t_plugins_2)) $t_plugins_2=\Fc2blog\Model\Model::load('BlogPlugins')->findByDeviceTypeAndCategory(\Fc2blog\App::getDeviceType($request), \Fc2blog\Config::get('BLOG_PLUGIN.CATEGORY.SECOND'), $blog_id); ?><?php if (!empty($t_plugins_2)) foreach($t_plugins_2 as $t_plugin) { ?>
      <div class="footer_content plg">
        <h3 class="plg_header" style="text-align:<?php if(isset($t_plugin['title_align'])) echo $t_plugin['title_align']; ?>; color:<?php if(isset($t_plugin['title_color'])) echo $t_plugin['title_color']; ?>"><?php if(isset($t_plugin['title'])) echo $t_plugin['title']; ?></h3>
        <!--plugin_second_description--><div class="plg_description" style="text-align:"></div><!--/plugin_second_description-->
        <div class="plg_body" style="text-align:<?php if(isset($t_plugin['contents_align'])) echo $t_plugin['contents_align']; ?>; color:<?php if(isset($t_plugin['contents_color'])) echo $t_plugin['contents_color']; ?>"><?php if(isset($t_plugin['id'])) include(\Fc2blog\App::getPluginFilePath($blog_id, $t_plugin['id'])); ?></div>
        <!--plugin_second_description2--><div class="plg_footer" style="text-align:"></div><!--/plugin_second_description2-->
      </div>
      <?php } ?>
    </div>
    <?php } ?>
    <div id="footer_inner">
      <p class="powered">Powered by <a href="http://blog.fc2.com"><?php echo __('FC2 BLOG'); ?></a></p>
      <!--Don't delete--><p class="ad"> </p>
      <p class="copyright">Copyright &copy; <?php if(isset($blog['name'])) echo h($blog['name']); ?> All Rights Reserved.</p>
    </div><!-- /footer_inner -->
  </div><!--/footer-->
</div><!--/container-->
<script type="text/javascript" src="https://static.fc2.com/share/blog_template/equalbox.js"></script>
<script type="text/javascript" src="/js/js.cookie.js"></script>
</body>
</html>
