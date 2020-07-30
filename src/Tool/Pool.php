<?php

namespace Lark\Tool;
//include "IPool.php";

/**
 * Object pool
 * @package Lark\Core
 * @author kelezyb
 */
class Pool implements IPool
{


    public const DEFAULT_SIZE = 400;

    /**
     * object pool
     * @var \SplQueue
     */
    protected $pool;

    /**
     * pool
     * @var int
     */
    protected $num;

    /**
     * pool size
     * @var int
     */
    protected $size;

    /** @var callable */
    protected $constructor;

    /**
     * Pool constructor.
     * @param callable $constructor
     * @param int $size
     */
    public function __construct(callable $constructor, $size = self::DEFAULT_SIZE)
    {
        $this->pool = new \SplQueue();
        $this->size = $size;
        $this->num = 0;
        $this->constructor = $constructor;
    }

    /**
     * @throws \Throwable
     */
    private function make()
    {
        $this->num++;
        try {
            $constructor = $this->constructor;
            $connection = $constructor();
        } catch (\Throwable $throwable) {
            $this->num--;
            throw $throwable;
        }
        $this->pool->enqueue($connection);
    }

    /**
     * fill pool
     * @throws \Throwable
     */
    public function fill()
    {
        while ($this->size < $this->num) {
            $this->make();
        }
    }


    /**
     * @return mixed
     * @throws \Throwable
     */
    public function get()
    {
        if ($this->pool === null) {
            throw new \RuntimeException('Pool has been closed');
        }
        if ($this->pool->isEmpty() && $this->num < $this->size) {
            $this->make();
        }

        if ($this->pool->count()> 0) {
            return $this->pool->dequeue();
        } else {
            throw new \RuntimeException('Pool is empty');
        }
    }

    /**
     * release object to pool
     * @param mixed $object
     */
    public function release($object): void
    {
        if ($this->pool === null) {
            return;
        }

        if ($object !== null) {
            $this->pool->enqueue($object);
        } else {
            /* connection broken */
            $this->num -= 1;
        }
    }

    public function close(): void
    {
        $this->pool = null;
        $this->num = 0;
    }

    /**
     * @return int
     */
    public function getNum()
    {
        return $this->num;
    }
}

//
//class O
//{
//    private $id;
//
//    public function __construct($id)
//    {
//        $this->id = $id;
//    }
//}
//
//$pool = new Pool(function () {
//    return new O(mt_rand(1, 100));
//});
//
//for ($i = 0; $i < 125; $i++) {
//    $o = $pool->get();
//    var_dump($pool->getNum());
////    $pool->releace($o);
//}

//var_dump($pool);