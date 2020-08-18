@charset "utf-8";

/*============================================================
  -Index-
  Reset
  Basic
  Header
    Header Menu List
    Blog Introduction
  Main Contents
    Layout
    Page title
    Entry List
    Entry (個別記事表示)
    Comment List, Trackback List
    Comment Form
    Plugin Contents
    Contents Footer Links
    Profile (index mode)
  Ad
  Pager
  Page Navigation
  Blog Footer
============================================================*/


/*============================================================
  Reset
============================================================*/
html,body,div, dl,dt,dd,ul,ol,li,h1,h2,h3,h4,h5,h6,pre,form,fieldset,input,textarea,blockquote,th,td,p{margin:0;padding:0;}
ul, ol, li, dl, dt, dd{list-style:none;}
img {border:none;vertical-align:middle;}

/*============================================================
  Basic
============================================================*/
body {
  margin: 0;
  padding: 0;
  background-color:#E0E0E0;
  font-family: Helvetica, "Hiragino Kaku Gothic ProN", "ヒラギノ角ゴ ProN W3";
  font-size:16px;
  color:#666666;
  -webkit-text-size-adjust: none;
}
a {
  text-decoration:none;
  -webkit-tap-highlight-color:rgba(255,255,255,0.5);
}
a:link,a:visited { color:#4073D4; }
a:focus,a:hover,a:active { color:#0044B2; }
h1{font-size:20px;}
h2{font-size:18px;}
h3{font-size:17px;}
h4,h5,h6{font-size:16px;}
input,textarea,select { font-size:16px; }
.system_message { padding:10px;color:#FF0000; }

/*============================================================
  Header
============================================================*/
#header {
  position:relative;
  min-height: 44px;
  margin:0 0 15px;
  padding: 0 50px;
  background-color:#000000;
  color:#FFFFFF;
  text-align: center;
}
#header h1 {
  overflow: hidden;
  width: 85%;
  margin:0 auto;
  padding: 10px 0;
  font-size:20px;
  text-overflow: ellipsis;
  white-space: nowrap;
}
#header h1 a {
  color:#FFFFFF;
}

