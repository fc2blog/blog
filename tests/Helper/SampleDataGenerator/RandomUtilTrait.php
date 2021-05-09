<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

use Exception;
use InvalidArgumentException;
use RuntimeException;

trait RandomUtilTrait
{
    static public function getRandomValue(array $array)
    {
        $_array = $array;
        shuffle($_array);
        return $_array[0];
    }

    static public function getRandomKey(array $array)
    {
        $_array = $array;
        $_array = array_keys($_array);
        shuffle($_array);
        return $_array[0];
    }

    static public function getRandomColumn(array $array, string $column_key_name, $num = 5): array
    {
        shuffle($array);
        $return_list = [];

        $random_list = static::getRandomSlice($array, $num);

        while ($num-- > 0) {
            $row = static::getRandomSlice($random_list, 1)[0];
            if (!isset($row[$column_key_name])) {
                throw new InvalidArgumentException("missing column: {$column_key_name} from " . print_r($random_list, true));
            }
            $return_list[] = $random_list[$num][$column_key_name];
        }
        return $return_list;
    }

    static public function getRandomSlice(array $array, int $num): array
    {
        shuffle($array);
        $list = [];
        while ($num-- > 0) {
            $list = array_merge($list, array_slice($array, $num, 1));
        }
        return $list;
    }

    /**
     * @param $left_hand
     * @param $right_hand
     * @param int $percent
     * @return mixed
     * @throws RuntimeException
     */
    static public function getRandomChoice($left_hand, $right_hand, int $percent = 50)
    {
        try {
            if ($percent > random_int(0, 100)) {
                return $left_hand;
            } else {
                return $right_hand;
            }
        } catch (Exception $e) {
            throw new RuntimeException("random_int failed.");
        }
    }
}
