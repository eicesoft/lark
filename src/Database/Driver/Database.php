<?php
namespace Lark\Database\Driver;

use Lark\Pool\PoolTrait;

/**
 * Class Database
 * @package Lark\Database\Driver
 * @author 
 */
abstract class Database
{
    use PoolTrait;

    /**
     * @return mixed
     */
    public abstract function connect();

    /**
     * @param string $sql
     * @return \PDOStatement
     */
    public abstract function query($sql);

    /**
     * @param $sql
     * @return \PDOStatement
     */
    public abstract function prepare($sql, $params=[]);

    /**
     * @param $sql
     * @return string
     */
    public abstract function escape($sql);
    public abstract function begin();
    public abstract function commit();
    public abstract function rollback();
    public abstract function lastInsertId();

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
}