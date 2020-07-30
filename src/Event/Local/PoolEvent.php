<?php
namespace Lark\Event\Local;


use Lark\Event\Event;
use Lark\Pool\ObjectPool;


/**
 * Pool Event
 * @package Lark\Event\Local
 * @author kelezyb
 */
class PoolEvent implements Event
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var ObjectPool
     */
    private $pool;

    /**
     * PoolEvent constructor.
     * @param string $type
     */
    public function __construct(string $type, ObjectPool $pool)
    {
        $this->type = $type;
        $this->pool = $pool;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return ObjectPool
     */
    public function getPool(): ObjectPool
    {
        return $this->pool;
    }

    public function getName()
    {
        return "Object Pool event";
    }

    public function __toString()
    {
        $pool_name = get_class($this->pool);

        $stats = json_encode($this->pool->stats());
        return <<<DESC
Pool {$pool_name} Event {  type: {$this->type},  stats: {$stats}  }
DESC;

    }
}