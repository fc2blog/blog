<?php
// \Fc2blog\Web\Controller\Controller::renderByPhpTemplate からincludeされる、変数スコープはそちらを参照
?>
<!DOCTYPE html>
<html lang="<?php echo $request->lang; ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0" />
  <meta content="telephone=no" name="format-detection" />
  <meta name="robots" content="noindex, nofollow, noarchive" />
  <title><?php echo h($blog['name']); ?></title>
  <link rel="icon" href="https://static.fc2.com/share/image/favicon.ico">
  <link rel="stylesheet" href="/css/sp/blog_sp_admin.css" type="text/css" media="all">
  <link rel="stylesheet" href="/css/sp/side_menu.css" type="text/css" media="all">
  <link rel="stylesheet" href="/css/sp/blog_comment_sp.css" type="text/css" media="all">

  <?php echo $this->includeCSS(); ?>

  <script type="text/javascript" src="/js/jquery/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery-ui/1.9.2/jquery-ui.min.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery.ui.touch-punch.min.js"></script>
  <link rel="stylesheet" href="/css/jquery/jquery-ui/1.9.2/themes/ui-lightness/jquery-ui.css" type="text/css" media="screen" charset="utf-8">
  <script type="text/javascript" src="/js/common_design.js"></script>

  <script type="text/javascript" src="/js/common.js"></script>

  <?php echo $this->includeJS(); ?>
</head>
<body>

<div id="wrapper_all">
<div id="wrapper">

  <header id="global_header">
    <div><!--span id="left_menu_btn"><i class="leftmenu"></i></span--></div>
    <?php if ($this->isLogin()): ?>
      <div><!--span id="right_menu_btn"><i class="rightmenu"></i></span--></div>
    <?php endif; ?>
    <!--h1><?php echo \Fc2blog\Web\Session::get('blog_id'); ?></h1-->
  </header>

  <div id="contents">
      <?php $this->display($request, $template_file_path); ?>
  </div>

  <footer id="site_footer">
      <?php $lang = $request->lang; ?>
      <div id="switch_lang" class="sh_langselect">
        <select id="sys-language-setting" onchange="location.href=common.fwURL('common', 'lang', {lang: $(this).val()});">
          <option value="ja" <?php if ($lang=='ja') : ?>selected="selected"<?php endif; ?>>日本語</option>
          <option value="en" <?php if ($lang=='en') : ?>selected="selected"<?php endif; ?>>English</option>
        </select>
      </div>
  </footer>

</div><!--/wrapper-->
</div><!--#wrapper_all-->
</body>
</html>
