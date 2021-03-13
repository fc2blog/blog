<!doctype html>
<?php
// config
define("GITHUB_REPO", "/uzulla/fc2blog"); // TODO: change to fc2blog/blog
define("GITHUB_REPO_URL", "https://github.com" . GITHUB_REPO . "/");
define("GITHUB_REPO_RELEASE_API_URL", "https://api.github.com/repos" . GITHUB_REPO . "/releases");

// set error handling.
ini_set('display_errors', 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
  // trap any errors. don't allow any notice,warn.
  exit(PHP_EOL . "Caught error: {$errno}, {$errstr} on {$errfile}:{$errline}" . PHP_EOL);
});
ob_start(); // for redirect.
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>fc2blog installer (alpha version)</title>
    <style>
        .notice {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<?php if (get_post_val('mode') === null) { ?>
    <h1>fc2blog installer (alpha version)</h1>
    This is fc2blog installer. Download fc2blog release file and extract, generate config. <br>
    <b class="notice">The installer is now UNDER DEVELOPMENT, ALPHA VERSION.</b> <br>
    <b>The installer will manipulate files. </b><span class="notice">PLEASE BACKUP YOUR SITE before use, And remove this script as
            soon as possible after install</span> (will be self delete when completed). <br>
    <b>The installer should be place to DocumentRoot dir.</b> <br>
    <br>
    <!--<b>If you want update. please put to dir that same as `index.php`.</b> <br> TODO -->
    <hr>

    <h2># requirement check</h2>
    <ul>
        <li>PHP version (fc2blog require php>=7.3):
          <?php
          // check os
          if (is_windows()) {
            exit("Sorry, The script is not support Windows.");
          }

          if (is_php_newer_than("7.3.0")) {
            echo "Your PHP version is <b>" . PHP_VERSION . "</b>, OK.";
          } else {
            echo "Your PHP version is <b>" . PHP_VERSION . "</b>, NG. please update before install.";
            exit;
          }
          ?>
        </li>
        <li>
            Extension check:
          <?php
          $list = [];
          if (!extension_loaded('gd')) {
            $list[] = "gd notfound. ";
          }
          if (!extension_loaded('PDO')) {
            $list[] = "PDO notfound. ";
          }
          if (!extension_loaded('mysqli')) {
            $list[] = "mysqli notfound. ";
          }
          if (!extension_loaded('mbstring')) {
            $list[] = "mbstring notfound. ";
          }
          if (!extension_loaded('intl')) {
            $list[] = "intl notfound. ";
          }
          if (!extension_loaded('zip')) {
            $list[] = "zip notfound. ";
          }
          if (count($list) > 0) {
            echo join($list);
            echo "please install php-extension(s) before install.";
            exit;
          } else {
            echo "looks good.";
          }
          ?>

        </li>
    </ul>

  <?php if (file_exists(__DIR__ . "/index.php")) { ?>
        UPDATE IS NOT IMPLEMENTED YET. please delete index.php(and apps).

  <?php } else { ?>
        <form action="" method="post" onsubmit="return block_duplicate_execute();">
            <script>
              let is_submitted = false;

              function block_duplicate_execute() {
                if (is_submitted) {
                  alert("already executed. please wait a minutes.");
                  return false;
                } else {
                  if (confirm("all settings ok?")) {
                    if (!is_submitted) {
                      is_submitted = true;
                      return true;
                    } else {
                      return false;
                    }
                  } else {
                    return false;
                  }
                }
              }
            </script>

            <h2># install source</h2>
            <ul>
                <li><label><input type="radio" name="install_source" value="GITHUB"
                                  <?php if (!file_exists("fc2blog_dist.zip")){ ?>checked<?php } ?>>
                        <b>Download from GitHub latest release (<a
                                    href="<?= GITHUB_REPO_URL ?>"><?= GITHUB_REPO_URL ?></a>)</b>
                    </label></li>
                <li>
                    <label>
                        <input type="radio" name="install_source" value=""
                               <?php if (file_exists("fc2blog_dist.zip")){ ?>checked<?php } ?>>
                        Other, you can set path to your `fc2blog_dist.zip` . <b>path</b>:
                        <input type="text" name="install_source_other" size="80"
                               value="<?= hsc(__DIR__) ?>/fc2blog_dist.zip">
                    </label>
                </li>
            </ul>

            <h2># install dir</h2>
            <b>(index.php</b> and <b>assets</b> will be install to
            <b><?= hsc(__DIR__) ?></b> (this dir). this is not changeable.) <br>
            <br>
            Please select <b>app</b> directory. (app directory contain code and config. No need to expose.) <br>
            <ul>
                <li>
                    <label>
                        <input type="radio" name="app_dir"
                               value="<?= hsc(__DIR__) ?>/../app/">
                        <b><?= hsc(dirname(__DIR__)) ?>/app/</b>
                        (In document root parent dir. recommend, but some server will be has problem. please carefull
                        conflict other installation.)
                    </label>
                </li>
                <li><label><input type="radio" name="app_dir"
                                  value="<?= hsc(__DIR__) ?>/app/"
                                  checked>
                        <b><?= hsc(__DIR__) ?>/app/</b>
                        (In document root. less secure, more compatibility)
                    </label></li>
                <li>
                    <label>
                        <input type="radio" name="app_dir" value="">
                        other, path input. <b>path</b>:
                        <input type="text" name="app_dir_other" size="80"
                               value="<?= hsc(dirname(__DIR__)) ?>/app">
                    </label>
                </li>
            </ul>
            <h2># generate config.php</h2>
            If you want generate <b>app/config.php</b>. <br>
            <b>Please change to your server settings.</b> <br>
            <ul>
                <li>
                    <label>
                        <input type="checkbox" name="generate_config" value="Y">
                        Generate <b>config.php</b> (if checked, generate config.php.
                        if not, should be create yourself.) <br>
                    </label>
                </li>
                <li>
                    <label>
                        <b>Data base host name</b>
                        <input type="text" name="db_host" value="127.0.0.1">
                    </label>
                </li>
                <li>
                    <label>
                        <b>Data base port</b>
                        <input type="text" name="db_port" value="3306">
                    </label>
                </li>
                <li>
                    <label>
                        <b>Data base name</b>
                        <input type="text" name="db_name" value="fc2blog_db">
                    </label>
                </li>
                <li>
                    <label>
                        <b>Data base user id</b>
                        <input type="text" name="db_user" value="dbuser">
                    </label>
                </li>
                <li>
                    <label>
                        <b>Data base password</b>
                        <input type="text" name="db_password" value="d1B2p3a#s!s">
                    </label>
                </li>
                <li>
                    <label>
                        <b>Data base charset</b>
                        <input type="text" name="db_charset" value="UTF8MB4">
                    </label>
                </li>
                <li>
                    <label>
                        <b>Web server domain</b>
                        <input type="text" name="domain"
                               value="<?= hsc($_SERVER['SERVER_NAME']) ?>">
                    </label>
                </li>
                <li>
                    <label>
                        <b>Web server http port</b>
                        <input type="text" name="http_port" value="80">
                    </label>
                </li>
                <li>
                    <label>
                        <b>Web server https port</b>
                        <input type="text" name="https_port" value="443">
                    </label>
                </li>
            </ul>

            <hr>
            <input type="hidden" name="mode" value="extract">
            <button type="submit" id="extract_button">Execute</button>
        </form>
  <?php } ?>

<?php } elseif (get_post_val('mode') === 'extract') { ?>
    <h2>Extract....</h2>

  <?php
  // create working temp name dir
  if (!is_writable(__DIR__)) {
    exit("this dir(" . __DIR__ . ") not writable");
  }
  $tmp_dir = __DIR__ . "/fc2blog_installer_tmp_dir_delete_me_" . md5(microtime(true));
  if (!mkdir($tmp_dir)) {
    exit("tmpdir create failed. path:{$tmp_dir}");
  }

  // Get zip
  if (
    get_post_val('install_source') === "" &&
    strlen(get_post_val('install_other')) > 0
  ) {
    // use local zip
    $dist_zip_path = get_post_val('install_source_other');

  } elseif (get_post_val('install_source') === "GITHUB") {
    // get latest version url from GitHub
    $release_zip_download_url = get_latest_dist_zip_url_from_github_release();
    if (is_null($release_zip_download_url)) {
      exit("not found any latest release. failed. please delete {$tmp_dir}");
    }

    // download zip to local.
    $dist_zip_path = __DIR__ . "/fc2blog_dist.zip";
    file_download($release_zip_download_url, $dist_zip_path);
  } else {
    exit('invalid install_source');

  }

  // extract zip in tmp dir
  $zip = new ZipArchive();
  if (!$zip->open($dist_zip_path)) {
    exit("failed open zip: {$dist_zip_path}");
  }
  $zip->extractTo($tmp_dir);

  // check extract file
  $tmp_dir_app = $tmp_dir . "/app";
  $tmp_dir_public = $tmp_dir . "/public";
  if (!file_exists($tmp_dir_app) || !file_exists($tmp_dir_public)) {
    exit("It seem failed that file extract. please delete {$tmp_dir}");
  }

  // decide app dir.
  $app_dir = get_post_val('app_dir');
  if ($app_dir === '') { // read from app_dir_other
    $app_dir = get_post_val('app_dir_other');
  }
  if ($app_dir === '') {
    exit("app dir is empty");
  }

  // check app dir
  $app_parent_dir = dirname($app_dir);
  if (!is_writable($app_parent_dir)) {
    exit("app dir' parent_dir is not writable :{$app_parent_dir}");
  }

  // check exists app dir
  if (file_exists($app_dir)) {
    // TODO update mode
    exit("update is not implemented yet");
  } else {
    // new install mode

    mkdir($app_dir);

    // deploy files
    $files_in_tmp_dir_app = glob($tmp_dir_app . '/{*,.[!.]*,..?*}', GLOB_BRACE);
    foreach ($files_in_tmp_dir_app as $files_in_tmp_dir_app_row) {
      copy_r($files_in_tmp_dir_app_row, $app_dir); // todo error handling
    }
    $files_in_tmp_dir_public = glob($tmp_dir_public . '/{*,.[!.]*,..?*}', GLOB_BRACE);
    foreach ($files_in_tmp_dir_public as $files_in_tmp_dir_public_row) {
      copy_r($files_in_tmp_dir_public_row, __DIR__); // todo error handling
    }
  }

  // generate config.php
  if (get_post_val('generate_config') === "Y") {
    echo "Generate config.php<br>" . PHP_EOL;
    $config_php_path = $app_dir . "/config.php";
    file_put_contents($config_php_path, "<?php
define('DB_HOST', '" . get_post_val('db_host') . "');
define('DB_PORT', '" . get_post_val('db_port') . "');
define('DB_USER', '" . get_post_val('db_user') . "');
define('DB_PASSWORD', '" . get_post_val('db_password') . "');
define('DB_DATABASE', '" . get_post_val('db_name') . "');
define('DB_CHARSET', '" . get_post_val('db_charset') . "');
define('DOMAIN', '" . get_post_val('domain') . "');
define('HTTP_PORT', '" . get_post_val('http_port') . "');
define('HTTPS_PORT', '" . get_post_val('https_port') . "');
define('WWW_DIR', '" . __DIR__ . "/');
  ");
  }

  // rewrite app dir path in index.php
  $index_php = file_get_contents("index.php");
  $index_php = preg_replace('/\n\$app_dir_path.+;/u', "\n\$app_dir_path = \"{$app_dir}\";", $index_php);
  file_put_contents("index.php", $index_php);

  // clean up. delete self.
  rmdir_r($tmp_dir);
  unlink($dist_zip_path);
  unlink(__FILE__);

  // Done, redirect
  echo "Extract done.<br>" . PHP_EOL;
  echo "Please goto next install process. <a href='/admin/common/install'>/admin/common/install</a>" . PHP_EOL;
  header("Location: /admin/common/install");

} else {
  exit("<h1>Bad request</h1><br>wrong \$_POST['mode']'");

}
?>
</body>
</html>

<?php
// === functions

function copy_r(string $source, string $dest)
{
  // TODO rewrite by PHP
  `cp -a {$source} {$dest}`;
}

function rmdir_r(string $dirPath): bool
{
  if (!empty($dirPath) && is_dir($dirPath)) {
    $dirObj = new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS);
    $files = new RecursiveIteratorIterator($dirObj, RecursiveIteratorIterator::CHILD_FIRST);

    // remove include files,dirs,symlinks.
    foreach ($files as $path) {
      if ($path->isFile()) {
        unlink($path->getPathname());
      } elseif ($path->isLink()) {
        unlink($path->getPathname());
      } elseif ($path->isDir()) {
        rmdir($path->getPathname());
      }
    }
    // remove target.
    rmdir($dirPath);
    return true;
  }
  return false;
}

function get_latest_dist_zip_url_from_github_release(): ?string
{
  $release_data_url = GITHUB_REPO_RELEASE_API_URL;
  $options = ['http' => ['header' => "User-Agent: fc2blog_installer"]];
  if (!($releases_json = file_get_contents($release_data_url, false, stream_context_create($options)))) {
    exit("releases data download failed. url:{$release_data_url}");
  }
  if (false == ($releases_data = json_decode($releases_json, true)) || !is_array($releases_data)) {
    exit("releases data json parse failed. url:{$release_data_url}");
  }

  $release_zip_download_url = null;

  // Find release that have vX.X.X tag and asset(fc2blog_dist.zip).
  foreach ($releases_data as $release) {
    if (preg_match("/\Av[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\\z/", $release['tag_name'])) {
      if (isset($release['assets']) && is_array($release['assets']) && count($release['assets']) > 0) {
        foreach ($release['assets'] as $release_asset) {
          if ($release_asset['name'] === 'fc2blog_dist.zip') {
            $release_zip_download_url = $release_asset['browser_download_url'];
          }
        }
      }
    }
  }

  return $release_zip_download_url;
}

function file_download(string $src, string $dist): void
{
  $download_read_fh = fopen($src, "r");
  $download_write_fh = fopen($dist, 'w');
  while (!feof($download_read_fh)) {
    fwrite($download_write_fh, fread($download_read_fh, 1024 * 1024));
  }
  fclose($download_read_fh);
  fclose($download_write_fh);
}

function get_post_val(string $key): ?string
{
  if (!isset($_POST[$key])) {
    return null;
  }

  if (!is_string($_POST[$key])) {
    return null;
  }

  return $_POST[$key];
}

function is_windows(): bool
{
  return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
}

function is_php_newer_than(string $version): bool
{
  return version_compare(PHP_VERSION, $version) >= 0;
}

function hsc(string $str): string
{
  return htmlspecialchars($str, ENT_QUOTES);
}
