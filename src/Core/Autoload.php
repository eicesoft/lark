<?php
namespace Lark\Core;


class Autoload
{
    private static $class_caches = [];

    /**
     * Load注册
     */
    public static function registry()
    {
        spl_autoload_register(['Lark\Core\Autoload', "autoload"], true);
    }

    /**
     * 自动加载文件
     */
    public static function autoload($className)
    {
        $className = str_replace("\\", "/", lcfirst($className));
        /** @var Lark $app*/
        $app = Kernel::Instance()->get('app');
        $classFile = $app->app_path() . DS . $className . '.php';
        
        if (is_readable($classFile)) {
            require $classFile;
        } else {
//            var_dump($className);
//            throw new Exception('load class [' . $className . '] exception', Exception::LOADERE_CLASS);
        }
    }
}