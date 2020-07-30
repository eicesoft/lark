<?php
namespace Lark\Database;


use Lark\Database\Driver\Database;

/**
 * Database model helper class
 * @package Lark\Database
 * @author kelezyb
 * @deprecated
 */
class Model
{
    /**
     * @var Database
     */
    private $database;

    /**
     * Database table name
     * @var string
     */
    private $table;

    /**
     * Model constructor.
     */
    public function __construct($table='', $database='database')
    {
        $this->database = \bean($database);
        $this->table = $table;
    }

    /**
     * Insert new record
     * @param array $data
     * @return mixed
     */
    public function add($data)
    {
        $fields = [];
        $values = [];
        $value_placeholders = [];
        foreach($data as $key => $value) {
            $fields[] = "`$key`";
            $value_placeholders[] = '?';
            $values[] = $value;
        }
        $sql = sprintf("INSERT INTO %s(%s) VALUES(%s)",
            $this->table,
            join(',', $fields),
            join(',', $value_placeholders)
        );

        return $this->execute($sql, $values);
    }

    /**
     * Remove data
     * @param array $where
     * @return mixed
     */
    public function delete($where)
    {
        $params = [];
        $wheres = [];
        foreach($where as $key => $val) {
            $wheres[] = "`{$key}`=?";
            $params[] = $val;
        }
        $sql = sprintf("DELETE FROM %s WHERE %s", $this->table, join(' AND ', $wheres));
        return $this->execute($sql, $params);
    }

    /**
     * Update data
     * @param array $data
     * @param array $where
     * @return mixed
     */
    public function update($data, $where)
    {
        $params = [];
        $sets = [];
        $wheres = [];
        foreach($data as $key => $val) {
            $sets[] = "`{$key}`=?";
            $params[] = $val;
        }

        foreach($where as $key => $val) {
            $wheres[] = "`{$key}`=?";
            $params[] = $val;
        }
        $sql = sprintf("UPDATE %s SET %s WHERE %s", $this->table, join(',', $sets), join(' AND ', $wheres));
        return $this->execute($sql, $params);
    }

    /**
     * @param array $where
     * @param array $sort
     * @param array $limit
     */
    public function finds($where, $sort=null, $limit=null) {
        $params = [];
        $wheres = [];

        foreach($where as $key => $val) {
            $wheres[] = "`{$key}`=?";
            $params[] = $val;
        }

        if ($where) {
            $strWhere = sprintf(" WHERE %s", join(' AND ', $wheres));
        } else {
            $strWhere = '';
        }

        $sql = sprintf("SELECT * FROM %s %s", $this->table, $strWhere);
        if ($sort !== null) {
            $sql .= ' ORDER BY ' . join(',', $sort);
        }

        if ($limit !== null) {
            $sql .= ' LIMIT ' . join(',', $limit);
        }

        $stmt = $this->database->prepare($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * count record
     * @param array $where
     * @return mixed
     */
    public function count($where) {
        $params = [];
        $wheres = [];

        foreach($where as $key => $val) {
            $wheres[] = "`{$key}`=?";
            $params[] = $val;
        }

        if ($where) {
            $strWhere = sprintf(" WHERE %s", join(' AND ', $wheres));
        } else {
            $strWhere = '';
        }

        $sql = sprintf("SELECT Count(0) as C FROM %s %s", $this->table, $strWhere);

        $stmt = $this->database->prepare($sql, $params);
        return $stmt->fetch();
    }

    /**
     * query sql
     * @param string $sql
     * @param array $params
     * @param bool $isone
     * @return mixed
     */
    public function query($sql, $params=[], $isone=false)
    {
        $stmt = $this->database->prepare($sql, $params);
        if($isone) {
            return $stmt->fetch();
        } else {
            return $stmt->fetchAll();
        }
    }

    /**
     * execute sql
     * @param string $sql
     * @param array $params
     * @return mixed
     */
    public function execute($sql, $params)
    {
        $stmt = $this->database->prepare($sql, $params);
        if ($stmt->errorCode() == 0) {
            return $this->database->lastInsertId();
        } else {
            return -1;
        }
    }
}