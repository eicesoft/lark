<?php

namespace Lark\Core;


/**
 * Class StringBuffer
 * @package Lark\Core
 * @author kelezyb
 */
class StringBuffer
{
    /**
     * @var array
     */
    private $buffer;


    private $length;

    /**
     * StringBuffer constructor.
     * @param mixed $str
     */
    public function __construct($str = null)
    {
        $this->buffer = [];
        $this->length = 0;
        $this->append($str);
    }

    /**
     * @param mixed $str
     */
    public function append($obj)
    {
        if ($obj != null && (is_scalar($obj) || is_bool($obj))) {
            $str = strval($obj);
            $this->length += strlen($str);
            $this->buffer[] = $str;
        }
    }

    public function clear()
    {
        unset($this->buffer);
        $this->buffer = [];
    }

    public function indexOf($str)
    {
        var_dump((string)$this);
    }

    /**
     * @return int
     */
    public function length()
    {
        return $this->length;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return join('', $this->buffer);
    }

    /**
     * object release
     */
    public function __destruct()
    {
        unset($this->buffer);
    }
}

$str = new StringBuffer();
$str->append("test 2008");
$str->append(true);
$str->append("test 2009");
$str->append(["a1" => 20]);
$str->append(20);
$str->append(2.07);
var_dump($str->length());
var_dump($str->indexOf("test"));