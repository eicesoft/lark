<?php


namespace Lark\Database;


use Lark\Pool\DialerInterface;

class DatabaseDialer implements DialerInterface
{
    private $name;

    /**
     * DatabaseDialer constructor.
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function dial()
    {
        return bean($this->name);
    }
}