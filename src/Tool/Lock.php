<?php


namespace Lark\Tool;

use Lark\Cache\CacheFactory;

class Lock
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \Lark\Cache\Cache
     */
    private $cache;
    
    public function __construct($name)
    {
        $this->name = $name;
        $this->cache = CacheFactory::create();
    }

    public function lock($expire=3600)
    {
    }
}
