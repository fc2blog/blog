<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<%template_language>" lang="<%template_language>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<%template_charset>" />
<meta http-equiv="Content-Style-Type" content="text/css" />
<meta http-equiv="Content-Script-Type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="author" content="<%author_name>" />
<meta name="description" content="<%introduction>" />
<title><!--not_index_area--><%sub_title> - <!--/not_index_area--><%blog_name></title>
<link rel="icon" href="https://static.fc2.com/share/image/favicon.ico">
<link rel="stylesheet" type="text/css" href="<%css_link>" media="all" />
<link rel="alternate" type="application/rss+xml" href="<%url>?xml" title="RSS" />
<link rel="top" href="<%url>" title="Top" />
<link rel="index" href="<%url>?all" title="<%template_index>" />
<!--prevpage--><link rel="prev" href="<%prevpage_url>" title="<%template_prevpage>" /><!--/prevpage-->
<!--nextpage--><link rel="next" href="<%nextpage_url>" title="<%template_nextpage>" /><!--/nextpage-->
<!--preventry--><link rel="next" href="<%preventry_url>" title="<%preventry_title>" /><!--/preventry-->
<!--nextentry--><link rel="prev" href="<%nextentry_url>" title="<%nextentry_title>" /><!--/nextentry-->
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
    <h1><a href="<%url>" accesskey="0" title="<%blog_name>"><%blog_name></a></h1>
    <p><%introduction></p>
  </div><!-- /header -->
  <div id="headermenu">
    <p class="archives"><a href="<%url>archives.html">記事一覧</a></p>
    <!--not_titlelist_area-->
    <!--not_search_area-->
    <!--not_permanent_area-->
    <ul class="switch">
      <li class="list"><a href="#" title="リスト表示">リスト表示</a></li>
      <li class="grid"><a href="#" title="グリッド表示">グリッド表示</a></li>
    </ul>
    <!--/not_permanent_area-->
    <!--/not_titlelist_area-->
    <!--/not_search_area-->
  </div>
  <div id="wrap">
    <div id="main">
      <div id="main_contents" style="display: none">
        <!--not_titlelist_area-->
        <!--not_search_area-->
        <!--topentry-->
        <div class="content entry grid_content<!--permanent_area--> p_area<!--/permanent_area--><!--not_permanent_area--> no_br<!--/not_permanent_area-->" id="e<%topentry_no>">
          <h2 class="entry_header"><!--not_permanent_area--><a href="<%topentry_link>" title="<%template_abs_link>"><!--/not_permanent_area--><%topentry_title><!--not_permanent_area--></a><!--/not_permanent_area--></h2>
          <ul class="entry_date">
            <li><%topentry_year>/<%topentry_month>/<%topentry_day></li>
            <li><%topentry_hour>:<%topentry_minute></li>
          </ul>
          <!--not_permanent_area-->
          <ul class="entry_state">
            <!--allow_comment-->
            <li><a href="<%topentry_link>#cm" title="<%template_post_comment>">CM:<%topentry_comment_num></a></li>
            <!--/allow_comment-->
            <!--deny_comment--><!--/deny_comment-->
          </ul>
          <div class="entry_body">
            <!--body_img-->
            <div class="entry_image"><%topentry_image></div>
            <div class="entry_discription"><%topentry_discription></div>
            <!--/body_img-->
            <!--body_img_none--><%topentry_discription><!--/body_img_none-->
            <p class="entry_more"><a href="<%topentry_link>" title="<%template_abs_link>"><%template_extend></a></p>
          </div>
          <!--/not_permanent_area-->
          <!--permanent_area-->
          <div class="entry_body">
            <%topentry_body>
            <!--more--><div class="more"><%topentry_more></div><!--/more-->
          </div>
          <div class="entry_footer">
            <ul class="entry_state">
              <!--community-->
              <li><%template_theme>:<a href="<%topentry_thread_link>" title="<%topentry_thread_title>"><%topentry_thread_title></a></li>
              <li><%template_genre>:<a href="<%topentry_community_janrelink>" title="<%topentry_community_janrename>"><%topentry_community_janrename></a></li>
              <!--/community-->
              <li><a href="<%topentry_category_link>" title="<%template_view_category>">カテゴリ:<%topentry_category></a></li>
              <!--allow_comment-->
              <li><a href="<%topentry_link>#cm" title="<%template_post_comment>">CM:<%topentry_comment_num></a></li>
              <!--/allow_comment-->
              <!--deny_comment--><!--/deny_comment-->
            </ul>
          </div>
          <!--/permanent_area-->
        </div>
        <!--/topentry-->
        <!--/not_titlelist_area-->
        <!--/not_search_area-->

        <!--titlelist_area-->
        <div class="content" id="titlelist">
          <h2 class="sub_header"><%template_index></h2>
          <ul class="list_body">
            <!--titlelist-->
            <li><%titlelist_year>/<%titlelist_month>/<%titlelist_day>：<a href="<%titlelist_url>" title="<%titlelist_body>"><%titlelist_title></a>：<a href="<%titlelist_category_url>" title="<%template_view_category>"><%titlelist_category></a></li>
            <!--/titlelist-->
          </ul>
        </div><!--/titlelist-->
        <!--/titlelist_area-->

        <!--search_area-->
        <div class="content" id="search">
          <h2 class="sub_header"><%template_search_entry> : <%sub_title></h2>
          <ul class="list_body">
            <!--topentry--><li><%topentry_year>/<%topentry_month>/<%topentry_day>(<%topentry_hour>:<%topentry_minute>) ： <a href="<%topentry_category_link>" title="<%template_view_category>"><%topentry_category></a> ： <a href="<%topentry_link>" title="<%topentry_discription>"><%topentry_title></a></li><!--/topentry-->
          </ul>
        </div><!--/search-->
        <!--/search_area-->

        <!--permanent_area-->
        <div class="page_navi">
          <!--preventry--><a href="<%preventry_url>" title="<%preventry_title>" class="prev preventry"><%preventry_title></a><!--/preventry-->
          <a href="<%url>" title="<%template_home>" class="home"><%template_home></a>
          <!--nextentry--><a href="<%nextentry_url>" title="<%nextentry_title>" class="next nextentry"><%nextentry_title></a><!--/nextentry-->
        </div><!--/page_navi-->
        <!--/permanent_area-->

        <!--comment_area-->  
        <div id="cm" class="content">
          <h3 class="sub_header"><%template_comment></h3>
          <!--comment-->
          <div class="sub_content" id="comment<%comment_no>">
            <h4 class="sub_title"><%comment_title></h4>
            <div class="sub_body"><%comment_body></div>
            <ul class="sub_footer">
              <li><%comment_year>/<%comment_month>/<%comment_day>(<%comment_hour>:<%comment_minute>)</li>
              <li><%comment_mail+name> <%comment_url+str></li>
              <li><a href="<%comment_edit_link>" title="<%template_edit_comment>"><%template_edit></a></li>
            </ul>
            <!--comment_reply-->
              <div style="background-color: #ffffdd; border: 1px solid #d7d7d7; padding: 5px; margin: 5px 0 0 5px;">
                <%comment_reply_body>
                <ul class="sub_footer">
                  <li><%comment_reply_year>/<%comment_reply_month>/<%comment_reply_day>(<%comment_reply_hour>:<%comment_reply_minute>)</li>
                </ul>
              </div>
            <!--/comment_reply-->
          </div>
          <!--/comment-->
          <div class="form">
            <h4 class="sub_title"><%template_post_comment></h4>
            <form action="../../config" method="post" name="comment_form" id="comment_form">
              <dl>
                <dt>
                  <input type="hidden" name="mode" value="regist" />
                  <input type="hidden" name="comment[no]" value="<%pno>" />
                  <label for="name"><%template_name></label>
                </dt>
                <dd><input id="name" type="text" name="comment[name]" size="30" value="<%cookie_name>" /></dd>
                <dt><label for="subject"><%template_title></label></dt>
                <dd><input id="subject" name="comment[title]" type="text" size="30" value="No title" onblur="if(this.value == '') this.value='No title';" onfocus="if(this.value == 'No title') this.value='';" /></dd>
                <dt><label for="mail"><%template_address></label></dt>
                <dd><input id="mail" type="text" name="comment[mail]" size="30" value="<%cookie_mail>" /></dd>
                <dt><label for="url">URL</label></dt>
                <dd><input id="url" type="text" name="comment[url]" size="30" value="<%cookie_url>" /></dd>
                <dt><label for="comment"><%template_body></label></dt>
                <dd><script type="text/javascript" src="<%template_comment_js>"></script></dd>
                <dd><textarea id="comment" cols="50" rows="5" name="comment[body]"></textarea></dd>
                <dt><label for="pass"><%template_password></label></dt>
                <dd><input id="pass" type="password" name="comment[pass]" size="20" /></dd>
                <dt><%template_private></dt>
                <dd><input id="himitu" type="checkbox" name="comment[himitu]" /><label for="himitu"><%template_private_check></label></dd>
              </dl>
              <p class="form_btn"><input type="submit" value="<%template_send>" /></p>
            </form>
          </div><!--/form-->
        </div><!--/cm-->
        <!--/comment_area-->

        <!--edit_area-->
        <div class="content" id="edit">
          <h3 class="sub_header"><%template_edit_comment></h3>
          <div class="form">
            <form action="../../config" method="post" name="comment_form" id="comment_form">
              <dl>
                <dt>
                  <input type="hidden" name="mode" value="edit" />
                  <input type="hidden" name="mode2" value="edited" />
                  <input type="hidden" name="edit[rno]" value="<%eno>" />
                  <label for="name"><%template_name></label>
                </dt>
                <dd><input id="edit[name]" type="text" name="edit[name]" size="30" value="<%edit_name>" /></dd>
                <dt><label for="subject"><%template_title></label></dt>
                <dd><input id="subject" type="text" name="edit[title]" size="30" value="<%edit_title>" /></dd>
                <dt><label for="mail"><%template_address></label></dt>
                <dd><input id="mail" type="text" name="edit[mail]" size="30" value="<%edit_mail>" /></dd>
                <dt><label for="url">URL</label></dt>
                <dd><input id="url" type="text" name="edit[url]" size="30" value="<%edit_url>" /></dd>
                <dt><label for="comment"><%template_body></label></dt>
                <dd><script type="text/javascript" src="<%template_comment_js>"></script></dd>
                <dd><textarea id="comment" cols="50" rows="5" name="edit[body]"><%edit_body></textarea></dd>
                <dt><label for="pass"><%template_password></label></dt>
                <dd><input id="pass" type="password" name="edit[pass]" size="20" /></dd>
                <dt><%template_private></dt>
                <dd><input id="himitu" type="checkbox" name="edit[himitu]" /><label for="himitu"><%template_private_check></label></dd>
              </dl>
              <p class="form_btn"><input type="submit" value="<%template_send>" /><input type="submit" name="edit[delete]" value="<%template_delete>" /></p>
            </form>
          </div><!--/form-->
        </div><!--/edit-->
        <!--/edit_area-->

        <!--not_permanent_area-->
        <div class="page_navi">
          <!--prevpage--><a href="<%prevpage_url>" title="<%template_prevpage>" class="prev prevpage"><%template_prevpage></a><!--/prevpage-->
          <a href="<%url>" title="<%template_home>" class="home"><%template_home></a>
          <!--nextpage--><a href="<%nextpage_url>" title="<%template_nextpage>" class="next nextpage"><%template_nextpage></a><!--/nextpage-->
        </div><!--/page_navi-->
        <!--/not_permanent_area-->

        <!--plugin-->
        <!--plugin_third-->
        <div class="content plg">
          <h3 class="plg_header" style="text-align:<%plugin_third_talign>; color:<%plugin_third_tcolor>"><%plugin_third_title></h3>
          <!--plugin_third_description--><div class="plg_description" style="text-align:<%plugin_third_ialign>"><%plugin_third_description></div><!--/plugin_third_description-->
          <div class="plg_body" style="text-align:<%plugin_third_align>; color:<%plugin_third_color>"><%plugin_third_content></div>
          <!--plugin_third_description2--><div class="plg_footer" style="text-align:<%plugin_third_ialign>"><%plugin_third_description2></div><!--/plugin_third_description2-->
        </div>
        <!--/plugin_third-->
        <!--/plugin-->
      </div><!--/main_contents-->
    </div><!--/main-->

    <div id="sidemenu">
      <!--plugin-->
      <!--plugin_first-->
      <div class="sidemenu_content plg">
        <h3 class="plg_header" style="text-align:<%plugin_first_talign>; color:<%plugin_first_tcolor>"><%plugin_first_title></h3>
        <!--plugin_first_description--><div class="plg_description" style="text-align:<%plugin_first_ialign>"><%plugin_first_description></div><!--/plugin_first_description-->
        <div class="plg_body" style="text-align:<%plugin_first_align>; color:<%plugin_first_color>"><%plugin_first_content></div>
        <!--plugin_first_description2--><div class="plg_footer" style="text-align:<%plugin_first_ialign>"><%plugin_first_description2></div><!--/plugin_first_description2-->
      </div>
      <!--/plugin_first-->
      <!--/plugin-->
    </div><!--/sidemenu-->

    <div id="pagetop"><a href="#container" title="<%template_go_top>"><%template_go_top></a></div>
  </div><!--/wrap-->
  <div id="footer">
    <!--plugin-->
    <div id="footer_plg">
      <!--plugin_second-->
      <div class="footer_content plg">
        <h3 class="plg_header" style="text-align:<%plugin_second_talign>; color:<%plugin_second_tcolor>"><%plugin_second_title></h3>
        <!--plugin_second_description--><div class="plg_description" style="text-align:<%plugin_second_ialign>"><%plugin_second_description></div><!--/plugin_second_description-->
        <div class="plg_body" style="text-align:<%plugin_second_align>; color:<%plugin_second_color>"><%plugin_second_content></div>
        <!--plugin_second_description2--><div class="plg_footer" style="text-align:<%plugin_second_ialign>"><%plugin_second_description2></div><!--/plugin_second_description2-->
      </div>
      <!--/plugin_second-->
    </div>
    <!--/plugin-->
    <div id="footer_inner">
      <p class="powered">Powered by <a href="http://blog.fc2.com"><%template_fc2blog></a></p>
      <!--Don't delete--><p class="ad"><%ad> <%ad2></p>
      <p class="copyright">Copyright &copy; <%blog_name> All Rights Reserved.</p>
    </div><!-- /footer_inner -->
  </div><!--/footer-->
</div><!--/container-->
<script type="text/javascript" src="https://static.fc2.com/share/blog_template/equalbox.js"></script>
<script type="text/javascript" src="/js/js.cookie.js"></script>
</body>
</html>
