<!doctype html>
<html lang="<%template_language>">
<head>
    <meta charset="<%template_charset>">
    <title><!--not_index_area--><%sub_title> - <!--/not_index_area--><%blog_name></title>
    <meta name="author" content="<%author_name>"/>
    <meta name="description" content="<%introduction>"/>
    <link rel="icon" href="https://static.fc2.com/share/image/favicon.ico">
    <link rel="stylesheet" type="text/css" href="<%css_link>" media="all"/>
    <link rel="alternate" type="application/rss+xml" href="<%url>?xml" title="RSS"/>
    <link rel="top" href="<%url>" title="Top"/>
    <link rel="index" href="<%url>?all" title="<%template_index>"/>

    <!--prevpage-->
    <link rel="prev" href="<%prevpage_url>" title="<%template_prevpage>"/>
    <!--/prevpage-->

    <!--nextpage-->
    <link rel="next" href="<%nextpage_url>" title="<%template_nextpage>"/>
    <!--/nextpage-->

    <!--preventry-->
    <link rel="next" href="<%preventry_url>" title="<%preventry_title>"/>
    <!--/preventry-->

    <!--nextentry-->
    <link rel="prev" href="<%nextentry_url>" title="<%nextentry_title>"/>
    <!--/nextentry-->

</head>
<body>

<header>
    <h1><a href="<%url>"><%blog_name></a></h1>
    <p><%introduction></p>
</header>

<div id="headermenu">
    <p class="archives"><a href="<%url>archives.html">記事一覧</a></p>
</div>

<div>
    <h3>最新記事</h3>
    <ul>
        <!--recent-->
        <li>
            <a href="<%recent_link>" title="<%recent_title>"><%recent_title> (<%recent_month>/<%recent_day>)</a>
        </li>
        <!--/recent-->
    </ul>
</div>

<div>
    <h3>最新コメント</h3>
    <ul>
        <!--rcomment-->
        <li>
            <a href="<%rcomment_link>#comment<%rcomment_no>" title="<%rcomment_title>"><%rcomment_name>:<%rcomment_etitle> (<%rcomment_month>/<%rcomment_day>)</a>
        </li>
        <!--/rcomment-->
    </ul>
</div>

<div>
    <h3>月別アーカイブ</h3>
    <ul>
        <!--archive-->
        <li>
            <a href="<%archive_link>" title="<%archive_year>年<%archive_month>月"><%archive_year>年<%archive_month>月 (<%archive_count>)</a>
        </li>
        <!--/archive-->
    </ul>
</div>

<div>
    <h3>カテゴリ一覧</h3>
    <div>
        <!--category-->
        <div>
            <!--category_sub_hasnext-->┣<!--/category_sub_hasnext-->
            <!--category_sub_end-->┗<!--/category_sub_end-->
            <a href="<%category_link>" title="<%category_name>"><%category_name> (<%category_count>)</a>
        </div>
        <!--/category-->
    </div>
</div>

<div>
    <h3>カテゴリ一覧階層付き</h3>
    <ul class="plugin-multi-tree">
        <!--category-->
        <li><a href="<%category_link>" title="<%category_name>"><%category_name>(<%category_count>)</a></li>
        <!--category_parent-->
        <li><ul>
                <!--/category_parent-->
                <!--category_nosub--><!--category_multi_sub_end-->
            </ul></li>
        <!--/category_multi_sub_end--><!--/category_nosub-->
        <!--/category-->
    </ul>

    <style>
        ul.plugin-multi-tree li{
            list-style: none;
        }
        ul.plugin-multi-tree li a:before{
            content: '・';
        }
    </style>
</div>


<div>
    <h3>カレンダー</h3>
    <div class="plugin-calender">
        <table summary="カレンダー" class="calender">
            <caption>
                <a href="<%prev_month_link>"><%prev_month></a>
                | <%now_year>/<%now_month> |
                <a href="<%next_month_link>"><%next_month></a>
            </caption>
            <tr>
                <th abbr="日曜日" scope="col" id="sun">日</th>
                <th abbr="月曜日" scope="col">月</th>
                <th abbr="火曜日" scope="col">火</th>
                <th abbr="水曜日" scope="col">水</th>
                <th abbr="木曜日" scope="col">木</th>
                <th abbr="金曜日" scope="col">金</th>
                <th abbr="土曜日" scope="col" id="sat">土</th>
            </tr>
            <!--calender-->
            <tr>
                <td><%calender_sun></td>
                <td><%calender_mon></td>
                <td><%calender_tue></td>
                <td><%calender_wed></td>
                <td><%calender_thu></td>
                <td><%calender_fri></td>
                <td><%calender_sat></td>
            </tr>
            <!--/calender-->
        </table>
    </div>
