<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no," />
  <!--ios-->
  <meta name="format-detection" content="telephone=no" />
  <!--/ios-->
  <meta name="description" content="<%introduction>" />
  <title><%sub_title> - <%blog_name></title>
  <link rel="stylesheet" href="<%css_link>" />
  <link rel="apple-touch-icon" href="http://static.fc2.com/image/iphone/apple-touch-icon.png" />
  <script type="text/javascript" src="http://static.fc2.com/js/lib/jquery.js"></script>
  <script type="text/javascript" src="http://static.fc2.com/js/blog/iphone_resize.js"></script>
  <script type="text/javascript" src="http://static.fc2.com/js/blog/sp_acc_menu.js"></script>
  <script type="text/javascript" charset="utf-8">
    <!--android-->
    window.onresize = function(){
      setOrientation();
    }
    <!--/android-->

    window.onload = function() {
      setTimeout(function(){if(window.pageYOffset === 0){window.scrollTo(0, 1);}}, 100);
      setTimeout(function(){setOrientation();},300);
    }
    function submit(){
      document.form1.submit();
    }
  </script>
</head>
<body>

  <div id="header">
    <h1><a href="<%url>"><%blog_name></a></h1>
    <p class="browser_change"><a href="<%url>?pc" title="PCページを表示">PC</a></p>
    <div id="header_menu"><span></span></div>
  </div>
  <div id="ad_header" class="ad_header"><%ad></div>

  <!--spplugin-->

  <!--ヘッダーメニュー-->
  <div id="plugin_menu" class="section" style="display:none;">
    <ul class="blogmenu">
    <!--プラグインメニュー--><!--spplugin_first-->
      <li>
        <a href="<%url>?mp=<%spplugin_first_no><%tail_url>"><%spplugin_first_title></a>
      </li>
    <!--/spplugin_first-->
    </ul>
  </div>
  <!--/spplugin-->

  <div id="main_contents">
    <!--category_area--><!--カテゴリ一覧ページ-->
      <h2 class="category_title"><%sub_title></h2>
    <!--/category_area-->
    
    <!--not_permanent_area-->
    <!--記事詳細ページ以外-->
      <!--not_spplugin_area-->
      <!--not_comment_area-->
      <!--not_form_area-->
      <div class="section">
        <ul id="entry_list">
          <!--topentry-->
          <li>
            <a href="<%topentry_link><%tail_url>" class="entry">
              <strong<!--body_img--> class="photo_true"<!--/body_img-->><%topentry_title></strong>
              <span class="posted">
                <%topentry_year>/<%topentry_month>/<%topentry_day> <%topentry_hour>:<%topentry_minute>
              </span>
              <span class="res">
                <!--allow_comment-->コメント(<%topentry_comment_num>)<!--/allow_comment-->
              </span>
            </a>
          </li>
          <!--/topentry-->
        </ul>
      </div>
      <!--/not_form_area-->
      <!--/not_comment_area-->
      <!--/not_spplugin_area-->

<!--not_comment_area-->
      <!--page_area-->
        <div class="pager">
          <!--firstpage_disp--><a href="<%firstpage_url><%tail_url>" class="prevpage"><%firstpage_num></a>...<!--/firstpage_disp-->
          <%template_pager1>
          <!--lastpage_disp-->...<a href="<%lastpage_url><%tail_url>" class="nextpage"><%lastpage_num></a><!--/lastpage_disp-->
        </div>
      <!--/page_area-->
