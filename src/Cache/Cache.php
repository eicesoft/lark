<?php
namespace Lark\Cache;

use Lark\Pool\PoolTrait;

/**
 * Class Cache
 * @package Lark\Cache
 * @author kelezyb
 */
abstract class Cache implements CacheInterface
{
    use PoolTrait;

    /**
     * @var array
     */
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
     * return to pool
     */
    public function close()
    {
        $this->__return();
    }

    /**
     * 释放连接
     */
    public function __destruct()
    {
        $this->__return();
    }

    /**
     * @return mixed
     */
    public abstract function connect();

    /**
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public abstract function set(string $key, $value, $param=null);

    /**
     * @param string $key
     * @return mixed
     */
    public abstract function get(string $key);

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public abstract function call(string $method, array $params);
}