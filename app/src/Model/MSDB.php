<?php

namespace Fc2blog\Model;

use Fc2blog\Config;

class MSDB implements DBInterface
{
    private $master = null;

    // singleton pattern
    private static $instance = null;

    private function __construct()
    {
    }

    public static function getInstance(bool $rebuild = false)
    {
        if (self::$instance === null || $rebuild) {
            self::$instance = new MSDB();
        }
        return self::$instance;
    }

    /**
     * @return PDOWrap
     */
    private function getMasterDB(): PDOWrap
    {
        return $this->master = new PDOWrap(
            Config::get('MASTER_DB.HOST'),
            Config::get('MASTER_DB.PORT'),
            Config::get('MASTER_DB.USER'),
            Config::get('MASTER_DB.PASSWORD'),
            Config::get('MASTER_DB.DATABASE'),
            Config::get('DB_CHARSET')
        );
    }

    public function close()
    {
        $this->master->close();
        $this->master = null;
    }

    /**
     * 参照系SQL
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return array|false
     */
    public function find(string $sql, array $params = [], array $options = [])
    {
        $_options = array(
            'master' => false,                  // Masterから取得するかどうか
            'types' => '',                      // paramsの型設定(sdi)
            'result' => DBInterface::RESULT_ALL,  // 戻り値 one/row/all/statement...
        );
        $options = array_merge($_options, $options);
        $db = $this->getMasterDB();
        return $db->find($sql, $params, $options);
    }

    /**
     * 更新系SQL
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return false|int|mixed
     */
    public function execute(string $sql, array $params = [], array $options = [])
    {
        $default_options = [
            'types' => '',                             // paramsの型設定(sdi)
            'result' => DBInterface::RESULT_AFFECTED,  // 戻り値 one/row/all/statement...
        ];
        $options = array_merge($default_options, $options);
        $db = $this->getMasterDB();
        return $db->execute($sql, $params, $options);
    }

    /**
     * 複数の更新系SQL
     * @param $sql
     * @return bool
     */
    public function multiExecute($sql)
    {
        return $this->getMasterDB()->multiExecute($sql);
    }

    /**
     * 接続処理
     */
    public function connect()
    {
        $this->getMasterDB()->connect();
    }
}

