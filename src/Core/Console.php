<?php
namespace Lark\Core;


/**
 * Console
 * @package Lark\Core
 * @author kelezyb
 */
class Console
{
    const DEBUG = 0;
    const INFO = 1;
    const WARN = 2;
    const ERROR = 3;

    private static $labels = [
        self::DEBUG => "\033[0;37;40m[DEBUG]\033[0m",
        self::INFO => "\033[1;32;40m[INFO]\033[0m",
        self::WARN => "\033[1;31;43m[WARN]\033[0m",
        self::ERROR => "\033[1;32;41m[ERROR]\033[0m",
    ];

    /**
     * @param string $message
     * @param array $params
     */
    public static function debug($message, $params = [])
    {
        self::log($message, $params, self::DEBUG);
    }

    /**
     * @param string $message
     * @param array $params
     */
    public static function info($message, $params = [])
    {
        self::log($message, $params, self::INFO);
    }

    /**
     * @param string $message
     * @param array $params
     */
    public static function warn($message, $params = [])
    {
        self::log($message, $params, self::WARN);
    }

    /**
     * @param string $message
     * @param array $params
     */
    public static function error($message, $params = [])
    {
        self::log($message, $params, self::ERROR);
    }

    /**
     * @param string $message
     * @param array $params
     * @param int $level
     */
    private static function log($message, $params = [], $level = self::DEBUG)
    {
        if (php_sapi_name() != 'fpm-fcgi') {
            $label = self::$labels[$level];
            echo sprintf("%s: %s\n", $label, vsprintf($message, $params));
        }
    }
}