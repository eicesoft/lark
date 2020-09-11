<?php

namespace Lark\Routing;


use Lark\Cache\CacheFactory;

/**
 * Class Session
 * @package Lark\Routing
 * @author kelezyb
 */
class Session implements \ArrayAccess
{
    const SESSION_ID = 'session_id';
    private $session_id;
    private $key;
    private $cache;

    /**
     * Session constructor.
     * @param Request $request
     */
    public function __construct($session_id)
    {
        $this->session_id = $session_id;
        $this->key = 'SESS:' . $this->session_id;
        $this->cache = CacheFactory::create();
    }

    public function offsetExists($offset)
    {
        return $this->cache->call('hexists', [$this->key, $offset]);
    }

    public function offsetGet($offset)
    {
        return json_decode($this->cache->call('hget', [$this->key, $offset]), true);
    }

    public function offsetSet($offset, $value)
    {
        return $this->cache->call('hset', [$this->key, $offset, json_encode($value)]);
    }

    public function offsetUnset($offset)
    {
        return $this->cache->call('hdel', [$this->key, $offset]);
    }
}