</div>

<div>
    <h3>検索フォーム</h3>
    <form action="./" method="get">
        <p class="plugin-search">
            <input type="text" size="20" name="q" value="" maxlength="200" /><br />
            <input type="submit" value=" 検索 " />
        </p>
    </form>
</div>

<div id="main_contents">
    <!--not_titlelist_area-->
    <!--not_search_area-->
    <!--topentry-->
    <div>
        <p>eno: <%topentry_no></p>
        <h2>
            <!--not_permanent_area-->
            <a href="<%topentry_link>" title="<%template_abs_link>">
                <!--/not_permanent_area-->
                <%topentry_title>
                <!--not_permanent_area-->
            </a>
            <!--/not_permanent_area-->
        </h2>
        <ul>
            <li><%topentry_year>/<%topentry_month>/<%topentry_day></li>
            <li><%topentry_hour>:<%topentry_minute></li>
        </ul>

        <!--not_permanent_area-->
        <ul>
            <!--allow_comment-->
            <li><a href="<%topentry_link>#cm"><%template_post_comment> | comment count:<%topentry_comment_num></a></li>
            <!--/allow_comment-->
            <!--deny_comment-->
            <!--/deny_comment-->
        </ul>
        <div>
            <!--body_img-->
            <div><%topentry_image></div>
            <div><%topentry_discription></div>
            <!--/body_img-->
            <!--body_img_none-->
            <%topentry_discription>
            <!--/body_img_none-->
            <p class="entry_more"><a href="<%topentry_link>" title="<%template_abs_link>"><%template_extend></a></p>
        </div>
        <!--/not_permanent_area-->

        <!--permanent_area-->
        <div class="entry_body">
            <%topentry_body>
            <!--more-->
            <div class="more"><%topentry_more></div>
            <!--/more-->
        </div>
        <div class="entry_footer">
            <ul class="entry_state">
                <li><a href="<%topentry_category_link>"
                       title="<%template_view_category>">category:<%topentry_category></a></li>
                <!--allow_comment-->
                <li><a href="<%topentry_link>#cm" title="<%template_post_comment>">comment
                        count:<%topentry_comment_num></a></li>
                <!--/allow_comment-->
                <!--deny_comment-->
                <li>(comment not allowed)</li>
                <!--/deny_comment-->
            </ul>
        </div>
        <!--/permanent_area-->

    </div>
    <!--/topentry-->
    <!--/not_titlelist_area-->
    <!--/not_search_area-->

    <!--titlelist_area-->
    <div>
        <h2>titlelist_area: <%template_index></h2>
        <ul>
            <!--titlelist-->
            <li>
                <%titlelist_year>/<%titlelist_month>/<%titlelist_day>：
                <a href="<%titlelist_url>" title="<%titlelist_body>"><%titlelist_title></a>：
                <a href="<%titlelist_category_url>" title="<%template_view_category>"><%titlelist_category></a>
            </li>
            <!--/titlelist-->
        </ul>
    </div>
    <!--/titlelist_area-->

    <!--search_area-->
    <div class="content" id="search">
        <h2 class="sub_header"><%template_search_entry> : <%sub_title></h2>
        <ul class="list_body">
            <!--topentry-->
            <li>
                <%topentry_year>/<%topentry_month>/<%topentry_day>(<%topentry_hour>:<%topentry_minute>) ：
                <a href="<%topentry_category_link>" title="<%template_view_category>"><%topentry_category></a> ：
                <a href="<%topentry_link>" title="<%topentry_discription>"><%topentry_title></a>
            </li>
            <!--/topentry-->
        </ul>
    </div><!--/search-->
    <!--/search_area-->

    <!--permanent_area-->
    <div class="page_navi">
        <!--preventry-->
        <a href="<%preventry_url>" title="<%preventry_title>"><%preventry_title></a>
        <!--/preventry-->

        <a href="<%url>" title="<%template_home>" class="home"><%template_home></a>

        <!--nextentry-->
        <a href="<%nextentry_url>" title="<%nextentry_title>"><%nextentry_title></a>
        <!--/nextentry-->
    </div>
    <!--/permanent_area-->

    <!--comment_area-->
    <div>
        <h3 class="sub_header"><%template_comment></h3>

        <!--comment-->
        <div id="comment<%comment_no>">
            <h4 class="sub_title"><%comment_title></h4>
            <div class="sub_body"><%comment_body></div>
            <ul class="sub_footer">
                <li><%comment_year>/<%comment_month>/<%comment_day>(<%comment_hour>:<%comment_minute>)</li>
                <li><%comment_mail+name> <%comment_url+str></li>
                <li><a href="<%comment_edit_link>" title="<%template_edit_comment>"><%template_edit></a></li>
            </ul>
            <!--comment_reply-->
            <div>
                <div>
                    <%comment_reply_body>
                </div>
                <div>
                    <%comment_reply_year>/<%comment_reply_month>/<%comment_reply_day>(<%comment_reply_hour>:<%comment_reply_minute>)
                </div>
            </div>
            <!--/comment_reply-->
        </div>
        <!--/comment-->

        <div>
            <h4><%template_post_comment></h4>
            <form action="./" method="post" name="comment_form" id="comment_form">
                <dl>
                    <dt>
                        <input type="hidden" name="mode" value="regist"/>
                        <input type="hidden" name="comment[no]" value="<%pno>"/>
                        <label for="name"><%template_name></label>
                    </dt>
                    <dd><input id="name" type="text" name="comment[name]" size="30" value="<%cookie_name>"/></dd>
                    <dt><label for="subject"><%template_title></label></dt>
                    <dd><input id="subject" name="comment[title]" type="text" size="30" value=""></dd>
                    <dt><label for="mail"><%template_address></label></dt>
                    <dd><input id="mail" type="text" name="comment[mail]" size="30" value="<%cookie_mail>"/></dd>
                    <dt><label for="url">URL</label></dt>
                    <dd><input id="url" type="text" name="comment[url]" size="30" value="<%cookie_url>"/></dd>
                    <dt><label for="comment"><%template_body></label></dt>
                    <dd><textarea id="comment" cols="50" rows="5" name="comment[body]"></textarea></dd>
                    <dt><label for="pass"><%template_password></label></dt>
                    <dd><input id="pass" type="password" name="comment[pass]" size="20"/></dd>
                    <dt><%template_private></dt>
                    <dd><input id="himitu" type="checkbox" name="comment[himitu]"/><label for="himitu"><%template_private_check></label>
                    </dd>
                </dl>
                <script type="text/javascript" src="<%template_comment_js>"></script>
                <p><input type="submit" value="<%template_send>"/></p>
            </form>
        </div>
    </div>
    <!--/comment_area-->

    <!--edit_area-->
    <div>
        <h3><%template_edit_comment></h3>
        <form action="../../config" method="post" name="comment_form" id="comment_form">
            <dl>
                <dt>
                    <input type="hidden" name="mode" value="edit"/>
                    <input type="hidden" name="mode2" value="edited"/>
                    <input type="hidden" name="edit[rno]" value="<%eno>"/>
                    <label for="name"><%template_name></label>
                </dt>
                <dd><input id="edit[name]" type="text" name="edit[name]" size="30" value="<%edit_name>"/></dd>
                <dt><label for="subject"><%template_title></label></dt>
                <dd><input id="subject" type="text" name="edit[title]" size="30" value="<%edit_title>"/></dd>
                <dt><label for="mail"><%template_address></label></dt>
                <dd><input id="mail" type="text" name="edit[mail]" size="30" value="<%edit_mail>"/></dd>
                <dt><label for="url">URL</label></dt>
                <dd><input id="url" type="text" name="edit[url]" size="30" value="<%edit_url>"/></dd>
                <dt><label for="comment"><%template_body></label></dt>
                <dd><textarea id="comment" cols="50" rows="5" name="edit[body]"><%edit_body></textarea></dd>
                <dt><label for="pass"><%template_password></label></dt>
                <dd><input id="pass" type="password" name="edit[pass]" size="20"/></dd>
                <dt><%template_private></dt>
                <dd><input id="himitu" type="checkbox" name="edit[himitu]"/><label for="himitu"><%template_private_check></label>
                </dd>
            </dl>
            <script type="text/javascript" src="<%template_comment_js>"></script>
            <p class="form_btn">
                <input type="submit" value="<%template_send>"/>
                <input type="submit" name="edit[delete]" value="<%template_delete>"/>
            </p>
        </form>
    </div>
    <!--/edit_area-->

    <!--not_permanent_area-->
    <div class="page_navi">
        <!--prevpage-->
        <a href="<%prevpage_url>" title="<%template_prevpage>"><%template_prevpage></a>
        <!--/prevpage-->

        <a href="<%url>" title="<%template_home>" class="home"><%template_home></a>

        <!--nextpage-->
        <a href="<%nextpage_url>" title="<%template_nextpage>"><%template_nextpage></a>
        <!--/nextpage-->
    </div>
    <!--/not_permanent_area-->

    <div>
        <h2>plugin_third</h2>
        <!--plugin-->
        <div>
            <!--plugin_third-->
            <div>
                <h3><%plugin_third_title></h3>
                <!--plugin_third_description-->
                <div><%plugin_third_description></div><!--/plugin_third_description-->
                <div><%plugin_third_content></div>
                <!--plugin_third_description2-->
                <div><%plugin_third_description2></div><!--/plugin_third_description2-->
            </div>
            <!--/plugin_third-->
        </div>
        <!--/plugin-->
    </div>

