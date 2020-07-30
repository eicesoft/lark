<?php
namespace Lark\Cache\Pool;

use Lark\Cache\Cache;
use Lark\Database\DatabaseDialer;
use Lark\Pool\ObjectPool;

/**
 * Class CachePool
 * @package Lark\Cache\Pool
 * @author kelezyb
 */
class CachePool extends ObjectPool
{
    /**
     * CachePool constructor.
     */
    public function __construct()
    {
        $this->dialer = new CacheDialer();
        parent::__construct();
    }

    /**
     * @return Cache
     */
    public function borrow()
    {
        return parent::borrow();
    }
}