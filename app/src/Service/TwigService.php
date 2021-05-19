<?php
declare(strict_types=1);

namespace Fc2blog\Service;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigService
{
    const TWIG_TEMPLATE_BASE_PATH = __DIR__ . "/../../twig_templates/";

    public static function getTwigBasePath(): string
    {
        return static::TWIG_TEMPLATE_BASE_PATH;
    }

    public static function getTwigInstance(): Environment
    {
        $loader = new FilesystemLoader(static::getTwigBasePath());
        return new Environment($loader);
    }
}
