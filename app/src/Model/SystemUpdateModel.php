<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use InvalidArgumentException;
use JsonException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use SplFileInfo;
use ZipArchive;

class SystemUpdateModel
{
  /** @var string */
  static public $releases_url = "https://www.github.com/uzulla/fc2blog/releases"; // TODO: change to fc2blog/blog
  /** @var string */
  static public $releases_api_url = "https://api.github.com/repos/uzulla/fc2blog/releases"; // TODO: change to fc2blog/blog
  /** @var string */
  static public $cached_release_info_path = __DIR__ . "/../../temp/github_release_cache.json";
  /** @var string */
  static public $version_file_path = __DIR__ . "/../../version";

  /**
   * Get download url of `fc2blog_dist.zip` in assets from release array.
   * @param array $release
   * @return string|null
   */
  public static function getZipDownloadUrl(array $release): ?string
  {
    if (!isset($release['assets'])) {
      return null;
    }

    $found_asset = null;
    foreach ($release['assets'] as $release_asset) {
      if ($release_asset['name'] === 'fc2blog_dist.zip') {
        $found_asset = $release_asset;
      }
    }

    if (!is_array($found_asset) || !isset($found_asset['browser_download_url'])) {
      return null;
    }

    return $found_asset['browser_download_url'];
  }

