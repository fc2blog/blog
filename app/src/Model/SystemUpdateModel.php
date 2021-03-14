<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use JsonException;
use RuntimeException;

class SystemUpdateModel
{
  /** @var string */
  static public $releases_url = "https://www.github.com/uzulla/fc2blog/releases"; // TODO: change to fc2blog/blog
  /** @var string */
//  static public $releases_api_url = "https://api.github.com/repos/uzulla/tag-release-test/releases"; // TODO: change to fc2blog/blog
  static public $releases_api_url = "https://api.github.com/repos/uzulla/fc2blog/releases"; // TODO: change to fc2blog/blog

  /**
   * Get release info list from GitHub.
   */
  public static function getReleaseInfo(): ?array
  {
    // TODO cache
    // app/temp/releases.json の最終更新時刻をみて、1h以内ならそれを利用する

    $options = ['http' => ['header' => "User-Agent: fc2blog_installer"]];

    $releases_json = @file_get_contents(
      static::$releases_api_url,
      false,
      stream_context_create($options)
    );

    // rate limit等
    $pos = strpos($http_response_header[0], '403');
    if ($pos !== false) {
      var_dump($http_response_header);
      return null;
    }

    $pos = strpos($http_response_header[0], '200');
    if ($pos === false) {
      throw new RuntimeException("api request failed: status code {$http_response_header[0]}");
    }

    if ($releases_json === false) {
      throw new RuntimeException("api request failed");
    }

    try {
      $releases_data = json_decode($releases_json, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
      throw new RuntimeException("json parse failed:{$e->getMessage()} on {$e->getFile()}:{$e->getLine()}");
    }

    // app/temp/releases.json にjsonを書き出す

    return $releases_data;
  }

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

}
