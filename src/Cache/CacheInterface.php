<?php
namespace Lark\Cache;


/**
 * Cache Interface
 * @package Lark\Cache
 * @author kelezyb
 */
interface CacheInterface
{
    /**
     * @return mixed
     */
    public function connect();

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function set(string $key, $value);

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key);

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function call(string $method, array $params);
}