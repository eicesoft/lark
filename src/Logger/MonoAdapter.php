<?php
namespace Lark\Logger;


use Lark\Core\Kernel;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;

/**
 * Class Logger
 * @package Lark\Logger
 */
class MonoAdapter extends LoggerAdapter
{
    /**
     * @var Logger 
     */
    private $logger;

    /**
     * Logger constructor.
     * @param $options
     */
    public function __construct($options, $handlers)
    {
        parent::__construct($options, $handlers);
        $this->logger = new Logger($options['name']);
        $formatter = new LineFormatter($options['format'], $options['date_format'],
            false, true);
        foreach ($this->handlers as $handler_cfg) {
            $handler = Kernel::Instance()->newInstance($handler_cfg['class'], $handler_cfg['params']);
            $handler->setFormatter($formatter);
            $this->logger->pushHandler($handler);
        }
    }

    public function log($message, $content = [], $level = Logger::DEBUG)
    {
        $this->logger->log($level, $message, $content);
    }
}