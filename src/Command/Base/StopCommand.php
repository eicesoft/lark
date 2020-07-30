<?php


namespace Lark\Command\Base;


use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Command\Style;

class StopCommand extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe(null, 'stop', 'Stop lark service');
    }

    public function execute()
    {
        $config = include('config.php');

        $rpc = $this->getOpt('rpc');

        if ($rpc) {
            $pid_file = $config['rpc']['params']['pid_file'] ?? null;
        } else {
            $pid_file = $config['server']['params']['pid_file'] ?? null;
        }
        
        if (is_readable($pid_file)) {
            $pid = file_get_contents($pid_file);

            $status = posix_kill($pid, 15);
            $this->line("Service Stop is " . ($status ? 'success' : 'fails') . ", pid({$pid})",
                new Style(Style::INFO));
        } else {
            $this->line("Service is not running...", new Style(Style::WARN));

        }
    }
}