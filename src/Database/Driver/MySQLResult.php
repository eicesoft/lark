<?php
namespace Lark\Database\Driver;


class MySQLResult
{
    /**
     * @var \mysqli_result
     */
    private $result;

    /**
     * @var \mysqli_stmt
     */
    private $stmt;

    /**
     * MySQLResult constructor.
     * @param $result
     */
    public function __construct($result, $stmt) {
        $this->result = $result;
        $this->stmt = $stmt;
    }

    public function rowCount()
    {
        if ($this->result) {
            return $this->result->num_rows;
        } else {
            return $this->stmt->affected_rows;
        }
    }

    public function fetch()
    {
        return mysqli_fetch_assoc($this->result);
    }

    public function fetchAll()
    {
        return mysqli_fetch_all($this->result, MYSQLI_ASSOC);
    }

    public function errorCode()
    {
        return $this->stmt->errno;
    }

    public function lastInsertId()
    {
        return $this->stmt->insert_id;
    }
}