<?php
namespace Lark\Core;


abstract class Task
{
    protected $options;
    public function __construct($options)
    {
        $this->options = $options;
    }

    abstract public function execute();
}