</div>

<div>
    <h3>tag_list</h3>
    <!--tag_list-->
    <ul>
        <li><%topentry_tag_list_name></li>
        <li><%topentry_tag_list_parsename></li>
    </ul>
    <!--/tag_list-->
</div>


<div>
    <h3>ctag ($t_tags のインジェクションが必要)</h3>
    <!--ctag_exists-->
    <!--ctag-->
    <ul><%ctag_name>: <%ctag_url></ul>
    <!--/ctag-->
    <!--/ctag_exists-->
</div>
<div>
    <h3>spplugin_first</h3>
    <!--spplugin_first-->
    <div>
        <h3><%plugin_first_title></h3>

        <!--plugin_first_description-->
        <div><%plugin_first_description></div>
        <!--/plugin_first_description-->

        <div><%plugin_first_content></div>

        <!--plugin_first_description2-->
        <div><%plugin_first_description2></div>
        <!--/plugin_first_description2-->
    </div>
    <!--/spplugin_first-->
</div>

<div>
    <h2>plugin_first - side menu</h2>
    <!--plugin-->
    <div>
        <!--plugin_first-->
        <div>
            <h3><%plugin_first_title></h3>

            <!--plugin_first_description-->
            <div><%plugin_first_description></div>
            <!--/plugin_first_description-->

            <div><%plugin_first_content></div>

            <!--plugin_first_description2-->
            <div><%plugin_first_description2></div>
            <!--/plugin_first_description2-->
        </div>
        <!--/plugin_first-->
    </div>
    <!--/plugin-->