  /**
   * Get release info list from GitHub.
   * @return array|null
   */
  public static function getReleaseInfo(): ?array
  {
    // app/temp/releases.json の最終更新時刻をみて、1h以内ならそれを利用する
    if (file_exists(static::$cached_release_info_path) && filemtime(static::$cached_release_info_path) > (time() - (60 * 60))) {
      $releases_json = file_get_contents(static::$cached_release_info_path);
      try {
        return json_decode($releases_json, true, 512, JSON_THROW_ON_ERROR);
      } catch (JsonException $e) {
        error_log("invalid json. but on going.");
      }
    }

    // まず削除する
    if (file_exists(static::$cached_release_info_path)) {
      unlink(static::$cached_release_info_path);
    }

    // JSONのrelease情報JSONをGitHub APIより取得
    $options = ['http' => ['header' => "User-Agent: fc2blog_installer"]];
    $releases_json = @file_get_contents(
      static::$releases_api_url,
      false,
      stream_context_create($options)
    );

    // rate limit等
    $pos = strpos($http_response_header[0], '403');
    if ($pos !== false) {
      return null;
    }

    // 成功か確認
    $pos = strpos($http_response_header[0], '200');
    if ($pos === false) {
      throw new RuntimeException("api request failed: status code {$http_response_header[0]}");
    }
    if ($releases_json === false) {
      throw new RuntimeException("api request failed");
    }

    // デコード試行
    try {
      $releases_data = json_decode($releases_json, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
      // JSONでデコード出来ないデータが来ている
      error_log("json parse failed:{$e->getMessage()} on {$e->getFile()}:{$e->getLine()}");
      return null;
    }

    // app/temp/releases.json にjsonを書き出す
    file_put_contents(static::$cached_release_info_path, $releases_json);

    return $releases_data;
  }

  /**
   * Get latest release that has vX.X.X tag from release list.
   * @param $release_list
   * @return array|null
   */
  public static function getValidLatestRelease($release_list): ?array
  {
    foreach ($release_list as $release) {
      if (preg_match("/\Av[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\\z/", $release['tag_name'])) {
        if (isset($release['assets']) && is_array($release['assets']) && count($release['assets']) > 0) {
          return $release;
        }
      }
    }
    return null;
  }

  /**
   * Get this fc2blog version from version file
   * @param bool $allow_raw
   * @return string|null
   */
  public static function getVersion(bool $allow_raw = false): ?string
  {
    if (!file_exists(static::$version_file_path)) {
      // 開発中など、不明
      return null;
    }

    $version = trim(file_get_contents(static::$version_file_path));

    if ($allow_raw) {
      return $version;
    }

    if (!preg_match("/\Av[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\z/u", $version)) {
      // invalid version format
      return null;
    }

    return $version;
  }

  /**
   * find release array from release list by version string.
   * @param array $release_list
   * @param string $version
   * @return array|null
   */
  public static function findByVersionFromReleaseList(array $release_list, string $version): ?array
  {
    foreach ($release_list as $release) {
      if ($version === $release['tag_name']) {
        if (isset($release['assets']) && is_array($release['assets']) && count($release['assets']) > 0) {
          return $release;
        }
      }
    }
    return null;
  }

  /**
   * file download
   * @param $src
   * @param $dist
   * @return bool
   */
  private static function downloadFile($src, $dist): bool
  {
    // use stream wrapper
    $download_read_fh = fopen($src, "r");
    $download_write_fh = fopen($dist, 'w');
    while (!feof($download_read_fh)) {
      fwrite($download_write_fh, fread($download_read_fh, 1024 * 1024));
    }
    fclose($download_read_fh);
    fclose($download_write_fh);
    return true;
  }

  public static function updateSystemByUrl(string $zip_url)
  {
    // テンポラリのURLにダウンロード
    $file = tmpfile();
    $zip_path = stream_get_meta_data($file)['uri'];

    if (!static::downloadFile($zip_url, $zip_path)) {
      throw new RuntimeException("File download error");
    }

    try {
      static::updateSystemByLocalZip($zip_path);
    } finally {
      @unlink($zip_url);
    }
  }

  public static function updateSystemByLocalZip(string $dist_zip_path)
  {
    $tmp_path = tempnam(sys_get_temp_dir(), "fc2blog_dist_");
    unlink($tmp_path); // use to directory. file is not important.

    if (!mkdir($tmp_path)) {
      throw new RuntimeException("mkdir({$tmp_path}) failed");
    }

    try {
      // extract zip in tmp dir
      $zip = new ZipArchive();
      if (!$zip->open($dist_zip_path)) {
        exit("failed open zip: {$dist_zip_path}");
      }
      $zip->extractTo($tmp_path);

      // check extract file
      $tmp_dir_app = $tmp_path . "/app";
      $tmp_dir_public = $tmp_path . "/public";
      if (!file_exists($tmp_dir_app) || !file_exists($tmp_dir_public)) {
        throw new InvalidArgumentException("It seems failed that file extract or invalid zip.");
      }

      // decide app dir.
      $app_dir = APP_DIR;

      // deploy files
      // TODO remove src dir
      $files_in_tmp_dir_app = glob($tmp_dir_app . '/{*,.[!.]*,..?*}', GLOB_BRACE);
      foreach ($files_in_tmp_dir_app as $files_in_tmp_dir_app_row) {
        static::copy_r($files_in_tmp_dir_app_row, $app_dir); // todo error handling
      }
      $files_in_tmp_dir_public = glob($tmp_dir_public . '/{*,.[!.]*,..?*}', GLOB_BRACE);
      foreach ($files_in_tmp_dir_public as $files_in_tmp_dir_public_row) {
        static::copy_r($files_in_tmp_dir_public_row, __DIR__); // todo error handling
      }

    } finally {
      static::rmdir_r($tmp_path);
    }

  }

  private static function rmdir_r(string $dirPath): bool
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

  private static function copy_r(string $src_path, string $dest_dir)
  {
    $src_base_dir = realpath(dirname($src_path));
    $src_file_name = basename($src_path);

    // コピー先ディレクトリの準備
    if (!file_exists($dest_dir)) {
      // コピー先ディレクトリがないので作成
      mkdir($dest_dir, 0777, true);
    } else if (is_dir($src_path) && (is_file($dest_dir) || is_link($dest_dir))) {
      // srcがdirだが、コピー先はdirでない既存がある場合、削除してディレクトリを作成
      // (ファイルなら後で上書きするので、そのまま進める）
      unlink($dest_dir);
      mkdir($dest_dir, 0777, true);
    }

    // 単なるファイルやSymlinkならコピーして終わり
    if (!is_dir($src_path)) {
      copy($src_path, $dest_dir . "/" . $src_file_name);
      return;
    }

    $dirObj = new RecursiveDirectoryIterator($src_path, RecursiveDirectoryIterator::SKIP_DOTS);
    /** @var SplFileInfo[] $files */
    $files = new RecursiveIteratorIterator($dirObj, RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($files as $path) {
      $relative_path = substr($path->getPath(), strlen($src_base_dir) + 1);
      $src_full_path = $src_base_dir . "/" . $relative_path;
      $dest_full_path = $dest_dir . "/" . $relative_path;

      if (is_dir($dest_full_path) && file_exists($dest_full_path)) {
        // ディレクトリで、すでにディレクトリが存在しているならスキップ
        continue;

      } else if (is_dir($src_full_path) && !file_exists($dest_full_path)) {
        // ディレクトリを作成
        mkdir($dest_full_path);

      } else if (is_dir($src_full_path) && file_exists($dest_full_path) && !is_dir($dest_full_path)) {
        // ファイルがディレクトリになっているので、削除してディレクトリへ
        unlink($dest_full_path);
        mkdir($dest_full_path);

      } else if (is_file($src_full_path) && file_exists($dest_full_path) && is_dir($dest_full_path)) {
        // ディレクトリがファイルになっているので、削除してファイルへ
        rmdir_r($dest_full_path);
        copy($src_full_path, $dest_full_path);

      } else {
        // ファイルなら、コピー

        // コピー先親ディレクトリがなければ作成
        $parent_dir = dirname($dest_full_path);
        if (!file_exists($parent_dir)) {
          mkdir($parent_dir, 0777, true);
        }
        copy($src_full_path, $dest_full_path);
        touch($dest_full_path); // update file timestamp
      }
    }
  }
}
