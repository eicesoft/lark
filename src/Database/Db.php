<?php

namespace Lark\Database;


use Lark\Database\Driver\Database;
use Swoole\Coroutine\MySQL\Statement;

/**
 * Db simple component
 * @package Lark\Database
 * @author kelezyb
 */
class Db
{
    /**
     * @var Database
     */
    private $database;

    /**
     * @var string
     */
    private $table = null;

    /**
     * @var string
     */
    private $fields = '*';

    /**
     * @var string[]|null
     */
    private $wheres = null;

    /**
     * @var int[]|null
     */
    private $limits = null;

    /**
     * @var string[]|null
     */
    private $sorts = null;

    /**
     * @var string|null
     */
    private $group = null;

    /**
     * @var string
     */
    private $database_name;

    /**
     * Db constructor.
     * @param string $database
     */
    public function __construct(string $database = 'database')
    {
        $this->database($database);
    }

    /**
     * @param string $database
     * @return $this
     */
    public function database(string $database)
    {
//        $this->database = bean($database);
        if ($this->database_name != $database) {
            $this->database = PoolFactory::create($database)->borrow();
//            $this->database = bean($database);
        }

        $this->database_name = $database;

        return $this;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function table(string $table): Db
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @param string $fields
     * @return $this
     */
    public function fields(string $fields): Db
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param array $wheres
     * @return $this
     */
    public function where(array $wheres = null): Db
    {
        $this->wheres = $wheres;
        return $this;
    }

    public function group($group): Db
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param array $limits
     * @return $this
     */
    public function limit(array $limits = null): Db
    {
        $this->limits = $limits;
        return $this;
    }

    public function sort(array $sorts = null): Db
    {
        $this->sorts = $sorts;
        return $this;
    }

    private function select_builder()
    {
        $sql = sprintf("SELECT %s FROM %s", $this->fields, $this->table);
        $s_where = '';
        if ($this->wheres !== null && !empty($this->wheres)) {
            $s_where = ' WHERE ' . join(' AND ', $this->wheres);
        }
        $sql .= $s_where;

        $s_group = '';
        if ($this->group !== null) {
            $s_group = ' GROUP BY ' . $this->group;
        }

        $s_sort = '';
        if ($this->sorts !== null) {
            $s_sort = ' ORDER BY ' . join(',', $this->sorts);
        }
        $sql .= $s_sort;

        $s_limit = '';
        if ($this->limits !== null) {
            $s_limit = ' LIMIT ' . join(',', $this->limits);
        }
        $sql .= $s_limit;

        return $sql;
    }

    private function reset()
    {
        $this->table = null;
        $this->fields = '*';
        $this->wheres = null;
        $this->sorts = null;
        $this->limits = null;
    }

    /**
     * @return array|mxied|null
     */
    public function get(array $params = [])
    {
        $sql = $this->select_builder();
        $this->reset();
        return $this->query($sql, $params);
    }

    public function one(array $params = [])
    {
        $sql = $this->select_builder();
        $this->reset();
        return $this->query($sql, $params, true);
    }

    /**
     * @param array $data
     * @param bool $lastId
     * @return int
     */
    public function insert(array $data, $lastId = false)
    {
        $fields = [];
        $values = [];
        $value_placeholders = [];
        foreach ($data as $key => $value) {
            $fields[] = "`$key`";
            $value_placeholders[] = '?';
            $values[] = $value;
        }
        $sql = sprintf("INSERT INTO %s(%s) VALUES(%s)",
            $this->table,
            join(',', $fields),
            join(',', $value_placeholders)
        );
        $this->reset();
        return $this->execute($sql, $values, $lastId);

    }

    public function inserts(array $datas)
    {
        $values = [];
        $value_placeholders = [];

        foreach ($datas as $data) {
            $fields = [];
            $placeholders = [];

            foreach ($data as $key => $val) {
                $fields[] = "`$key`";
                $placeholders[] = '?';
                $values[] = $val;
            }

            $value_placeholder = "(" . join(',', $placeholders) . ')';
            $value_placeholders[] = $value_placeholder;
        }

        $sql = sprintf("INSERT INTO %s(%s) VALUES %s",
            $this->table,
            join(',', $fields),
            join(',', $value_placeholders)
        );
        $this->reset();
        return $this->execute($sql, $values);
    }

    /**
     * @param array $data
     * @return int
     */
    public function update(array $data)
    {
        $params = [];
        $sets = [];
        $wheres = [];
        foreach ($data as $key => $val) {
            $sets[] = "`{$key}`=?";
            $params[] = $val;
        }

        $sql = sprintf("UPDATE %s SET %s WHERE %s", $this->table, join(',', $sets), join(' AND ', $this->wheres));
        $this->reset();
        return $this->execute($sql, $params);
    }

    /**
     * @param array $params
     * @return int
     */
    public function delete(array $params)
    {
        $sql = sprintf("DELETE FROM %s WHERE %s", $this->table, join(' AND ', $this->wheres));
        $this->reset();

        return $this->execute($sql, $params);
    }

    /**
     * @param array $params
     * @return int|mixed|null
     */
    public function count(array $params = [])
    {
        $this->fields("Count(0) as C");
        $sql = $this->select_builder();
        $this->reset();
        $data = $this->query($sql, $params, true);
        if ($data) {
            return $data['C'] ?? 0;
        } else {
            return null;
        }
//        /** @var Statement $stmt */
//        $stmt = $this->database->prepare($sql, $params);
//
//        if ($stmt) {
//            $data = $stmt->fetch();
//            return $data['C'] ?? 0;
//        } else {
//            return null;
//        }
    }

    /**
     * query data
     * @param string $sql
     * @return mixed
     */
    public function select(string $sql): mixed
    {
        return $this->database->query($sql);
    }

    /**
     * @param string $sql
     * @param array $params
     * @param bool $one
     * @return mixed
     */
    public function query(string $sql, array $params, $one = false)
    {
        $stmt = $this->database->prepare($sql, $params);

        if ($stmt) {
            if ($one) {
                $datas = $stmt->fetch();
            } else {
                $datas = $stmt->fetchAll();
            }

            return $datas;
        } else {
            return null;
        }
    }

    /**
     * @param string $sql
     * @param array $params
     * @return int
     */
    public function execute(string $sql, array $params, bool $lastId = false): int
    {
        $stmt = $this->database->prepare($sql, $params);
        if ($stmt->errorCode() == 0) {
            if ($lastId) {
                return $this->database->lastInsertId();
            } else {
                return $stmt->rowCount();
            }
        } else {
            return -1;
        }
    }

    /**
     * @return mixed
     */
    public function begin()
    {
        return $this->database->begin();
    }

    /**
     * @return mixed
     */
    public function commit()
    {
        return $this->database->commit();
    }

    /**
     * @return mixed
     */
    public function rollback()
    {
        return $this->database->rollback();
    }

    public function __return()
    {
        $this->database->__return();
    }
}