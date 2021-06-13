<?php

declare(strict_types=1);

namespace Fc2blog\Util;

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class Log
{
    static $logger;
    static $logLevel = Logger::DEBUG;

    public static function getLogger(): LoggerInterface
    {
        if (!isset(static::$logger)) {
            if (defined('APP_LOG_LEVEL') && APP_LOG_LEVEL > 0) {
                static::$logLevel = APP_LOG_LEVEL;
            }

            $log = new Logger('log');
            if (defined('APP_LOG_PATH') && strlen(APP_LOG_PATH) > 0) {
                $log->pushHandler(new StreamHandler(APP_LOG_PATH, static::$logLevel));
            } else {
                $log->pushHandler(new NullHandler(static::$logLevel));
            }
            static::$logger = $log;
        }
        return static::$logger;
    }

    /**
     * @param string $message
     * @param array $context
     */
    public static function debug_log(string $message, array $context = []): void
    {
        static::getLogger()->debug($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public static function error(string $message, array $context = []): void
    {
        static::getLogger()->error($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public static function notice(string $message, array $context = []): void
    {
        static::getLogger()->notice($message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public static function debug(string $message, array $context = []): void
    {
        static::getLogger()->debug($message, $context);
    }

}
