<?php

declare(strict_types=1);

namespace Fc2blog\Util;

use PhpParser\Error;
use PhpParser\ParserFactory;

class PhpCodeLinter
{
    /**
     * 文字列がPHPのコードとして正しいかLintする
     * @param $string
     * @return bool
     */
    public static function isParsablePhpCode($string): bool
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        try {
            $parser->parse($string);
        } catch (Error $error) {
            return false;
        }
        return true;
    }
}
