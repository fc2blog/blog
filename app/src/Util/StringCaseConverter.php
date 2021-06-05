<?php
declare(strict_types=1);

namespace Fc2blog\Util;

use InvalidArgumentException;

class StringCaseConverter
{
    /**
     * パスカルケースへの変換
     * @param string $snake_case
     * @return string
     */
    public static function pascalCase(string $snake_case): string
    {
        if (preg_match('/[^a-zA-Z0-9_ ]/u', $snake_case)) throw new InvalidArgumentException("contain non allowable string {$snake_case}");
        $snake_case = str_replace('_', ' ', $snake_case);
        $snake_case = ucwords($snake_case);
        return str_replace(' ', '', $snake_case);
    }

    /**
     * キャメルケースへの変換
     * @param string $snake_case
     * @return string
     */
    public static function camelCase(string $snake_case): string
    {
        if (preg_match('/[^a-zA-Z0-9_ ]/u', $snake_case)) throw new InvalidArgumentException("contain non allowable string {$snake_case}");
        return lcfirst(static::pascalCase($snake_case));
    }

    /**
     * スネークケースへの変換
     * @param string $camel_case
     * @return string
     */
    public static function snakeCase(string $camel_case): string
    {
        if (preg_match('/[^a-zA-Z0-9_ ]/u', $camel_case)) throw new InvalidArgumentException("contain non allowable string {$camel_case}");
        return strtolower(preg_replace("/([A-Z])/u", "_$0", lcfirst($camel_case)));
    }
}