<!--/not_comment_area-->
    <!--/not_permanent_area-->

    <!--spplugin_area-->
    <!--プラグイン表示エリア-->
      <!--spplugin-->
      <div id="plugin" class="section">
        <div class="page_title">
          <h2 style="text-align:<%spplugin_talign>; color:<%spplugin_tcolor>;"><%spplugin_title></h2>
        </div>
        <div class="plugin_body" style="text-align:<%spplugin_align>; color:<%spplugin_color>;">
          <%spplugin_content>
        </div>
      </div>
      <!--/spplugin-->
    <!--/プラグイン表示エリア-->
    <!--/spplugin_area-->

    <!--permanent_area-->
    <!--エントリー個別ページ-->
      <!--topentry-->
      <div id="entry" class="section">
        <div class="entry_title">
          <h2><%topentry_title></h2>
          <p class="posted">
            <%topentry_year>年<%topentry_month>月<%topentry_day>日<%topentry_hour>:<%topentry_minute>&nbsp;
            <a href="<%topentry_category_link>"><%topentry_category></a>
            <!--body_img-->
            <a href="<%topentry_link>&photo=true<%tail_url>"><img src="http://templates.blog.fc2.com/template/sphone/basic_black/img.gif" alt="写真あり" width="18" height="14" /></a>
            <!--/body_img-->
          </p>
        </div>
        <div class="entry_body">
          <%topentry_body>
        </div>
    <!--more-->
        <div class="entry_more" id="more<%topentry_no>">
          <%topentry_more>
        </div>
    <!--/more-->
        <ul class="contents_footer">
        <!--allow_comment-->
          <li>
            <a href="<%topentry_link>&m2=form<%tail_url>">コメントを書く</a>
          </li>
          <li>
            <a href="<%topentry_link>&m2=res<%tail_url>">コメント(<%topentry_comment_num>)</a>
          </li>
        <!--/allow_comment-->
        <!--deny_comment-->
          <li>
            <span>コメントクローズ中</span>
          </li>
        <!--/deny_comment-->
        </ul>
      </div>
      <div class="pager">
        <!--preventry--><a href="<%preventry_url><%tail_url>" class="prevpage">前の記事</a><!--/preventry-->
        <!--nextentry--><a href="<%nextentry_url><%tail_url>" class="nextpage">次の記事</a><!--/nextentry-->
      </div>
      <!--/topentry-->
    <!--/permanent_area-->

    <!--form_area-->
    <!--コメント投稿ページ-->
      <div id="comment_post" class="section">
        <div class="page_title">
          <h2>コメントを書く</h2>
        </div>
        <form method="post" name="form1" action="./">
          <dl class="form">
            <dt>名前</dt>
            <dd><input type="text" name="comment[name]" /></dd>
            <dt>タイトル</dt>
            <dd><input type="text" name="comment[title]" /></dd>
            <dt>メールアドレス</dt>
            <dd><input type="email" name="comment[mail]" /></dd>
            <dt>URL</dt>
            <dd><input type="url" name="comment[url]" /></dd>
            <dt>コメント本文(必須)</dt>
            <dd><textarea name="comment[body]"></textarea></dd>
            <dt>パスワード</dt>
            <dd><input type="password" name="comment[pass]" /></dd>
            <dt>公開設定</dt>
            <dd>
              <select name="comment[himitu]">
                <option value ="0">公開コメント</option>
                <option value ="1">管理人への秘密コメント</option>
              </select>
            </dd>
          </dl>
          <input type="hidden" name="mode" value="regist" />
          <input type="hidden" name="comment[no]" value="<%pno>" />
          <input type="hidden" name="mobile" value="1" />
          <!--private_area--><input type="hidden" name="spass" value="<%spass>" /><!--/private_area-->
          <div class="submit_btn">
            <a href="#" onclick="submit()">投稿する</a>
          </div>
        </form>
        <ul class="contents_footer">
          <li><a href="<%url>?no=<%pno><%tail_url>">記事本文へもどる</a></li>
        </ul>
      </div>
    <!--/form_area-->

    <!--edit_area-->
    <!--ｺﾒﾝﾄｴﾃﾞｨｯﾄｴﾘｱ開始-->
             <div id="comment_post" class="section">
                 <div class="page_title">
                     <h2>コメントを編集する</h2>
                 </div>
                <form method="post" name="form1" action="./">
                     <dl class="form">
                        <dt>名前</dt>
                         <dd><input type="text" name="edit[name]" value="<%edit_name>" /></dd>
                         <dt>タイトル</dt>
                         <dd><input type="text" name="edit[title]" value="<%edit_title>" /></dd>
                         <dt>メールアドレス</dt>
                         <dd><input type="email" name="edit[mail]" value="<%edit_mail>" ></dd>
                         <dt>URL</dt>
                         <dd><input type="url" name="edit[url]" value="<%edit_url>" /></dd>
                         <dt>コメント本文(必須)</dt>
                         <dd><textarea name="edit[body]"><%edit_body></textarea></dd>
                         <dt>パスワード</dt>
                         <dd><input type="password" name="edit[pass]" /></dd>
                         <dt>公開設定</dt>
                         <dd>
                             <select name="edit[himitu]">
                                 <option value ="0">公開コメント</option>
                                 <option value ="1">管理人への秘密コメント</option>
                             </select>
                         </dd>
                     </dl>
                     <div class="submit_btn">
                         <input type="submit" value="更新する">
                         <input type="submit" name="edit[delete]" value="削除する">
                     </div>
                     <input type="hidden" name="mode" value="edit">
                     <input type="hidden" name="mode2" value="edited" />
                     <input type="hidden" name="edit[rno]" value="<%eno>">
                     <input type="hidden" name="sp" value="1" />
                     <!--private_area--><input type="hidden" name="spass" value="<%spass>" /><!--/private_area-->
                 </form>
                 <ul class="contents_footer">
                     <li><a href="<%url>?no=<%edit_entry_no><%tail_url>">記事本文へもどる</a></li>
                 </ul>
             </div>
    <!--ｺﾒﾝﾄｴﾃﾞｨｯﾄｴﾘｱ終了-->
    <!--/edit_area-->

    <!--comment_area-->
    <!--コメント一覧ページ-->
      <div id="comment" class="section">
        <div class="page_title">
          <h2>コメント一覧</h2>
        </div>
        <dl class="list">
          <!--comment-->
          <dt><%comment_title></dt>
          <dd>
            <%comment_body>
            <p class="posted">
              <%comment_mail+name>
              <%comment_url+str><br />
              <%comment_year>年<%comment_month>月<%comment_day>日 <%comment_hour>:<%comment_minute><a href="<%comment_edit_link>" title="<%template_edit_comment>">&nbsp;<%template_edit></a>
            </p>
            <!--comment_reply-->
              <div style="background-color: #ffffdd; border: 1px solid #d7d7d7; padding: 5px; margin: 5px 0 0 5px;">
                <%comment_reply_body>
                <p class="posted">
                  <%comment_reply_year>年<%comment_reply_month>月<%comment_reply_day>日 <%comment_reply_hour>:<%comment_reply_minute>
                </p>
              </div>
            <!--/comment_reply-->
          </dd>
          <!--/comment-->
        </dl>
        <ul class="contents_footer">
          <li><a href="<%url>?no=<%pno><%tail_url>">記事本文へもどる</a></li>
          <li><a href="<%url>?m2=form&no=<%pno><%tail_url>">コメントを書く</a></li>
        </ul>
      </div>
      <!--respage_area-->
      <div class="pager">
        <div class="pager_box">
          <!--res_prevpage_area--><a href="<%res_prevpage_url><%tail_url>" class="prevpage">前へ</a><!--/res_prevpage_area-->
          <%res_template_pager1>
          <!--res_nextpage_area--><a href="<%res_nextpage_url><%tail_url>" class="nextpage">次へ</a><!--/res_nextpage_area-->
        </div>
      </div>
      <!--/respage_area-->
    <!--/comment_area-->

  </div><!--/main_contents-->

  <ul id="page_navi">
    <li class="goto_home"><a href="<%url>">ホーム</a></li>
    <li class="page_top"><a href="#header">ページトップ</a></li>
  </ul>

  <div class="ad_footer"><%ad2></div>

  <div id="footer">
    <div class="footer_menu">
      <a href="<%server_url><%blog_id>/admin.php">ログイン</a>
      <a href="mailto:@?subject=FC2%E3%83%96%E3%83%AD%E3%82%B0&body=<%url>">友達に教える</a>
    </div>
    <p class="copyright">Copyright (c) <%blog_name></p>
    <div class="powered">
      Powered by <a href="http://blog.fc2.com"><%template_fc2blog></a>
    </div>
  </div>
<%ad_overlay>
</body>
</html>
