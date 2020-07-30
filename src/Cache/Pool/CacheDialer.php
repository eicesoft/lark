<?php
namespace Lark\Cache\Pool;


use Lark\Cache\Cache;
use Lark\Pool\DialerInterface;

/**
 * Cache Dialer
 * @package Lark\Cache\Pool
 * @author kelezyb
 */
class CacheDialer implements DialerInterface
{
    /**
     * @return Cache
     */
    public function dial()
    {
        $connection = bean('cache');
        $connection->connect();

        return $connection;
    }
}