/* PC SKIN Change
---------------------------------------------- */
#header .browser_change {
  position:absolute;
  top:6px;
  right:5px;
  width: 50px;
}
#header .browser_change a {
  display:block;
  height:22px;
  padding-top:8px;
  border-width:1px;
  border-style:solid;
  border-color:#DFDFDF #6F6F6F #3F3F3F;
  -webkit-border-radius:5px;
  -moz-border-radius:5px;
  border-radius:5px;
  background: url(https://blog-imgs-42.fc2.com/t/e/m/templates/basic_black_b_bg_001.png) repeat-x left center;
  background-image: -webkit-gradient(linear, left top, left bottom,
    from(#AFAFAF),
    color-stop(0.4,#333333),
    to(#2F2F2F));
  background-image:
     -moz-linear-gradient(top,
          #AFAFAF,
          #333333 40%,
          #2F2F2F
     );
  background-image:
     -o-linear-gradient(top,
          #AFAFAF,
          #333333 40%,
          #2F2F2F
     );
  color: #FFFFFF;
  font-size:12px;
  text-align:center;
  text-overflow: ellipsis;
  -webkit-tap-highlight-color:rgba(255,255,255,0.5);
}

/* Header Menu Button
---------------------------------------------- */
#header #header_menu {
  position:absolute;
  top:6px;
  left:5px;
  width: 40px;
  border-width:1px;
  border-style:solid;
  border-color:#DFDFDF #6F6F6F #3F3F3F;
  -webkit-border-radius:5px;
  -moz-border-radius:5px;
  border-radius:5px;
  background: url(https://blog-imgs-42.fc2.com/t/e/m/templates/basic_black_b_bg_001.png) repeat-x left center;
  background-image: -webkit-gradient(linear, left top, left bottom,
  from(#AFAFAF),
  color-stop(0.4,#333333),
  to(#2F2F2F));
  background-image:
  -moz-linear-gradient(top,
    #AFAFAF,
    #333333 40%,
    #2F2F2F
  );
  background-image:
  -o-linear-gradient(top,
    #AFAFAF,
    #333333 40%,
    #2F2F2F
  );
  color: #FFFFFF;
  cursor:pointer;
}
#header #header_menu span {
  display:block;
  height:30px;
  background:url(https://templates.blog.fc2.com/template/sphone/basic_black/dropmenu.png) no-repeat center 12px;
  -webkit-tap-highlight-color:rgba(255,255,255,0.5);
}
#header #header_menu.selected {
  border:none;
  background-image:
    -webkit-gradient(linear, left top, left bottom,
      from(#2F2F2F),
      to(#505050)
    );
  background-image:
     -moz-linear-gradient(top,
      #2F2F2F,
      #505050
     );
  background-image:
     -o-linear-gradient(top,
      #2F2F2F,
      #505050
     );
  -webkit-box-shadow: 1px 1px 1px #DADADA;
  -moz-box-shadow: 1px 1px 1px #DADADA;
  box-shadow: 1px 1px 1px #DADADA;
}
#header #header_menu.selected span {
  background:url(https://templates.blog.fc2.com/template/sphone/basic_black/dropmenu.png) no-repeat center -12px;
}

/*============================================================
  Header Menu List
============================================================*/
#plugin_menu {
  position:absolute;
  top:45px;
  width:100%;
  background-color:#000000;
  z-index:999;
}
#plugin_menu .blogmenu {
  overflow:hidden;
}
#plugin_menu .blogmenu li {
  position:relative;
  margin:-1px 0 0;
  border-top: 1px solid #000000;
  background-image:
  -webkit-gradient(linear, left top, left bottom,
    from(#1F1F1F),
    to(#2A2A2A)
  );
  background-image:
  -moz-linear-gradient(top,
    #1F1F1F,
    #2A2A2A
 );
 background-image:
 -o-linear-gradient(top,
    #1F1F1F,
    #2A2A2A
 );
}
#plugin_menu .blogmenu li a {
  overflow: hidden;
  display: block;
  padding:10px 30px 10px 10px;
  color:#FFFFFF;
  background: url(https://templates.blog.fc2.com/template/sphone/basic_black/chevron.png) no-repeat right center;
}
#plugin_menu .blogmenu li a:focus,
#plugin_menu .blogmenu li a:hover,
#plugin_menu .blogmenu li a:active {
  background-color:#3F3F3F;
}
#plugin_menu .blogmenu li a span {
  position:absolute;
  top:6px;
  right:5px;
  color:#FFFFFF;
  font-family: AppleGothic,sans-serif;
}

/*============================================================
  Blog Introduction (Delete)
============================================================*/
#blog_intro {
  margin:0 2% 15px;
  padding:15px 10px;
  -webkit-border-radius:5px;
  background-color:#FFFFFF;
  -webkit-box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
  -moz-box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
  box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
  font-size:13px;
}

/*============================================================
  Layout
============================================================*/
#main_contents {
  margin:0 2%;
}
#main_contents .section {
  margin:0 0 20px;
  background-color:#FFFFFF;
  -webkit-border-radius:5px;
  -moz-border-radius:5px;
  border-radius:5px;
  -webkit-box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
  -moz-box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
  box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
}

/*============================================================
  Page title
============================================================*/
/* 記事の見出し */
.section .entry_title {
  margin: 0 10px;
    padding: 10px 0;
  border-bottom:2px solid #F2F2F2;
}
/* 記事以外の見出し */
.section .page_title {
    padding:15px 10px;
  border-bottom: 1px solid #F2F2F2;
}
/* 見出しの文字色 */
.section .page_title h2,
.section entry_title h2,
.section .page_title h2 a,
.section .entry_title h2 a {
  color:#000000;
}
/* カテゴリ一覧見出し */
.category_title {
  margin:0 0 15px;
  padding:10px;
  background-color:#FFFFFF;
  text-align:center;
  -webkit-border-radius:5px;
  -moz-border-radius:5px;
  border-radius:5px;
  -webkit-box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
  -moz-box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
  box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
}

/*============================================================
  Entry List (個別記事ページ以外の一覧表示)
============================================================*/
#entry_list {
  overflow:hidden;
  margin:0 0 20px;
}
#entry_list li {
  margin:-1px 0 0;
  border-top:1px solid #F2F2F2;
}
#entry_list li .entry {
  display:block;
  padding:10px 30px 10px 10px;
  background: url(https://templates.blog.fc2.com/template/sphone/basic_black/chevron.png) no-repeat right center;
  text-overflow: ellipsis;
}
#entry_list li .entry strong {
  overflow:hidden;
  display:block;
  font-size:18px;
  text-overflow: ellipsis;
  white-space: nowrap;
}
#entry_list li .entry .photo_true{
  background: url(https://templates.blog.fc2.com/template/sphone/basic_black/img.gif) no-repeat right center;
}
#entry_list li .entry .posted {
  padding-right:10px;
}
#entry_list li .entry .posted,
#entry_list li .entry .res {
  font-size:12px;
  color:#808080;
}

