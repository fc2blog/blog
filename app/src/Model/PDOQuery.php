<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use Fc2blog\Config;
use Fc2blog\Util\Log;
use PDO;
use PDOException;
use PDOStatement;

class PDOQuery
{
    // find等の挙動選択肢 TODO 削除し、別々のメソッドへ
    const RESULT_ONE = 'one';                // １カラムのみ取得
    const RESULT_ROW = 'row';                // １行のみ取得
    const RESULT_LIST = 'list';              // リスト形式で取得
    const RESULT_ALL = 'all';                // 全て取得
    const RESULT_STAT = 'statement';          // 結果ステートメントを返却する
    const RESULT_INSERT_ID = 'insert_id';    // AUTO_INCREMENTの値を返却
    const RESULT_AFFECTED = 'affected';      // 変更のあった行数を返却
    const RESULT_SUCCESS = 'success';        // SQLの実行結果が成功かどうかを返却

    /**
     * 参照系SQLの実行
     * @param PDO $pdo
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return array|int
     * @throw PDOException
     */
    public static function find(PDO $pdo, string $sql, array $params = [], array $options = [])
    {
        $options = array_merge(['result' => static::RESULT_ALL], $options);
        try {
            $stmt = static::query($pdo, $sql, $params);
        } catch (PDOException $e) {
            Log::error("DB find error " . $e->getMessage() . " {$sql}", [$params]);
            throw $e;
        }
        return static::result($pdo, $stmt, $options['result']);
    }

    /**
     * 更新系SQLの実行
     * @param PDO $pdo
     * @param string $sql
     * @param array $params
     * @param array $options
     * @return array|int
     */
    public static function execute(PDO $pdo, string $sql, array $params = [], array $options = [])
    {
        $options = array_merge(['result' => static::RESULT_SUCCESS], $options);
        try {
            $stmt = static::query($pdo, $sql, $params);
        } catch (PDOException $e) {
            Log::error("DB execute error " . $e->getMessage() . " {$sql}", [$params]);
            throw $e;
        }
        return static::result($pdo, $stmt, $options['result']);
    }

    /**
     * 複数行となるSQLの実行
     * @param PDO $pdo
     * @param $sql
     * @return bool
     */
    public static function multiExecute(PDO $pdo, $sql): bool
    {
        $sql = preg_replace('/^--.*?\n/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $sql_list = explode(';', $sql);
        foreach ($sql_list as $sql) {
            if (trim($sql) === '') continue; // 空クエリならスキップ
            static::execute($pdo, $sql);
        }
        return true;
    }

    /**
     * PDOでSQLの実行
     * @param PDO $pdo
     * @param $sql
     * @param array $params
     * @return false|PDOStatement 成功時PDOStatement
     */
    private static function query(PDO $pdo, $sql, array $params = [])
    {
        if (Config::get('SQL_DEBUG', 0)) {
            $mtime = microtime(true);
        }

        if (!count($params)) {
            $stmt = $pdo->query($sql);
        } else {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
        }

        if (isset($mtime) && Config::get('SQL_DEBUG', 0)) {
            $mtime = sprintf('%0.2fms', (microtime(true) - $mtime) * 1000);
            Log::debug_log("SQL {$mtime} {$sql}", ['params' => $params]);
        }
        return $stmt;
    }

    /**
     * 結果内容の変換 TODO いつか削除する
     * @param PDO $pdo
     * @param $stmt
     * @param $type
     * @return array|int $typeによって様々な意味の返り値となる
     */
    public static function result(PDO $pdo, $stmt, $type)
    {
        if ($stmt === false) {
            return [];
        }

        switch ($type) {
            // １カラムのみ取得
            case static::RESULT_ONE :
                return $stmt->fetchColumn();

            // １行のみ取得
            case static::RESULT_ROW :
                return $stmt->fetch();

            // リスト形式で取得
            case static::RESULT_LIST :
                $rows = [];
                $stmt->setFetchMode(PDO::FETCH_NUM);
                foreach ($stmt as $value) {
                    $rows[$value[0]] = $value[1];
                }
                return $rows;

            // 全て取得
            case static::RESULT_ALL :
                return $stmt->fetchAll();

            // InsertIDを返却
            case static::RESULT_INSERT_ID :
                return $pdo->lastInsertId();

            // 影響のあった行数を返却
            case static::RESULT_AFFECTED :
                return $stmt->rowCount();

            // 成功したかどうかを返却
            case static::RESULT_SUCCESS :
                return 1;

            case static::RESULT_STAT:
            default:
                return $stmt;
        }
    }
}
