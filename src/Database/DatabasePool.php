<?php
namespace Lark\Database;

use Lark\Database\Driver\Database;
use Lark\Pool\ObjectPool;

/**
 * Class DatabasePool
 * @package Lark\Database
 * 
 */
class DatabasePool extends ObjectPool
{
    /**
     * DatabasePool constructor.
     * @param string $name
     */
    public function __construct(string $name='database')
    {
        $this->dialer = new DatabaseDialer($name);
        parent::__construct();
    }

    /**
     * @return Database
     */
    public function borrow()
    {
        /** @var Database $database */
        $database = parent::borrow();
        $database->pool = $this;
        return $database;
    }
}