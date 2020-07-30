<?php
namespace Lark\Command;

use Lark\Core\Kernel;
use Lark\Loader\CommandLoader;
use Lark\Tool\CommandLine;

/**
 * Class CommandParse
 * @package Lark\Command
 * @author kelezyb
 */
class CommandProcess
{
    const BASE_MOUDLE = 'base';
    private $commands;

    /**
     * CommandProcess constructor
     */
    public function __construct()
    {
        $loader = CommandLoader::Instance();
        $cli_list = $loader->getLoadMetas();

        $this->commands = [];
        foreach($cli_list as $cli) {
            /** @var CommandDescribe $desc */
            $desc = $cli['desc'];
            $object = $cli['object'];
            $moudle = $desc->getMoudle();
            if ($moudle == null) {
                $moudle = self::BASE_MOUDLE;
            }

            if(!isset($this->commands[$moudle])) {
                $this->commands[$moudle] = [];
            }

            $this->commands[$moudle][$desc->getName()] = $object;
        }
    }

    /**
     * Command process
     * @return mixed
     * @throws \Lark\Core\Exception
     */
    public function execute()
    {
        $run_moudle = '';
        $run_command = '';
        $datas = CommandLine::parseArgs();
        $args = $datas['args'];
        $opts = $datas['opts'];
        if (count($args) > 0) {
            $commands = explode(':', $args[0]);
            if (count($commands) == 1) {
                $run_moudle = self::BASE_MOUDLE;
                $run_command = $commands[0];
            } else {
                $run_moudle = $commands[0];
                $run_command = $commands[1];
            }
        } else {
            $run_moudle = self::BASE_MOUDLE;
            $run_command = 'help';
        }
        $warn_style = new Style(Style::WARN);
        if (!isset($this->commands[$run_moudle][$run_command])) {   //命令不存在
            if ($run_moudle == self::BASE_MOUDLE) {
                echo $warn_style->format("Command {$run_command} not defined.\n\n");
            } else {
                echo $warn_style->format("Command {$run_moudle}:{$run_command} not defined.\n\n");
            }
            $run_moudle = self::BASE_MOUDLE;
            $run_command = 'help';
        }

        /** @var Command $command */
        $command = $this->commands[$run_moudle][$run_command];
        $kernel = Kernel::Instance();
        $kernel->call($command, 'setProcess', [$this]);
        return $kernel->call($command, 'execute');
    }

    /**
     * @return array
     */
    public function getCommands(): array
    {
        return $this->commands;
    }
}