</div>

<div>
    <h2>plugin_second - footer menu</h2>
    <!--plugin-->
    <div>
        <!--plugin_second-->
        <div>
            <h3><%plugin_second_title></h3>

            <!--plugin_second_description-->
            <div><%plugin_second_description></div>
            <!--/plugin_second_description-->

            <div><%plugin_second_content></div>

            <!--plugin_second_description2-->
            <div><%plugin_second_description2></div>
            <!--/plugin_second_description2-->
        </div>
        <!--/plugin_second-->
    </div>
    <!--/plugin-->
</div>

<div>
    <details>
        <summary>printable tags</summary>
        <ul>
            <li>%server_url : <%server_url></li>
            <li>%blog_id : <%blog_id></li>
            <li>%titlelist_eno : <%titlelist_eno></li>
            <li>%titlelist_title : <%titlelist_title></li>
            <li>%titlelist_url : <%titlelist_url></li>
            <li>%titlelist_body : <%titlelist_body></li>
            <li>%titlelist_year : <%titlelist_year></li>
            <li>%titlelist_month : <%titlelist_month></li>
            <li>%titlelist_day : <%titlelist_day></li>
            <li>%titlelist_hour : <%titlelist_hour></li>
            <li>%titlelist_minute : <%titlelist_minute></li>
            <li>%titlelist_second : <%titlelist_second></li>
            <li>%titlelist_youbi : <%titlelist_youbi></li>
            <li>%titlelist_wayoubi : <%titlelist_wayoubi></li>
            <li>%titlelist_comment_num : <%titlelist_comment_num></li>
            <li>%titlelist_tb_num : <%titlelist_tb_num></li>
            <li>%titlelist_category_no : <%titlelist_category_no></li>
            <li>%titlelist_category_url : <%titlelist_category_url></li>
            <li>%titlelist_category : <%titlelist_category></li>
            <li>%topentry_no : <%topentry_no></li>
            <li>%topentry_title : <%topentry_title></li>
            <li>%topentry_title_w_img : <%topentry_title_w_img></li>
            <li>%topentry_enc_title : <%topentry_enc_title></li>
            <li>%topentry_enc_utftitle : <%topentry_enc_utftitle></li>
            <li>%topentry_body : <%topentry_body></li>
            <li>%topentry_discription : <%topentry_discription></li>
            <li>%topentry_description : <%topentry_description></li>
            <li>%topentry_desc : <%topentry_desc></li>
            <li>%topentry_link : <%topentry_link></li>
            <li>%topentry_enc_link : <%topentry_enc_link></li>
            <li>%topentry_more : <%topentry_more></li>
            <li>%topentry_year : <%topentry_year></li>
            <li>%topentry_month : <%topentry_month></li>
            <li>%topentry_month:short : <%topentry_month:short></li>
            <li>%topentry_day : <%topentry_day></li>
            <li>%topentry_hour : <%topentry_hour></li>
            <li>%topentry_minute : <%topentry_minute></li>
            <li>%topentry_second : <%topentry_second></li>
            <li>%topentry_youbi : <%topentry_youbi></li>
            <li>%topentry_wayoubi : <%topentry_wayoubi></li>
            <li>%topentry_tb_num : <%topentry_tb_num></li>
            <li>%topentry_tb_no : <%topentry_tb_no></li>
            <li>%topentry_jointtag : <%topentry_jointtag></li>
            <li>%topentry_image : <%topentry_image></li>
            <li>%topentry_image_72 : <%topentry_image_72></li>
            <li>%topentry_image_w300 : <%topentry_image_w300></li>
            <li>%topentry_image_url : <%topentry_image_url></li>
            <li>%topentry_image_url_760x420 : <%topentry_image_url_760x420></li>
            <li>%topentry_comment_num : <%topentry_comment_num></li>
            <li>%topentry_category_no : <%topentry_category_no></li>
            <li>%topentry_category_link : <%topentry_category_link></li>
            <li>%topentry_category : <%topentry_category></li>
            <li>%topentry_tag_list_name : <%topentry_tag_list_name></li>
            <li>%topentry_tag_list_parsename : <%topentry_tag_list_parsename></li>
            <li>%ctag_name : <%ctag_name></li>
            <li>%ctag_url : <%ctag_url></li>
            <li>%topentry_comment_list_name : <%topentry_comment_list_name></li>
            <li>%topentry_comment_list_title : <%topentry_comment_list_title></li>
            <li>%topentry_comment_list_body : <%topentry_comment_list_body></li>
            <li>%topentry_comment_list_brbody : <%topentry_comment_list_brbody></li>
            <li>%topentry_comment_list_date : <%topentry_comment_list_date></li>
            <li>%comment_no : <%comment_no></li>
            <li>%comment_title : <%comment_title></li>
            <li>%comment_body : <%comment_body></li>
            <li>%comment_year : <%comment_year></li>
            <li>%comment_month : <%comment_month></li>
            <li>%comment_day : <%comment_day></li>
            <li>%comment_hour : <%comment_hour></li>
            <li>%comment_minute : <%comment_minute></li>
            <li>%comment_second : <%comment_second></li>
            <li>%comment_youbi : <%comment_youbi></li>
            <li>%comment_wayoubi : <%comment_wayoubi></li>
            <li>%comment_edit_link : <%comment_edit_link></li>
            <li>%comment_name : <%comment_name></li>
            <li>%comment_mail : <%comment_mail></li>
            <li>%comment_url : <%comment_url></li>
            <li>%comment_url+str : <%comment_url+str></li>
            <li>%comment_mail+name : <%comment_mail+name></li>
            <li>%comment_url+name : <%comment_url+name></li>
            <li>%comment_trip : <%comment_trip></li>
            <li>%comment_reply_body : <%comment_reply_body></li>
            <li>%comment_reply_year : <%comment_reply_year></li>
            <li>%comment_reply_month : <%comment_reply_month></li>
            <li>%comment_reply_day : <%comment_reply_day></li>
            <li>%comment_reply_hour : <%comment_reply_hour></li>
            <li>%comment_reply_minute : <%comment_reply_minute></li>
            <li>%comment_reply_second : <%comment_reply_second></li>
            <li>%comment_reply_youbi : <%comment_reply_youbi></li>
            <li>%comment_reply_wayoubi : <%comment_reply_wayoubi></li>
            <li>%cookie_name : <%cookie_name></li>
            <li>%cookie_mail : <%cookie_mail></li>
            <li>%cookie_url : <%cookie_url></li>
            <li>%eno : <%eno></li>
            <li>%edit_name : <%edit_name></li>
            <li>%edit_title : <%edit_title></li>
            <li>%edit_mail : <%edit_mail></li>
            <li>%edit_url : <%edit_url></li>
            <li>%edit_body : <%edit_body></li>
            <li>%edit_message : <%edit_message></li>
            <li>%edit_entry_no : <%edit_entry_no></li>
            <li>%edit_entry_title : <%edit_entry_title></li>
            <li>%plugin_first_title : <%plugin_first_title></li>
            <li>%plugin_second_title : <%plugin_second_title></li>
            <li>%plugin_third_title : <%plugin_third_title></li>
            <li>%plugin_first_content : <%plugin_first_content></li>
            <li>%plugin_second_content : <%plugin_second_content></li>
            <li>%plugin_third_content : <%plugin_third_content></li>
            <li>%plugin_first_description : <%plugin_first_description></li>
            <li>%plugin_first_description2 : <%plugin_first_description2></li>
            <li>%plugin_second_description : <%plugin_second_description></li>
            <li>%plugin_second_description2 : <%plugin_second_description2></li>
            <li>%plugin_third_description : <%plugin_third_description></li>
            <li>%plugin_third_description2 : <%plugin_third_description2></li>
            <li>%plugin_first_talign : <%plugin_first_talign></li>
            <li>%plugin_second_talign : <%plugin_second_talign></li>
            <li>%plugin_third_talign : <%plugin_third_talign></li>
            <li>%plugin_first_tcolor : <%plugin_first_tcolor></li>
            <li>%plugin_second_tcolor : <%plugin_second_tcolor></li>
            <li>%plugin_third_tcolor : <%plugin_third_tcolor></li>
            <li>%plugin_first_align : <%plugin_first_align></li>
            <li>%plugin_second_align : <%plugin_second_align></li>
            <li>%plugin_third_align : <%plugin_third_align></li>
            <li>%plugin_first_color : <%plugin_first_color></li>
            <li>%plugin_second_color : <%plugin_second_color></li>
            <li>%plugin_third_color : <%plugin_third_color></li>
            <li>%plugin_third_ialign : <%plugin_third_ialign></li>
            <li>%plugin_first_ialign : <%plugin_first_ialign></li>
            <li>%plugin_second_ialign : <%plugin_second_ialign></li>
            <li>%spplugin_first_no : <%spplugin_first_no></li>
            <li>%spplugin_first_title : <%spplugin_first_title></li>
            <li>%spplugin_title : <%spplugin_title></li>
            <li>%spplugin_content : <%spplugin_content></li>
            <li>%spplugin_talign : <%spplugin_talign></li>
            <li>%spplugin_tcolor : <%spplugin_tcolor></li>
            <li>%spplugin_first_talign : <%spplugin_first_talign></li>
            <li>%spplugin_first_tcolor : <%spplugin_first_tcolor></li>
            <li>%spplugin_align : <%spplugin_align></li>
            <li>%spplugin_color : <%spplugin_color></li>
            <li>%recent_no : <%recent_no></li>
            <li>%recent_title : <%recent_title></li>
            <li>%recent_link : <%recent_link></li>
            <li>%recent_body : <%recent_body></li>
            <li>%recent_year : <%recent_year></li>
            <li>%recent_month : <%recent_month></li>
            <li>%recent_day : <%recent_day></li>
            <li>%recent_hour : <%recent_hour></li>
            <li>%recent_minute : <%recent_minute></li>
            <li>%recent_second : <%recent_second></li>
            <li>%recent_youbi : <%recent_youbi></li>
            <li>%recent_wayoubi : <%recent_wayoubi></li>
            <li>%recent_image_w300 : <%recent_image_w300></li>
            <li>%category_no : <%category_no></li>
            <li>%category_number : <%category_number></li>
            <li>%category_link : <%category_link></li>
            <li>%category_name : <%category_name></li>
            <li>%category_count : <%category_count></li>
            <li>%archive_link : <%archive_link></li>
            <li>%archive_count : <%archive_count></li>
            <li>%archive_year : <%archive_year></li>
            <li>%archive_month : <%archive_month></li>
            <li>%calendar_sun : <%calendar_sun></li>
            <li>%calendar_mon : <%calendar_mon></li>
            <li>%calendar_tue : <%calendar_tue></li>
            <li>%calendar_wed : <%calendar_wed></li>
            <li>%calendar_thu : <%calendar_thu></li>
            <li>%calendar_fri : <%calendar_fri></li>
            <li>%calendar_sat : <%calendar_sat></li>
            <li>%calender_sun : <%calender_sun></li>
            <li>%calender_mon : <%calender_mon></li>
            <li>%calender_tue : <%calender_tue></li>
            <li>%calender_wed : <%calender_wed></li>
            <li>%calender_thu : <%calender_thu></li>
            <li>%calender_fri : <%calender_fri></li>
            <li>%calender_sat : <%calender_sat></li>
            <li>%rcomment_keyno : <%rcomment_keyno></li>
            <li>%rcomment_etitle : <%rcomment_etitle></li>
            <li>%rcomment_link : <%rcomment_link></li>
            <li>%rcomment_no : <%rcomment_no></li>
            <li>%rcomment_title : <%rcomment_title></li>
            <li>%rcomment_name : <%rcomment_name></li>
            <li>%rcomment_body : <%rcomment_body></li>
            <li>%rcomment_year : <%rcomment_year></li>
            <li>%rcomment_month : <%rcomment_month></li>
            <li>%rcomment_day : <%rcomment_day></li>
            <li>%rcomment_hour : <%rcomment_hour></li>
            <li>%rcomment_minute : <%rcomment_minute></li>
            <li>%rcomment_second : <%rcomment_second></li>
            <li>%rcomment_youbi : <%rcomment_youbi></li>
            <li>%rcomment_wayoubi : <%rcomment_wayoubi></li>
            <li>%rcomment_mail : <%rcomment_mail></li>
            <li>%rcomment_url : <%rcomment_url></li>
            <li>%rcomment_url+str : <%rcomment_url+str></li>
            <li>%rcomment_mail+name : <%rcomment_mail+name></li>
            <li>%rcomment_url+name : <%rcomment_url+name></li>
            <li>%nextpage_url : <%nextpage_url></li>
            <li>%prevpage_url : <%prevpage_url></li>
            <li>%days : <%days></li>
            <li>%now_year : <%now_year></li>
            <li>%now_month : <%now_month></li>
            <li>%prev_month : <%prev_month></li>
            <li>%prev_year : <%prev_year></li>
            <li>%next_month : <%next_month></li>
            <li>%next_year : <%next_year></li>
            <li>%prev_month_link : <%prev_month_link></li>
            <li>%next_month_link : <%next_month_link></li>
            <li>%nextentry_url : <%nextentry_url></li>
            <li>%nextentry_title : <%nextentry_title></li>
            <li>%preventry_url : <%preventry_url></li>
            <li>%preventry_title : <%preventry_title></li>
            <li>%firstpage_num : <%firstpage_num></li>
            <li>%lastpage_num : <%lastpage_num></li>
            <li>%firstpage_url : <%firstpage_url></li>
            <li>%lastpage_url : <%lastpage_url></li>
            <li>%current_page_num : <%current_page_num></li>
            <li>%total_pages : <%total_pages></li>
            <li>%total_num : <%total_num></li>
            <li>%tail_url : <%tail_url></li>
            <li>%template_pager1 : <%template_pager1></li>
            <li>%template_pager2 : <%template_pager2></li>
            <li>%template_pager3 : <%template_pager3></li>
            <li>%template_pager4 : <%template_pager4></li>
            <li>%template_pager5 : <%template_pager5></li>
            <li>%res_nextpage_url : <%res_nextpage_url></li>
            <li>%res_prevpage_url : <%res_prevpage_url></li>
            <li>%res_firstpage_url : <%res_firstpage_url></li>
            <li>%res_lastpage_url : <%res_lastpage_url></li>
            <li>%res_template_pager1 : <%res_template_pager1></li>
            <li>%res_template_pager2 : <%res_template_pager2></li>
            <li>%res_template_pager3 : <%res_template_pager3></li>
            <li>%res_template_pager4 : <%res_template_pager4></li>
            <li>%res_template_pager5 : <%res_template_pager5></li>
            <li>%rapid_templates_autopager : <%rapid_templates_autopager></li>
            <li>%css_link : <%css_link></li>
            <li>%url : <%url></li>
            <li>%blog_name : <%blog_name></li>
            <li>%author_name : <%author_name></li>
            <li>%introduction : <%introduction></li>
            <li>%pno : <%pno></li>
            <li>%sub_title : <%sub_title></li>
            <li>%template_comment_js : <%template_comment_js></li>
            <li>%template_copyright_date : <%template_copyright_date></li>
            <li>%ad : <%ad></li>
            <li>%ad2 : <%ad2></li>
            <li>%ad_overlay : <%ad_overlay></li>
            <li>%template_fc2blog : <%template_fc2blog></li>
            <li>%template_extend : <%template_extend></li>
            <li>%template_theme : <%template_theme></li>
            <li>%template_genre : <%template_genre></li>
            <li>%template_trackback : <%template_trackback></li>
            <li>%template_comment : <%template_comment></li>
            <li>%template_abs_link : <%template_abs_link></li>
            <li>%template_category : <%template_category></li>
            <li>%template_view_category : <%template_view_category></li>
            <li>%template_edit : <%template_edit></li>
            <li>%template_title : <%template_title></li>
            <li>%template_name : <%template_name></li>
            <li>%template_address : <%template_address></li>
            <li>%template_body : <%template_body></li>
            <li>%template_post_comment : <%template_post_comment></li>
            <li>%template_private : <%template_private></li>
            <li>%template_private_check : <%template_private_check></li>
            <li>%template_password : <%template_password></li>
            <li>%template_send : <%template_send></li>
            <li>%template_delete : <%template_delete></li>
            <li>%template_edit_comment : <%template_edit_comment></li>
            <li>%template_trackback_this : <%template_trackback_this></li>
            <li>%template_home : <%template_home></li>
            <li>%template_index : <%template_index></li>
            <li>%template_firstentry : <%template_firstentry></li>
            <li>%template_search_entry : <%template_search_entry></li>
            <li>%template_prevpage : <%template_prevpage></li>
            <li>%template_nextpage : <%template_nextpage></li>
            <li>%template_preventry : <%template_preventry></li>
            <li>%template_nextentry : <%template_nextentry></li>
            <li>%template_go_top : <%template_go_top></li>
            <li>%template_charset : <%template_charset></li>
            <li>%template_return_post : <%template_return_post></li>
            <li>%template_write_cm : <%template_write_cm></li>
            <li>%template_month : <%template_month></li>
            <li>%template_year : <%template_year></li>
            <li>%template_date : <%template_date></li>
            <li>%template_language : <%template_language></li>
            <li>%template_privacy_set : <%template_privacy_set></li>
            <li>%template_privacy_secret : <%template_privacy_secret></li>
            <li>%template_privacy_public : <%template_privacy_public></li>
            <li>%template_cm_body : <%template_cm_body></li>
            <li>%template_required : <%template_required></li>
            <li>%template_next : <%template_next></li>
            <li>%template_prev : <%template_prev></li>
            <li>%template_tell_friend : <%template_tell_friend></li>
            <li>%template_login : <%template_login></li>
            <li>%template_show_pc : <%template_show_pc></li>
            <li>%template_update : <%template_update></li>
            <li>%template_sp_delete : <%template_sp_delete></li>
            <li>%template_tb_list : <%template_tb_list></li>
            <li>%template_edit_cm : <%template_edit_cm></li>
            <li>%template_page_top : <%template_page_top></li>
            <li>%template_sp_post : <%template_sp_post></li>
            <li>%template_cm_list : <%template_cm_list></li>
            <li>%template_tb_close : <%template_tb_close></li>
            <li>%template_show_pic : <%template_show_pic></li>
            <li>%template_cm_close : <%template_cm_close></li>
            <li>%template_pc_view : <%template_pc_view></li>
            <li>%template_category_word : <%template_category_word></li>
            <li>%template_write_cm_2lines : <%template_write_cm_2lines></li>
            <li>%template_last_page : <%template_last_page></li>
            <li>%template_pgof_1 : <%template_pgof_1></li>
            <li>%template_pgof_2 : <%template_pgof_2></li>
            <li>%template_first_page : <%template_first_page></li>
            <li>%template_cm_post : <%template_cm_post></li>
            <li>%template_newest_entries : <%template_newest_entries></li>
            <li>%template_cm_list_of : <%template_cm_list_of></li>
            <li>%template_write_cm_to : <%template_write_cm_to></li>
            <li>%template_album : <%template_album></li>
            <li>%template_posted : <%template_posted></li>
            <li>%template_load_more : <%template_load_more></li>
            <li>%template_view_cm : <%template_view_cm></li>
            <li>%template_newest_comments : <%template_newest_comments></li>
            <li>%template_san : <%template_san></li>
            <li>%template_month_archive : <%template_month_archive></li>
            <li>%template_noplugin : <%template_noplugin></li>
            <li>%template_goto_preventry : <%template_goto_preventry></li>
            <li>%template_goto_nextentry : <%template_goto_nextentry></li>
            <li>%template_secret : <%template_secret></li>
            <li>%template_css_text : <%template_css_text></li>
            <li>name="mode" value="regist" : name="mode" value="regist"</li>
            <li>name="mode" value="edit" : name="mode" value="edit"</li>
        </ul>
    </details>
</div>

</body>
</html>
