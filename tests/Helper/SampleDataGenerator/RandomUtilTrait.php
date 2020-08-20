<?php
declare(strict_types=1);

namespace Fc2blog\Tests\Helper\SampleDataGenerator;

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
}
