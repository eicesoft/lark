<?php

namespace Lark\Database\Driver;

//include_once 'MySQLResult.php';

use Lark\Event\EventManager;
use Lark\Event\Local\QueryEvent;

/**
 * MySQLi Driver
 * @package Lark\Database\Driver
 */
class MySQLIDatabase extends Database
{
    public function connect()
    {
        // TODO: Implement connect() method.
    }

    public function query($sql)
    {
        return mysqli_query($this->server, $sql);
    }

    /**
     * @var \mysqli
     */
    private $link;

    /**
     * @var array
     */
    private $options;

    public function __construct($options = [])
    {
        $this->server = mysqli_connect($options['host'], $options['user'], $options['password'],
            $options['database'], $options['port']);
        mysqli_set_charset($this->server, $options['charset']);
    }

    /**
     * @param $sql
     * @param array $params
     * @return MySQLResult
     * @throws \Exception
     */
    public function prepare($sql, $params = [])
    {
        try {
            $stmt = mysqli_stmt_init($this->server);
            if (!mysqli_stmt_prepare($stmt, $sql)) {
                throw new \Exception($stmt->error, $stmt->errno);
            }

            if (count($params) != 0) {
                mysqli_stmt_bind_param($stmt, str_repeat('s', count($params)), ...array_values($params));
            }

            EventManager::Instance()->trigger(new QueryEvent($sql, $params));
            $status = mysqli_stmt_execute($stmt);
            if ($status) {
                $result = mysqli_stmt_get_result($stmt);

                return new MySQLResult($result, $stmt);
            } else {
                throw new \Exception($stmt->error, $stmt->errno);
            }
        } catch (\Exception $ex) {
            $this->__discard();
            throw $ex;
        }
    }

    public function escape($sql)
    {
        return mysqli_escape_string($sql);
    }

    public function begin()
    {
        return mysqli_begin_transaction($this->server);
    }

    public function commit()
    {
        return mysqli_commit($this->server);
    }

    public function rollback()
    {
        return mysqli_rollback($this->server);
    }

    public function lastInsertId()
    {
        return mysqli_insert_id($this->server);
    }
}

//
//try {
//    $options = ['host' => '192.168.1.25',
//        'port' => 3306,
//        'user' => 'brand',
//        'password' => 'Hdgy3J$ssd12',
//        'database' => 'mall',
//        'charset' => 'utf8mb4'];
//    $mysql = new MySQLIDatabase($options);
////$result = $mysql->prepare("SELECT * FROM visit_plan WHERE type=? AND is_visit=?",
////    ['type' => 2, 'is_visit' => 1]);
////var_dump($result->fetchAll());
////var_dump($result->rowCount());
//
//
//    $result = $mysql->prepare("INSERT INTO tests(`name`) VALUES( ?)",
//        [ 'name' . mt_rand(0, 100)]);
//    var_dump($mysql->lastInsertId());
//} catch (\Exception $ex) {
//    var_dump($ex->getMessage());
//}
