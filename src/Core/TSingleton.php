<?php
namespace Lark\Core;


/**
 * Trait TSingleton
 * @package Lark\Core
 * @author kelezyb
 */
trait TSingleton
{
    private static $singleton = null;

    /**
     * @return mixed
     */
    public static function getInstance()
    {
        static $instances;

        $called_class = get_called_class();

        if (!isset($instances[$called_class])) {
            $instances[$called_class] = new $called_class();
        }
        return $instances[$called_class];
    }
}