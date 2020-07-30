<?php
namespace Lark\Event\Local;


use Lark\Event\Event;

class QueryEvent implements Event
{
    /**
     * @var string
     */
    private $sql;

    /**
     * @var array
     */
    private $params;

    /**
     * UserLoginEvent constructor.
     * @param $user
     */
    public function __construct($sql, $params)
    {
        $this->sql = $sql;
        $this->params = $params;
    }

    /**
     * @return mixed
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->params;
    }

    public function getName()
    {
        return "Database Query";
    }
}