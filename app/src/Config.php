<?php
/**
 * 設定クラス
 */

namespace Fc2blog;

use LogicException;

class Config
{
    private static $config = [];
    private static $read_files = [];

    /**
     * 設定を取得する
     *
     * @param string $key 取得用のキー(MASTER.DB => $config["MASTER"]["DB"] .区切りで取得できる)
     * @param null $default 未設定の場合に返却する値
     * @return mixed|null
     */
    public static function get(string $key, $default = null)
    {
        if (isset(self::$config[$key])) {
            return self::$config[$key];
        }
        $keys = explode('.', $key);
        if (count($keys) > 1 && isset(self::$config[$keys[0]])) {
            $value = self::$config[array_shift($keys)];
            foreach ($keys as $v) {
                if ($v === '' || !isset($value[$v])) {
                    return $default;
                }
                $value = $value[$v];
            }
            return $value;
        }
        return $default;
    }

    /**
     * @param string $key
     * @param $value
     * @deprecated can only be used for testing purposes
     */
    public static function set(string $key, $value)
    {
        if (!defined("THIS_IS_TEST")) {
            throw new LogicException("Config::set can only be used for testing purposes");
        }
        self::_set($key, $value, self::$config);
    }

    /**
     * @param string $key
     * @param string|int|null $value
     * @param array $config
     */
    private static function _set(string $key, $value, array &$config)
    {
        $keys = explode('.', $key);
        $count = count($keys);
        if ($count == 1) {
            $config[$key] = $value;
            return;
        }
        $key = array_shift($keys);
        self::_set(implode('.', $keys), $value, $config[$key]);
    }

    /**
     * ファイルから環境情報を設定
     * @param string $name
     * @param bool $force_reload UnitTest内などで再読み込みを強制したい場合に指定
     */
    public static function read(string $name, bool $force_reload = false)
    {
        if (!$force_reload && !empty(self::$read_files[$name])) {
            // 既に読み込み済みのファイルは読み込まないが、強制的に再読み込みの指定があれば読み込みする。
            return;
        }
        self::$read_files[$name] = true;
        /** @noinspection PhpIncludeInspection */
        $configs = include(App::CONFIG_DIR . $name);
        foreach ($configs as $key => $value) {
            self::$config[$key] = $value;
        }
    }
}
