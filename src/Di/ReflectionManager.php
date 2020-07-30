<?php
declare(strict_types=1);

namespace Lark\Di;


/**
 * Class ReflectionManager
 * @package Lark\Di
 * @author kelezyb
 */
class ReflectionManager
{
    /**
     * @var array
     */
    private static $container = [];

    /**
     * reflect class info
     * @param string $className
     * @return \ReflectionClass
     * @throws \InvalidArgumentException
     */
    public static function reflectClass(string $className): \ReflectionClass
    {
        if (!isset(self::$container['class'][$className])) {
            if (!class_exists($className) && !interface_exists($className)) {
                throw new \InvalidArgumentException("Class {$className} not exist");
            }

            self::$container['class'][$className] = new \ReflectionClass($className);

//            $x = new \ReflectionClass($className);
//            $c = $x->getConstructor();
//            if ($c) {
//                $params = $c->getParameters();
//                echo $x->name . ":";
//                foreach ($params as $param) {
//                    echo $param->name  .", ";
//                }
//                echo "\n";
//            }
        }

        return self::$container['class'][$className];
    }

    /**
     * Reflect methods
     * @param string $className
     * @return \ReflectionMethod[]
     * @throws \InvalidArgumentException
     */
    public static function reflectMethods(string $className, $filter=\ReflectionMethod::IS_PUBLIC)
    {
        if (! isset(self::$container['methods'][$className])) {
            if (! class_exists($className)) {
                throw new \InvalidArgumentException("Class {$className} not exist");
            }
            self::$container['methods'][$className] = self::reflectClass($className)->getMethods();
        }

        return self::$container['methods'][$className];
    }

    /**
     * Reflect propertys
     * @param string $className
     * @param int $filter
     * @return ReflectionProperty[]
     */
    public static function reflectPropertys(string $className, $filter=\ReflectionProperty::IS_PRIVATE)
    {
        if (! isset(static::$container['propertys'][$className])) {
            if (! class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['propertys'][$className] = static::reflectClass($className)->getProperties($filter);
        }
        return static::$container['propertys'][$className];
    }

    /**
     * Reflect constants
     * @param string $className
     * @return array
     * @throws \InvalidArgumentException
     */
    public static function reflectConstants(string $className)
    {
        if (! isset(static::$container['constants'][$className])) {
            if (! class_exists($className)) {
                throw new InvalidArgumentException("Class {$className} not exist");
            }
            static::$container['constants'][$className] = static::reflectClass($className)->getReflectionConstants();
        }
        return static::$container['constants'][$className];
    }

    /**
     * clear reflect cache
     */
    public function clear()
    {
        self::$container = [];
    }
}