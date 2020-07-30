<?php
namespace Lark\Database\Driver;


use Lark\Core\Kernel;
use Lark\Event\EventManager;
use Lark\Event\Local\QueryEvent;
use Lark\Tool\Timing;

/**
 * Class MySQLDatabase
 * @package Lark\Database
 */
class MySQLDatabase extends Database
{
    /**
     * @var \PDO
     */
    private $server;

    /**
     * @var array
     */
    private $options;

//    private $

    public function __construct($options=[])
    {
        $dns = sprintf("mysql:host=%s;dbname=%s;port=%s", $options['host'], $options['database'], $options['port']);
        $alertcommd = sprintf("set NAMES '%s'", $options["charset"]);
		$params = [
            \PDO::ATTR_CASE => \PDO::CASE_NATURAL,
			\PDO::MYSQL_ATTR_INIT_COMMAND => $alertcommd,
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
		];

		$this->server = new \PDO($dns, $options["user"], $options["password"], $params);
    }

    public function connect()
    {
//        return $this->server->connect($this->options);
    }

    /**
     * @param string $sql
     * @return \PDOStatement
     */
    public function query($sql)
    {
        try {
            return $this->server->query($sql);
        } catch (\Exception $ex) {
            $this->__discard();
            throw $ex;
        }
    }

    /**
     * @param $sql
     * @param array $params
     * @return bool|mixed|\PDOStatement
     * @throws \Exception
     */
    public function prepare($sql, $params=[])
    {
        try {
            EventManager::Instance()->trigger(new QueryEvent($sql, $params));
            $stmt = $this->server->prepare($sql);
            if ($stmt !== false) {
                $stmt->execute($params);
//            Timing::Instance()->add("dbquery");
                return $stmt;
            } else {
                throw new \Exception($this->server->error, $this->server->connect_errno);
            }
        } catch (\Exception $ex) {
            $this->__discard();
            throw $ex;
        }
    }

    public function escape($sql)
    {
        return $this->server->prepare($sql);
    }

    public function begin()
    {
        return $this->server->begin();
    }

    public function commit()
    {
        return $this->server->commit();
    }

    public function rollback()
    {
        return $this->server->rollback();
    }

    public function lastInsertId()
    {
        return $this->server->lastInsertId();
    }
}