<?php
if (!headers_sent()) {
  header("Content-Type: text/html; charset=UTF-8");
}
?>
<!DOCTYPE html>
<html lang="<?php echo \Fc2blog\Config::get('LANG'); ?>">
<head>
  <meta charset="utf-8">
  <title><?php if(isset($html_title)) {echo h($html_title);} else {echo h(\Fc2blog\Web\Session::get('blog_id'));} ?></title>
  <link rel="icon" href="https://static.fc2.com/share/image/favicon.ico">
  <link rel="stylesheet" href="/css/normalize.css" type="text/css" media="all">
  <link rel="stylesheet" href="/css/admin-fc2.css" type="text/css" media="all">
  <link rel="stylesheet" href="/css/admin-form.css" type="text/css" media="all">
  <link rel="stylesheet" href="/css/common.css" type="text/css" media="all">
  <link rel="stylesheet" href="/css/main.css" type="text/css" media="all">
  <link rel="stylesheet" href="/css/admin_style.css" type="text/css" media="all">

  <?php echo $this->includeCSS(); ?>

  <script type="text/javascript" src="/js/jquery/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery-migrate-1.2.1.min.js"></script>
  <script type="text/javascript" src="/js/jquery/jquery-ui/1.9.2/jquery-ui.min.js"></script>
  <link rel="stylesheet" href="/css/jquery/jquery-ui/1.9.2/themes/ui-lightness/jquery-ui.css" type="text/css" media="screen" charset="utf-8">
  <script type="text/javascript" src="/js/common_design.js"></script>

  <script type="text/javascript" src="/js/common.js"></script>
  <script>
    // フレームワーク用のjsの設定
    <?php if(\Fc2blog\Config::get('URL_REWRITE')): ?>
      common.isURLRewrite = true;
    <?php endif; ?>
    common.baseDirectory = '<?php echo \Fc2blog\Config::get('BASE_DIRECTORY'); ?>';
    common.deviceType = <?php echo $this->getDeviceType(); ?>;
    common.deviceArgs = '<?php echo \Fc2blog\App::getArgsDevice($request); ?>';
  </script>

  <?php echo $this->includeJS(); ?>

</head>
<body>

  <header class="clear">
    <div>
      <?php if ($this->isLogin()): ?>
        <span><?php echo $this->getNickname(); ?></span>
      <?php endif; ?>

      <?php if ($this->isLogin()): ?>
      <div id="blog_id">
        <?php echo __('Blog name'); ?>：
        <?php $blogSelectList = \Fc2blog\Model\Model::load('Blogs')->getSelectList($this->getUserId()); ?>
        <select id="sys-blog-change">
          <?php foreach ($blogSelectList as $key => $value) : ?>
            <option value="<?php echo $key; ?>" <?php if($key==$this->getBlogId($request)): ?>selected="selected"<?php endif; ?>><?php echo h($value) ?></option>
          <?php endforeach; ?>
          <option disabled="disabled">--------------</option>
          <option value=""><?php echo __('Create a new blog'); ?></option>
        </select>
        <script>
          $(function(){
            $('#sys-blog-change').change(function(){
              var value = $('#sys-blog-change').val();
              if (value=='') {
                location.href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Blogs', 'action'=>'create')); ?>";
              }else{
                location.href = common.fwURL('blogs', 'choice', {blog_id: value});
              }
            });
          });
        </script>
      </div>
      <?php endif; ?>

      <?php $lang = \Fc2blog\Config::get('LANG'); ?>
      <div id="switch_lang">
        <select id="sys-language-setting" onchange="location.href=common.fwURL('common', 'lang', {lang: $(this).val()});">
          <option value="ja" <?php if ($lang=='ja') : ?>selected="selected"<?php endif; ?>>日本語</option>
          <option value="en" <?php if ($lang=='en') : ?>selected="selected"<?php endif; ?>>English</option>
        </select>
      </div>
    </div>
  </header>

  <article>

    <nav id="left-nav">
      <?php if ($this->isLogin()): ?>

        <?php if ($this->isSelectedBlog()) : ?>
          <div class="menu">
            <h3 class="home"><?php echo __('Home'); ?></h3>
            <ul>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Common','action'=>'notice')); ?>"><?php echo __('Notice'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\App::userURL($request,array('controller'=>'entries', 'action'=>'index', 'blog_id'=>$this->getBlogId($request))); ?>" target="_blank"><?php echo __('Checking the blog'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Entries','action'=>'create')); ?>"><?php echo __('New article'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Entries', 'action'=>'index')); ?>"><?php echo __('List of articles'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Comments', 'action'=>'index')); ?>"><?php echo __('List of comments'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Files', 'action'=>'upload')); ?>"><?php echo __('Upload file'); ?></a></li>
            </ul>
          </div>

          <div class="menu">
            <h3 class="setting"><?php echo __('Setting'); ?></h3>
            <ul>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'BlogTemplates', 'action'=>'index')); ?>"><?php echo __('Template management'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'blog_plugins', 'action'=>'index')); ?>"><?php echo __('Plugin management'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Categories','action'=>'create')); ?>"><?php echo __('Category management'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Tags', 'action'=>'index')); ?>"><?php echo __('List of tags'); ?></a></li>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Blogs', 'action'=>'edit')); ?>"><?php echo __('Blog setting'); ?></a></li>
            </ul>
          </div>
        <?php endif; ?>

        <div class="menu">
          <h3 class="account"><?php echo __('User Menu'); ?></h3>
          <ul>
            <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Blogs', 'action'=>'index')); ?>"><?php echo __('List of blogs'); ?></a></li>
            <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Users', 'action'=>'edit')); ?>"><?php echo __('User setting'); ?></a></li>
            <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Users', 'action'=>'logout')); ?>"><?php echo __('Logout'); ?></a></li>
          </ul>
        </div>

        <?php if (\Fc2blog\Config::get('DEBUG')!=0): ?>
          <div class="menu">
            <h3 style="background-color:#999;background-position: 2px -660px;">デバッグ用</h3>
            <ul>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Users', 'action'=>'index')); ?>">ユーザー一覧</a></li>
            </ul>
          </div>
        <?php endif; ?>

      <?php else: ?>

        <div class="menu">
          <h3 style="background-color:#999;background-position: 2px -660px;"><?php echo __('User Menu'); ?></h3>
          <ul>
            <?php if (\Fc2blog\Config::get('USER.REGIST_SETTING.FREE') == \Fc2blog\Config::get('USER.REGIST_STATUS')): ?>
              <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Users', 'action'=>'register')); ?>"><?php echo __('User registration'); ?></a></li>
            <?php endif; ?>
            <li><a href="<?php echo \Fc2blog\Web\Html::url($request, array('controller'=>'Users', 'action'=>'login')); ?>"><?php echo __('Login'); ?></a></li>
          </ul>
        </div>

      <?php endif; ?>
    </nav>

    <article id="main-contents">
      <?php $this->display($request, 'Common/flash_message.php', array('messages'=>$this->removeMessage())); ?>
      <?php $this->display($request, $fw_template); ?>
    </article>

  </article>
<!--
  <footer id="sh_fc2footer_fix">
    <p>フッター</p>
  </footer>
-->
</body>
</html>
