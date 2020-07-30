<?php
namespace Lark\Core;

use Closure;
use Swoole\Coroutine;

/**
 * Class Context
 * @package Lark\Core
 */
class Context
{
    protected static $pool = [];

    /**
     * 协程执行处理异常.
     *
     * @param $function
     */
    protected static function go(Closure $function): void
    {
        if (php_sapi_name() == 'fpm-fcgi') {
            $function();
        } else {
            if (-1 !== Coroutine::getuid()) {
                $pool = self::$pool[Coroutine::getuid()] ?? false;
            } else {
                $pool = false;
            }

            go(function () use ($function, $pool) {
                try {
                    if ($pool) {
                        self::$pool[Coroutine::getuid()] = $pool;
                    }
                    $function();
                    if ($pool) {
                        unset(self::$pool[Coroutine::getuid()]);
                    }
                } catch (\Exception $ex) {
//                Console::error($ex->getMessage());
                }
            });
        }
    }
}