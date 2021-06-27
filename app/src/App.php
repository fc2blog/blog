<?php
declare(strict_types=1);

namespace Fc2blog;

use Exception;
use Fc2blog\Model\BlogsModel;
use Fc2blog\Util\StringCaseConverter;
use Fc2blog\Web\Request;
use InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use RuntimeException;

class App
{
    const WWW_DIR = WWW_DIR;
    const APP_DIR = APP_DIR;
    const WWW_UPLOAD_DIR = self::WWW_DIR . 'uploads/';
    const CONFIG_DIR = self::APP_DIR . 'src/config/';
    const LOCALE_DIR = self::APP_DIR . 'locale/';
    const TEMP_DIR = self::APP_DIR . 'temp/';
    const BLOG_TEMPLATE_DIR = self::TEMP_DIR . 'blog_template/';

    const SESSION_NAME = 'dojima';
    const SESSION_COOKIE_EXPIRE_DAY = 180;
    const DOMAIN = DOMAIN;
    const DOMAIN_USER = self::DOMAIN;

    const HTTP_PORT_STR = (HTTP_PORT === "80") ? '' : ":" . HTTP_PORT; // http時、80は省略できる
    const HTTPS_PORT_STR = (HTTP_PORT === "443") ? '' : ":" . HTTPS_PORT; // https時、443は省略できる

    const DEVICE_PC = 1;
    const DEVICE_SP = 4;
    const DEVICES = [
        self::DEVICE_PC,
        self::DEVICE_SP,
    ];

    const DEVICE_FC2_KEY = [
        1 => 'pc',   // PC
        4 => 'sp',   // スマフォ
    ];

    static public function getDeviceFc2Key($device_id): string
    {
        if (!isset(self::DEVICE_FC2_KEY[(int)$device_id])) throw new InvalidArgumentException("missing device id in DEVICE_FC2_KEY");
        return self::DEVICE_FC2_KEY[(int)$device_id];
    }

    const ALLOW_DEVICES = [
        self::DEVICE_PC,
        self::DEVICE_SP,
    ];

    const APP_DISPLAY_SHOW = 0; // 非表示
    const APP_DISPLAY_HIDE = 1; // 非表示

    public static $lang = "ja";
    public static $language = "ja_JP.UTF-8";
    public static $languages = [
        'ja' => 'ja_JP.UTF-8',
        'en' => 'en_US.UTF-8',
    ];
    public static $timesZone = 'Asia/Tokyo';

    /**
     * ブログIDから階層別フォルダ作成
     * @param string $blog_id
     * @return string
     */
    public static function getBlogLayer(string $blog_id): string
    {
        return $blog_id[0] . '/' . $blog_id[1] . '/' . $blog_id[2] . '/' . $blog_id;
    }

    /**
     * ユーザーのアップロードしたファイルパスを返す
     * @param array $file
     * @param bool $abs
     * @param bool $timestamp
     * @return string
     */
    public static function getUserFilePath(array $file, bool $abs = false, bool $timestamp = false): string
    {
        $file_path = static::getBlogLayer($file['blog_id']) . '/file/' . $file['id'] . '.' . $file['ext'];
        return ($abs ? App::WWW_UPLOAD_DIR : '/uploads/') . $file_path . ($timestamp ? '?t=' . strtotime($file['updated_at']) : '');
    }

    /**
     * サムネイル画像のパスを返却する
     * 対象外の場合は元のパスを返却する
     * @param string $url
     * @param int $size
     * @param string $whs
     * @return string
     */
    public static function getThumbnailPath(string $url, int $size = 72, string $whs = ''): string
    {
        if (empty($url)) {
            return $url;
        }
        if (!preg_match('{(/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]+/file/[0-9]+)\.(png|gif|jpe?g)(\?t=[0-9]+)?$}', $url, $matches)) {
            return $url;
        }
        return $matches[1] . '_' . $whs . $size . '.' . $matches[2] . ($matches[3] ?? '');
    }

