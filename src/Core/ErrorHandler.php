<?php
namespace Lark\Core;


use Lark\Event\EventManager;
use Lark\Event\Local\ExceptionEvent;

/**
 * Class ErrorHandler
 * @package Lark\Core
 * @author kelezyb
 */
class ErrorHandler
{
    /**
     * 异常错误事件处理
     */
    public static function registry()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
            self::error_handler($errno, $errstr, $errfile, $errline, $errcontext);
        });

        register_shutdown_function(function (){
            //Fatal error handler
            $error = error_get_last();
            if ($error) {
                EventManager::Instance()->trigger(new ExceptionEvent(new \Exception($error['message'], 50000)));
            }
        });
    }

    public static function error_handler($errno, $errstr, $errfile, $errline, $errcontext) {
        Console::error("Message(%s): %s - %s(%s)", [$errno, $errstr, $errfile, $errline]);
        throw new \Exception($errstr, $errno);
    }
}