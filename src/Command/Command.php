<?php
namespace Lark\Command;

use Lark\Core\Context;
use Lark\Tool\CommandLine;

/**
 * Class Command
 * @package Lark\Core
 * @author kelezyb
 */
abstract class Command extends Context
{
    /**
     * @var array
     */
    protected $params;

    /**
     * @var CommandProcess
     */
    protected $process;

    /**
     * @var Output
     */
    private $output;

    /**
     * Command constructor.
     */
    public function __construct()
    {
        $this->params = CommandLine::parseArgs();
        $this->output = new Output();
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getOpt(string $key, $default=null)
    {
        return $this->params['opts'][$key] ?? $default;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isOpt(string $key)
    {
        return isset($this->params['opts'][$key]);
    }

    /**
     * @return int
     */
    public function getArgLength(): int
    {
        return count($this->params['args']);
    }

    /**
     * @param int $index
     * @param mixed $default
     * @return mixed
     */
    public function getArg($index, $default=null)
    {
        return $this->params['args'][$index] ?? $default;
    }

    /**
     * @param string $message
     */
    public function line(string $message, Style $style=null)
    {
        if ($style == null) {
            $style = new Style(Style::DEFAULT);
        }

        $this->output->write($message, true, $style);
    }

    /**
     * @param string $title
     * @param array $desc
     */
    public function block(string $title, array $desc=[])
    {
        $this->output->block($title, $desc);
    }

    /**
     * @param CommandProcess $process
     */
    public function setProcess(CommandProcess $process)
    {
        $this->process = $process;
    }

    /**
     * registry command to system
     * @return CommandDescribe
     */
    public abstract function registry(): CommandDescribe;

    /**
     * @return mixed
     */
    public abstract function execute();
}