    /**
     * 中央切り抜きのサムネイル画像のパスを返却する
     * 対象外の場合は元のパスを返却する
     * @param string $url
     * @param int $width
     * @param int $height
     * @param string $whs
     * @return string
     */
    public static function getCenterThumbnailPath(string $url, int $width = 760, int $height = 420, string $whs = ''): string
    {
        if (empty($url)) {
            return $url;
        }
        if (!preg_match('{(/uploads/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]/[0-9a-zA-Z]+/file/[0-9]+)\.(png|gif|jpe?g)(\?t=[0-9]+)?$}', $url, $matches)) {
            return $url;
        }
        return $matches[1] . '_' . $whs . $width . '_' . $height . '.' . $matches[2] . ($matches[3] ?? '');
    }

    /**
     * ブログIDとIDに紐づくファイルを削除する
     * @param string $blog_id
     * @param string $id
     */
    public static function deleteFile(string $blog_id, string $id): void
    {
        $dir_path = App::WWW_UPLOAD_DIR . static::getBlogLayer($blog_id) . '/file/';
        $files = scandir($dir_path);
        foreach ($files as $file_name) {
            if (strpos($file_name, $id . '_') === 0) {
                // サムネイル用ファイルの削除
                unlink($dir_path . $file_name);
            }
            if (strpos($file_name, $id . '.') === 0) {
                // オリジナルファイル削除
                unlink($dir_path . $file_name);
            }
        }
    }

    /**
     * プラグインへのファイルパス
     * @param string $blog_id
     * @param string $id
     * @return string
     */
    public static function getPluginFilePath(string $blog_id, string $id): string
    {
        return App::BLOG_TEMPLATE_DIR . static::getBlogLayer($blog_id) . '/plugins/' . $id . '.php';
    }

    /**
     * ファイルパスまでのフォルダを作成する
     * @param string $file_path
     */
    public static function mkdir(string $file_path): void
    {
        $folder_dir = dirname($file_path);
        if (!file_exists($folder_dir)) {
            mkdir($folder_dir, 0777, true);
        }
    }

    /**
     * ブログディレクトリを削除
     * @param string $blog_id
     */
    public static function removeBlogDirectory(string $blog_id): void
    {
        $fs = new Local("/");

        $upload_path = App::WWW_UPLOAD_DIR . '/' . static::getBlogLayer($blog_id);
        $fs->deleteDir($upload_path);

        $template_path = App::BLOG_TEMPLATE_DIR . static::getBlogLayer($blog_id);
        $fs->deleteDir($template_path);
    }

