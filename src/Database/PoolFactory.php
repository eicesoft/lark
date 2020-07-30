<?php


namespace Lark\Database;


class PoolFactory
{
    private static $instances = [];

    /**
     * @param string $database
     * @return DatabasePool
     */
    public static function create(string $database='database')
    {
        if (!isset(self::$instances[$database])) {
            self::$instances[$database] = new DatabasePool($database);
        }

        return self::$instances[$database];
    }
}