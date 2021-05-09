<?php

$config = array();

$config['official_plugins'] = array();

$config['official_plugins'][] = array(
    'device_type' => 1,
    'title' => '最新記事',
    'body' => '最近の記事を表示します',
    'contents' => <<<HTML
<ul>
  <!--recent-->
    <li>
      <a href="<%recent_link>" title="<%recent_title>"><%recent_title> (<%recent_month>/<%recent_day>)</a>
    </li>
  <!--/recent-->
</ul>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 1,
    'title' => '最新コメント',
    'body' => '最近のコメントを表示します',
    'contents' => <<<HTML
<ul>
  <!--rcomment-->
  <li>
    <a href="<%rcomment_link>#comment<%rcomment_no>" title="<%rcomment_title>"><%rcomment_name>:<%rcomment_etitle> (<%rcomment_month>/<%rcomment_day>)</a>
  </li>
  <!--/rcomment-->
</ul>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 1,
    'title' => '月別アーカイブ',
    'body' => '月別アーカイブの一覧を表示します',
    'contents' => <<<HTML
<ul>
  <!--archive-->
  <li>
    <a href="<%archive_link>" title="<%archive_year>年<%archive_month>月"><%archive_year>年<%archive_month>月 (<%archive_count>)</a>
  </li>
  <!--/archive-->
</ul>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 1,
    'title' => 'カテゴリー',
    'body' => 'カテゴリーの一覧を表示します',
    'contents' => <<<HTML
<div>
  <!--category-->
  <div>
    <!--category_sub_hasnext-->┣<!--/category_sub_hasnext-->
    <!--category_sub_end-->┗<!--/category_sub_end-->
    <a href="<%category_link>" title="<%category_name>"><%category_name> (<%category_count>)</a>
  </div>
  <!--/category-->
</div>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 1,
    'title' => 'カテゴリー',
    'body' => '多階層のカテゴリーの一覧を表示します',
    'contents' => <<<HTML
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
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 1,
    'title' => 'カレンダー',
    'body' => '月ごとのカレンダーを表示します',
    'contents' => <<<HTML
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
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 1,
    'title' => '検索フォーム',
    'body' => 'ブログ内検索フォームを表示します',
    'contents' => <<<HTML
<form action="./" method="get">
  <p class="plugin-search">
    <input type="text" size="20" name="q" value="" maxlength="200" /><br />
    <input type="submit" value=" 検索 " />
  </p>
</form>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 4,
    'title' => '最新記事',
    'body' => '最近の記事を表示します',
    'contents' => <<<HTML
<ul class="plugin_list">
<!--recent-->
  <li>
    <a href="<%recent_link>" title="<%recent_title>">
      <em><%recent_title></em><br />
      <span><%recent_year>年<%recent_month>月<%recent_day>日</span>
    </a>
  </li>
<!--/recent-->
</ul>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 4,
    'title' => '最新コメント',
    'body' => '最近のコメントを表示します',
    'contents' => <<<HTML
<ul class="plugin_list">
<!--rcomment-->
  <li>
    <a href="/blog-entry-<%rcomment_keyno>.html?m2=res">
      <em><%rcomment_title></em><br />
      <span><%rcomment_year>年<%rcomment_month>月<%rcomment_day>日</span>
    </a>
  </li>
<!--/rcomment-->
</ul>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 4,
    'title' => '月別アーカイブ',
    'body' => '月別アーカイブの一覧を表示します',
    'contents' => <<<HTML
<ul class="plugin_list">
<!--archive-->
  <li>
    <a href="<%archive_link>">
      <%archive_year>年<%archive_month>月 (<%archive_count>)
    </a>
  </li>
<!--/archive-->
</ul>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 4,
    'title' => 'カテゴリー',
    'body' => 'カテゴリーの一覧を表示します',
    'contents' => <<<HTML
<ul class="plugin_list">
<!--category-->
  <li>
    <a href="<%category_link>">
      <%category_name>（<%category_count>）
    </a>
  </li>
<!--/category-->
</ul>
HTML
,
);

$config['official_plugins'][] = array(
    'device_type' => 4,
    'title' => 'カテゴリー',
    'body' => '多階層のカテゴリーの一覧を表示します',
    'contents' => <<<HTML
<ul class="plugin_list plugin-multi-tree">
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
ul.plugin-multi-tree ul{
  margin-left: 15px;
}
</style>
HTML
,
);

$config['official_plugins'] = array_reverse($config['official_plugins']);

return $config;

