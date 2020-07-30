<?php
namespace Lark\Command;


/**
 * Command describe
 * @package Lark\Command
 * @author kelezyb
 */
class CommandDescribe
{
    private $moudle;

    private $name;

    private $desc;

    /**
     * CommandDescribe constructor.
     * @param $moudle
     * @param $name
     * @param $desc
     */
    public function __construct($moudle, $name, $desc)
    {
        $this->moudle = $moudle;
        $this->name = $name;
        $this->desc = $desc;
    }

    /**
     * @return mixed
     */
    public function getMoudle()
    {
        return $this->moudle;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getDesc()
    {
        return $this->desc;
    }
}