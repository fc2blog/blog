<?php

declare(strict_types=1);

namespace Fc2blog\Util;

class StringCaseConverter
{

  /**
   * パスカルケースへの変換
   * @param string $snake_case
   * @return string
   */
  public static function pascalCase(string $snake_case): string
  {
    $snake_case = str_replace('_', ' ', $snake_case);
    $snake_case = ucwords($snake_case);
    $snake_case = str_replace(' ', '', $snake_case);
    return $snake_case;
  }

  /**
   * キャメルケースへの変換
   * @param string $snake_case
   * @return string
   */
  public static function camelCase(string $snake_case): string
  {
    return lcfirst(static::pascalCase($snake_case));
  }

  /**
   * スネークケースへの変換
   * @param string $camel_case
   * @return string
   */
  public static function snakeCase(string $camel_case): string
  {
    return strtolower(preg_replace("/([A-Z])/u", "_$0", lcfirst($camel_case)));
  }
}