/*============================================================
  Entry (個別記事表示)
============================================================*/
/* 記事投稿情報
---------------------------------------------- */
.section .posted {
  padding-top:5px;
  font-size:12px;
  color:#808080;
}

/* 記事本文・追記
---------------------------------------------- */
.section .entry_body,
.section .entry_more {
  padding:15px 10px;
  line-height:1.6;
  word-wrap: break-word;
}

.entry_body img[src^="https://blog-imgs-"],
.entry_more img[src^="https://blog-imgs-"] {
     max-width:100%;
     height:auto;
}
.section .readmore {
  background-color:#808080;
  
}
.section .readmore a {
  display:block;
  padding:10px 30px 10px 10px;
  color:#FFFFFF;
  background: url(https://templates.blog.fc2.com/template/sphone/basic_black/chevron_w.png) no-repeat right center;
}

/* トラバリンク（個別記事表示）
---------------------------------------------- */
.section #tb_url {
  margin:0 10px 10px;;
  padding:5px;
  background-color:#EFEFEF;
}
.section #tb_url p {
  font-size:12px;
}

/*============================================================
  Comment List, Trackback List
============================================================*/
#comment .list dt,
#trackback .list dt {
  padding:10px 10px 0;
  color:#000000;
  font-weight:bold;
}
#comment .list dd,
#trackback .list dd {
  padding:10px;
  border-bottom:1px solid #F2F2F2;
}
#comment .list dd .posted,
#trackback .list dd .posted {
  margin:3px 0;
  text-align:right;
  font-size:13px;
}

/*============================================================
  Comment Form
============================================================*/
#comment_post .confirm {
  padding:10px;
}

#comment_post .form {
  background: #fff;
  padding: 0;
  margin:10px;
}
#comment_post .form dd{
  margin:3px 0 10px;
}

ul.confirm li{
  padding:8px 3px;
}

#comment_post .form dt{
  color:#336699;
  margin:10px 0 0;
  font-weight:bold;
}
#comment_post .form dd{
  margin:3px 0 0;
}
#comment_post .form dd p{
  margin:5px 0;
}
#comment_post input[type="text"],
#comment_post input[type="email"],
#comment_post input[type="url"],
#comment_post input[type="password"],
#comment_post textarea,
#comment_post select {
  width: 100%;
  padding:5px 0;
  border:1px solid #DFDFDF;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  -webkit-appearance: textarea;
}

#comment_post textarea {
  height: 120px;
  padding: 0;
}

#comment_post select {
  background: url(https://templates.blog.fc2.com/template/sphone/basic_black/chevron.png) no-repeat right center;
  -webkit-appearance: textfield;
}

