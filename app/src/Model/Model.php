<?php
declare(strict_types=1);

namespace Fc2blog\Model;

use Fc2blog\Util\Log;

abstract class Model
{
    use QueryTrait;

    private static $loaded = [];
    public $validates = [];

    /** @var static */
    public static $instance;

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 複合キーのAutoIncrement対応
     */
    protected function getAutoIncrementCompositeKey(): string
    {
        return "";
    }

    /**
     * 入力チェック処理
     * @param array $data 入力データ
     * @param ?array &$valid_data 入力チェック後の返却データ
     * @param array $white_list 入力のチェック許可リスト
     * @return array
     */
    public function validate(array $data, ?array &$valid_data = [], array $white_list = []): array
    {
        $errors = [];
        $valid_data = [];

        $isNeedWhiteListCheck = count($white_list) > 0;
        foreach ($this->validates as $key => $valid) {
            // カラムのホワイトリストチェック
            if ($isNeedWhiteListCheck && !in_array($key, $white_list)) {
                continue;
            }
            foreach ($valid as $mKey => $options) {
                $method = (is_array($options) && isset($options['rule'])) ? $options['rule'] : $mKey;
                if (!isset($data[$key])) {
                    $data[$key] = null;
                }
                $error = Validate::$method($data[$key], $options, $key, $data, $this);
                if ($error === false) {
                    break;
                }
                if (getType($error) === 'string') {
                    $errors[$key] = $error;
                    break;
                }
            }
            if (isset($data[$key])) {
                $valid_data[$key] = $data[$key];
            }
        }

        return $errors;
    }

    /**
     * Modelをロードする(getInstance)
     * @param string $model
     * @return mixed
     * @deprecated
     */
    public static function load(string $model)
    {
        $model = "\\Fc2blog\\Model\\" . $model . 'Model';
        if (empty(self::$loaded[$model])) {
            self::$loaded[$model] = new $model;
        }
        return self::$loaded[$model];
    }

    /**
     * 配列内が全て数値型かチェック
     * @param array $array
     * @return bool
     */
    public function is_numeric_array(array $array): bool
    {
        // 配列チェック
        if (!is_array($array)) {
            return false;
        }
        // 空配列チェック
        if (!count($array)) {
            return false;
        }
        // 数値チェック
        foreach ($array as $value) {
            if (!is_numeric($value)) {
                return false;
            }
        }
        return true;
    }

    /**
     * ページング用のデータ取得
     * @param array $options
     * @return array
     */
    public function getPaging(array $options = []): array
    {
        $count = $this->getFoundRows($options);

        if (!isset($options['page']) || !isset($options['limit'])) {
            Log::error('getPaging options["page"] or options["limit"]が設定されておりません');
            return [];
        }

        $page = $options['page'];
        $limit = $options['limit'];

        $pages = [];
        $pages['count'] = $count;
        $pages['max_page'] = ceil($count / $limit);
        $pages['page'] = $page;
        $pages['is_next'] = $page < $pages['max_page'] - 1;
        $pages['is_prev'] = $page > 0;
        $pager_range = 3;
        $pages['start'] = max($pages['page'] - $pager_range, 0);
        $pages['end'] = min($pages['page'] + $pager_range + 1, $pages['max_page']);
        return $pages;
    }

    /**
     * ページングのリストを表示する
     * @param array $paging
     * @return array
     */
    public static function getPageList(array $paging): array
    {
        $pages = [];
        $pages[0] = '1' . __(' page');
        for ($i = 1; $i < $paging['max_page']; $i++) {
            $pages[$i] = ($i + 1) . __(' page');
        }
        return $pages;
    }
}
