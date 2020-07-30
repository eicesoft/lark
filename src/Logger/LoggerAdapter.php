<?php
namespace Lark\Logger;


use Monolog\Logger;

abstract class LoggerAdapter
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $handlers;

    public function __construct($options, $handlers)
    {
        $this->options = $options;
        $this->handlers = $handlers;
    }

    public abstract function log($message, $content=[], $level=Logger::DEBUG);
}