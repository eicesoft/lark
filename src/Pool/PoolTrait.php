<?php
namespace Lark\Pool;


/**
 * Class PoolTrait
 * @package Lark\Pool
 * @author kelezyb
 */
trait PoolTrait
{
    /**
     * @var ObjectPool
     */
    public $pool;

    /**
     * 丢弃连接
     * @return bool
     */
    public function __discard()
    {
        if (isset($this->pool)) {
            return $this->pool->discard($this);
        }
        return false;
    }

    /**
     * 归还连接
     * @return bool
     */
    public function __return()
    {
        if (isset($this->pool)) {
            return $this->pool->return($this);
        }
        return false;
    }
}