/* Submit button
---------------------------------------------- */
#comment_post .submit_btn {
  padding:10px;
  text-align:center;
}
#comment_post .submit_btn input[type="submit"],
#comment_post .submit_btn a {
  display:block;
  width:60%;
  margin:0 auto 10px;
  padding:10px;
  border:1px solid #000000;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  background: url(https://blog-imgs-42.fc2.com/t/e/m/templates/basic_black_b_bg_001.png) repeat-x left center;
  background-image: -webkit-gradient(linear, left top, left bottom,
    from(#AFAFAF),
    color-stop(0.5,#1F1F1F),
    to(#1A1A1A)
  );
  background-image:
  -moz-linear-gradient(top,
    #AFAFAF,
    #1F1F1F 50%,
    #1A1A1A
  );
  background-image:
  -o-linear-gradient(top,
    #AFAFAF,
    #1F1F1F 50%,
    #1A1A1A
  );
  -webkit-box-shadow: 1px 1px 1px #ffffff;
  -moz-box-shadow: 1px 1px 1px #ffffff;
  box-shadow: 1px 1px 1px #ffffff;
  color:#FFFFFF;
  -webkit-tap-highlight-color:rgba(255,255,255,0.5);
  text-overflow: ellipsis;
}

/*============================================================
  Plugin Contents
============================================================*/
/* Plugin Commons
---------------------------------------------- */
.section .plugin_profile,
.section .plugin_freearea{ padding:10px; }

/* Profile
---------------------------------------------- */
dl.profile {
  background: #fff;
  padding: 0;
}

dl.profile dt {
  color:#336699;
  list-style-type: none;
}

dl.profile dd {
  list-style-type: none;
  margin:0 0 20px;
}

/* new_entry,new_comment,new_tb
---------------------------------------------- */
.plugin_body .plugin_list {
  color: black;
  background: #fff;
  padding: 0;
  margin:0;
  -webkit-border-bottom-right-radius: 5px;
  -moz-border-bottom-right-radius: 5px;
  border-bottom-right-radius: 5px;
  -webkit-border-bottom-left-radius: 5px;
  -moz-border-bottom-left-radius: 5px;
  border-bottom-left-radius: 5px;
}
.plugin_body .plugin_list li {
  border-top:1px solid #F2F2F2;
  background: url(https://templates.blog.fc2.com/template/sphone/basic_black/chevron.png) no-repeat right center;
}
.plugin_body .plugin_list li a{
  display: block;
  overflow: hidden;
  padding:10px 30px 10px 10px;
}
.plugin_body .plugin_list li em {
  padding:0 0 3px;
  font-style: normal;
}
.plugin_body .plugin_list li span{
  color:#808080;
  font-size:13px;
}

/*============================================================
  Contents Footer Links
============================================================*/
/* トップ一覧表示
---------------------------------------------- */
.section .response {
  overflow:hidden;
  width:100%;
  background-color:#000000;
  -webkit-border-bottom-right-radius: 5px;
  -moz-border-bottom-right-radius: 5px;
  border-bottom-right-radius: 5px;
  -webkit-border-bottom-left-radius: 5px;
  -moz-border-bottom-left-radius: 5px;
  border-bottom-left-radius: 5px;
  font-size:13px;
}
.section .response li a {
  display:block;
  padding:10px;
  color:#FFFFFF;
  -webkit-tap-highlight-color:rgba(255,255,255,0.5);
}
.section .response li a:focus,
.section .response li a:hover,
.section .response li a:active {
  background-color:#0044B2;
}
.section .response .show_com {
  float:left;
  width:50%;
}
.section .response .show_tb {
  float:right;
  width:50%;
  text-align:right;
}

/* トップ一覧表示以外
---------------------------------------------- */
.section .contents_footer,
.section .form_footer {
  overflow:hidden;
  width:100%;
  background-color:#000000;
  -webkit-border-bottom-right-radius: 5px;
  -moz-border-bottom-right-radius: 5px;
  border-bottom-right-radius: 5px;
  -webkit-border-bottom-left-radius: 5px;
  -moz-border-bottom-left-radius: 5px;
  border-bottom-left-radius: 5px;
}
.section .contents_footer li,
.section .form_footer li {
  margin:-1px 0 0;
  border-top:1px solid #808080;
}
.section .contents_footer li a,
.section .form_footer li a {
  display:block;
  padding:10px;
  color:#FFFFFF;
  -webkit-tap-highlight-color:rgba(255,255,255,0.5);
}
.section .contents_footer li a {
  padding-right:30px;
  background: url(https://templates.blog.fc2.com/template/sphone/basic_black/chevron.png) no-repeat right center;
}
.section .form_footer li a {
  text-align:center;
}
.section .contents_footer li a:focus,
.section .contents_footer li a:hover,
.section .contents_footer li a:active,
.section .form_footer li a:focus,
.section .form_footer li a:hover,
.section .form_footer li a:active {
  background-color:#0044B2;
}

/* コメント・トラバ非表示の場合（共通）
---------------------------------------------- */
.section .response li span,
.section .contents_footer li span {
  display:block;
  padding:10px;
  color:#666666;
}

/*============================================================
  Profile (index mode)
============================================================*/
.profile_area {
  overflow:hidden;
  margin:0 2% 15px;
  background-color:#FFFFFF;
  -webkit-border-radius:5px;
  -moz-border-radius:5px;
  border-radius:5px;
  -webkit-box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
  -moz-box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
  box-shadow: 2px 2px 3px rgba(0,0,0,0.3);
}
.profile_area dt {
    margin:10px 10px 0;
    padding:0 0 5px;
  border-bottom:2px solid #F2F2F2;
}
.profile_area dd {
    overflow: hidden;
    padding:10px;
    font-size:13px;
}
.profile_area dd .prof_image {
  float:left;
  margin-right:5px;
}

/*============================================================
  Ad
============================================================*/
.ad_header{
  text-align:center;
  margin:10px 0;
}
.ad_footer{
  text-align:center;
  margin:10px 0;
}

/*============================================================
  Pager
============================================================*/
#main_contents .pager {
  position:relative;
  overflow:hidden; 
  margin:0 0 10px;
  padding:11px 0;
  min-height: 30px;
  text-align:center;
}
#main_contents .pager .prevpage {
  position:absolute;
  top:0;
  left:0;
  padding:8px 10px;
}
#main_contents .pager .nextpage {
  position:absolute;
  top:0;
  right:0;
  padding:8px 10px;
}
#main_contents .pager a {
  margin:0 1px;
  padding:10px 15px;
  border:1px solid #000000;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  background: url(https://blog-imgs-42.fc2.com/t/e/m/templates/basic_black_b_bg_001.png) repeat-x left center;
  background-image: -webkit-gradient(linear, left top, left bottom,
    from(#AFAFAF),
    color-stop(0.5,#1F1F1F),
    to(#1A1A1A));
  background-image:
     -moz-linear-gradient(top,
          #AFAFAF,
          #1F1F1F 50%,
          #1A1A1A
     );
  background-image:
     -o-linear-gradient(top,
          #AFAFAF,
          #1F1F1F 50%,
          #1A1A1A
     );
  -webkit-box-shadow: 1px 1px 1px #ffffff;
  -moz-box-shadow: 1px 1px 1px #ffffff;
  box-shadow: 1px 1px 1px #ffffff;
  color:#FFFFFF;
}
#main_contents .pager a:focus,
#main_contents .pager a:hover,
#main_contents .pager a:active {
  background:#0044B2 none;
}
#main_contents .pager strong {
  margin:0 1px;
  padding:10px 15px;
  border:1px solid #000000;
  -webkit-border-radius: 5px;
  -moz-border-radius: 5px;
  border-radius: 5px;
  background-color:#000000;
  -webkit-box-shadow: 1px 1px 1px #ffffff;
  -moz-box-shadow: 1px 1px 1px #ffffff;
  box-shadow: 1px 1px 1px #ffffff;
  color:#FFFFFF;
}

/*============================================================
  Page Navigation
============================================================*/
#page_navi {
  overflow:hidden;
  padding:0 2% 10px;    /* marginにするとbox-shadowがでません */
}
#page_navi li {
  width:48%;
  border: 1px solid #ffffff;
  -webkit-border-radius:5px;
  -moz-border-radius:5px;
  border-radius:5px;
  background: url(https://blog-imgs-42.fc2.com/t/e/m/templates/basic_b_bg_000.png) repeat-x left center;
  background-image: -webkit-gradient(linear, left top, left bottom,
    from(#FFFFFF),
    color-stop(0.49,#FEFEFE),
    color-stop(0.5,#EAEAEA),
    to(#F9F9F9)
  );
  background-image:
     -moz-linear-gradient(top,
          #FFFFFF,
          #FEFEFE 49%,
          #EAEAEA 50%,
          #F9F9F9
     );
  background-image:
     -o-linear-gradient(top,
          #FFFFFF,
          #FEFEFE 49%,
          #EAEAEA 50%,
          #F9F9F9
     );
  -webkit-box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
  -moz-box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
  box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
}
#page_navi .goto_home { float:left; }
#page_navi .page_top { float:right; }
#page_navi li a {
  display:block;
  padding:10px;
  text-align:center;
  color:#505050;
}

/*============================================================
  Blog Footer
============================================================*/
#footer{
  text-align:center;
}
#footer .footer_menu a{
  margin:0 10px;
}
#footer .footer_menu{
  margin:10px 0;
}
#footer .copyright {
  margin:0 0 10px;
  font-size:12px;
}
#footer .powered {
  padding:10px 5px;
  background-color:#000000;
  text-align:center;
  color:#FFFFFF;
}
#footer .powered a {
  text-decoration:underline;
}

