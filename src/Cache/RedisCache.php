<?php
namespace Lark\Cache;


use Lark\Core\Kernel;

/**
 * Class RedisCache
 * @package Lark\Cache
 */
class RedisCache extends Cache
{
    private $handler;

    /**
     * RedisCache constructor.
     * @param $params
     */
    public function __construct($params)
    {
        parent::__construct($params);
        $this->handler = new \Redis();
    }

    /**
     * @return mixed|void
     */
    public function connect()
    {
        $this->handler->connect($this->params['host'], $this->params['port']);
        if (isset($this->params['auth']) && !empty($this->params['auth'])) {
            $this->handler->auth($this->params['auth']);
        }
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param mixed $param
     * @return bool|mixed
     */
    public function set(string $key, $value, $param=null)
    {
        return $this->handler->set($key, $value, $param);
    }

    /**
     * @param string $key
     * @return bool|mixed|string
     */
    public function get(string $key)
    {
        return $this->handler->get($key);
    }

    /**
     * @param string $method
     * @param array $params
     * @return mixed
     * @throws \Lark\Core\Exception
     */
    public function call(string $method, array $params)
    {
        return Kernel::Instance()->call($this->handler, $method, $params);
    }
}