    /**
     * 開始日と終了日を計算する
     * 存在しない日付の場合は本日として解釈する
     * @param int $year
     * @param int $month
     * @param int $day
     * @return array|string[]
     */
    public static function calcStartAndEndDate(int $year = 0, int $month = 0, int $day = 0): array
    {
        if (!$year) {
            // 年が存在しない場合本日を開始、終了日時として割り当てる
            return array(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'));
        }
        $start = $end = $year . '-';
        if ($month) {
            $start .= $month . '-';
            $end .= $month . '-';
            if ($day) {
                $start .= $day;
                $end .= $day;
            } else {
                $start .= '01';
                $end .= date('t', mktime(0, 0, 0, $month, 1, $year));
            }
        } else {
            $start .= '01-01';
            $end .= '12-31';
        }
        $dates = explode('-', $start);
        if (!checkdate((int)$dates[1], (int)$dates[2], (int)$dates[0])) {
            // 存在日付の場合は本日を開始、終了日時として割り当てる
            $start = $end = date('Y-m-d');
        }
        $start .= ' 00:00:00';
        $end .= ' 23:59:59';
        return array($start, $end);
    }

    /**
     * デバイスタイプを取得する
     * @param Request $request
     * @return int
     */
    public static function getDeviceType(Request $request): int
    {
        // パラメータによりデバイスタイプを変更(FC2の引数順守)
        if ($request->isArgs('pc')) {
            return App::DEVICE_PC;
        }
        if ($request->isArgs('sp')) {
            return App::DEVICE_SP;
        }

        // Cookieからデバイスタイプを取得
        $device_type = $request->rawCookie('device');
        $devices = [
            App::DEVICE_PC,
            App::DEVICE_SP,
        ];
        if (!empty($device_type) && in_array($device_type, $devices)) {
            return (int)$device_type;
        }

        // ユーザーエージェントからデバイスタイプを取得
        $ua = $request->server['HTTP_USER_AGENT'];

        $devices = array('iPhone', 'iPod', 'Android');
        foreach ($devices as $device) {
            if (strpos($ua, $device) !== false) {
                return App::DEVICE_SP;
            }
        }
        return App::DEVICE_PC;
    }

    /**
     * デバイスタイプを取得する
     * @param Request $request
     * @return string|null
     */
    public static function getDeviceTypeStr(Request $request): string
    {
        $device_id = static::getDeviceType($request);
        $device_table = App::DEVICE_FC2_KEY;
        return $device_table[$device_id];
    }

    /**
     * 現在のデバイスタイプをPC,SPの形で取得する
     * @param Request $request
     * @return string
     */
    public static function getDeviceKey(Request $request): string
    {
        $device_type = self::getDeviceType($request);
        switch ($device_type) {
            default:
            case 1:
                return 'PC';
            case 4:
                return 'SP';
        }
    }

    /**
     * 引数のデバイスタイプを取得する
     * @param Request $request
     * @return string
     */
    public static function getArgsDevice(Request $request): string
    {
        static $device_name = null;   // 良く使用するのでキャッシュ
        if ($device_name === null) {
            if ($request->isArgs('pc')) {
                $device_name = 'pc';
            } else if ($request->isArgs('sp')) {
                $device_name = 'sp';
            } else {
                $device_name = '';
            }
        }
        return $device_name;
    }

    /**
     * iPhone,iPodかどうかを判定
     * (isIOSだが、過去iOSを搭載されていたiPadは含まれない)
     * @param Request $request
     * @return bool
     */
    public static function isIOS(Request $request): bool
    {
        return self::isSP($request) && strpos($request->server['HTTP_USER_AGENT'], 'iPhone') !== false;
    }

    /**
     * Androidかどうかを判定
     * @param Request $request
     * @return bool
     */
    public static function isAndroid(Request $request): bool
    {
        return self::isSP($request) && strpos($request->server['HTTP_USER_AGENT'], 'Android') !== false;
    }

    /**
     * PC環境下どうかを調べる
     * @param Request $request
     * @return bool
     */
    public static function isPC(Request $request): bool
    {
        return $request->deviceType == App::DEVICE_PC;
    }

    /**
     * SP環境下どうかを調べる
     * @param Request $request
     * @return bool
     */
    public static function isSP(Request $request): bool
    {
        return $request->deviceType == App::DEVICE_SP;
    }

    /**
     * ユーザー画面用のURL
     * @param Request $request
     * @param array $args
     * @param bool $reused
     * @param bool $abs
     * @return string
     */
    public static function userURL(Request $request, array $args = [], bool $reused = false, bool $abs = false): string
    {
        // 現在のURLの引数を引き継ぐ
        if ($reused == true) {
            $gets = $request->getGet();
            unset($gets['mode']);
            unset($gets['process']);
            $args = array_merge($gets, $args);
        }

        $controller = $request->shortControllerName;
        if (isset($args['controller'])) {
            $controller = $args['controller'];
            unset($args['controller']);
        }

        $action = $request->methodName;
        if (isset($args['action'])) {
            $action = $args['action'];
            unset($args['action']);
        }

        // BlogIdを先頭に付与する
        $blog_id = null;
        if (isset($args['blog_id'])) {
            $blog_id = $args['blog_id'];
            unset($args['blog_id']);
        }

        // 引数のデバイスタイプを取得
        $device_name = self::getArgsDevice($request);
        if (!empty($device_name) && isset($args[$device_name])) {
            unset($args[$device_name]);
        }

        // 絶対パスが必要な際に、フルのホスト名を取得する
        $full_domain = ($abs && !is_null($blog_id)) ? BlogsModel::getFullHostUrlByBlogId($blog_id) : "";

        // TOPページの場合
        if (strtolower($controller) == 'entries' && strtolower($action) == 'index' && !empty($blog_id)) {
            $url = '/';

            $params = [];
            foreach ($args as $key => $value) {
                $params[] = $key . '=' . $value;
            }
            if (!empty($device_name)) {
                $params[] = $device_name;
            }
            if (count($params)) {
                $url .= '?' . implode('&', $params);
            }
            if ($blog_id && $blog_id !== Config::get('DEFAULT_BLOG_ID')) {
                $url = '/' . $blog_id . $url;
            }
            return ($abs ? $full_domain : '') . $url;
        }

        // 記事の場合
        if (strtolower($controller) == 'entries' && strtolower($action) == 'view' && !empty($args['id'])) {
            $url = '/blog-entry-' . $args['id'] . '.html';
            unset($args['id']);

            $params = [];
            foreach ($args as $key => $value) {
                $params[] = $key . '=' . $value;
            }
            if (!empty($device_name)) {
                $params[] = $device_name;
            }
            if (count($params) > 0) {
                $url .= '?' . implode('&', $params);
            }
            if ($blog_id && $blog_id !== Config::get('DEFAULT_BLOG_ID')) {
                $url = '/' . $blog_id . $url;
            }
            return ($abs ? $full_domain : '') . $url;
        }

        $params = [];
        $params[] = 'mode=' . lcfirst($controller);
        $params[] = 'process=' . $action;
        foreach ($args as $key => $value) {
            $params[] = $key . '=' . $value;
        }
        if (!empty($device_name)) {
            $params[] = $device_name;
        }

        $url = '/index.php';
        if (count($params)) {
            $url .= '?' . implode('&', $params);
        }
        if ($blog_id && $blog_id !== Config::get('DEFAULT_BLOG_ID')) {
            $url = '/' . $blog_id . $url;
        }
        return ($abs ? $full_domain : '') . $url;
    }

    /**
     * ページ毎、デバイス毎の初期制限件数
     * @param Request $request
     * @param string $key
     * @return int
     */
    public static function getPageLimit(Request $request, string $key): int
    {
        return Config::get('PAGE.' . $key . '.' . self::getDeviceKey($request) . '.LIMIT', Config::get('PAGE.' . $key . '.DEFAULT.LIMIT', 10));
    }

    /**
     * ページ毎、デバイス毎の件数一覧
     * @param Request $request
     * @param string $key
     * @return array
     */
    public static function getPageList(Request $request, string $key): array
    {
        return Config::get('PAGE.' . $key . '.' . self::getDeviceKey($request) . '.LIST', Config::get('PAGE.' . $key . '.DEFAULT.LIST', []));
    }

    /**
     * 現在選択中のメニューかどうかを返す
     * @param Request $request
     * @param array|string params = array('entries/create', 'entries/edit', ...),
     * @return bool
     * TODO Configの削減
     * @noinspection PhpUnused
     */
    public static function isActiveMenu(Request $request, $params): bool
    {
        [$controller_name, $method_name] = explode('/', static::getActiveMenu($request));

        if (is_string($params)) {
            $params = array($params);
        }

        // コントローラー名とメソッド名を判定
        foreach ($params as $value) {
            [$c_name, $m_name] = explode('/', $value);
            if (lcfirst($c_name) != $controller_name) {
                continue;
            }
            if (!empty($m_name) && StringCaseConverter::snakeCase($m_name) != $method_name) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * 現在選択中のメニューを返す
     * @param Request $request
     * @return string
     */
    public static function getActiveMenu(Request $request): string
    {
        $controller_name = StringCaseConverter::snakeCase($request->shortControllerName);
        $method_name = StringCaseConverter::snakeCase($request->methodName);

        return "{$controller_name}/{$method_name}";
    }

    /**
     * 指定の文字配列から、指定長のランダム文字列を生成する
     * @param int $length 0文字以上の要求文字数
     * @param string $charList 1文字以上のUTF-8文字列、ただし合成文字はサポートしない
     * @return string
     * @throws RuntimeException
     */
    public static function genRandomString(int $length = 16, string $charList = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ012345679_-'): string
    {
        if ($length < 0) throw new InvalidArgumentException('must be $length 0 or more');
        if (mb_strlen($charList, 'UTF-8') <= 0) throw new InvalidArgumentException('must be $charList length more than 0');

        $charList = preg_split("//u", $charList, 0, PREG_SPLIT_NO_EMPTY);
        $charListLen = count($charList);
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            try {
                $str .= $charList[random_int(0, $charListLen - 1)];
            } catch (Exception $e) {
                throw new RuntimeException("random_int thrown exception. {$e->getMessage()}");
            }
        }
        return $str;
    }

    /**
     * a-zA-Z0-9 範囲から、指定長のランダム文字列を生成する
     * @param int $length
     * @return string
     */
    public static function genRandomStringAlphaNum(int $length = 32): string
    {
        return static::genRandomString($length, 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ012345679');
    }
}
