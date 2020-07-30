<?php
namespace Lark\Cache;


use Lark\Cache\Pool\CachePool;

/**
 * Class CacheFactory
 * @package Lark\Cache
 */
class CacheFactory
{
    /**
     * @var CachePool
     */
    private static $pool = null;

    private static $conns = [];

    /**
     * Create new cache connection
     * @return Cache
     */
    public static function create()
    {
        if (null == self::$pool) {
            self::$pool = new CachePool();
        }

        $conn = self::$pool->borrow();
        $id = spl_object_hash($conn);
        self::$conns[$id] = $conn;
        return $conn;
    }

    /**
     * reset cache connection
     */
    public static function reset()
    {
        foreach(self::$conns as $key => $conn) {
            $conn->close();
        }
        self::$conns = [];
    }
}