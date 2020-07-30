<?php
declare(strict_types=1);
namespace Lark\Command\Base;


use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Command\Style;
use Lark\Core\Lark;

class HelpCommand extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe(null, 'help', 'Display this help message');
    }

    public function execute()
    {
        $command_bufs = [];

        $style = new Style(Style::WARN);
        $info_style = new Style(Style::INFO);
        $command_style = new Style(Style::DEBUG);

        $commands = $this->process->getCommands();
        $base = $commands['base'];
        foreach($base as $command) {
            /** @var CommandDescribe $command_desc */
            $command_desc = $command->registry();
            $command_bufs[] = $info_style->format( "    {$command_desc->getName()}\t\t") . $command_style->format($command_desc->getDesc());
        }
        unset($commands['base']);
        foreach($commands as $moudle => $commands) {
            $command_bufs[] = $style->format($moudle);
            foreach($commands as $command) {
                /** @var CommandDescribe $command_desc */
                $command_desc = ($command->registry());
                $command_bufs[] = $info_style->format("    {$moudle}:{$command_desc->getName()}") . $command_style->format("\t\t{$command_desc->getDesc()}");
            }
        }

        $command_buf = join("\n", $command_bufs);
        $version = Lark::VERSION;
        $desc = <<<CON
Lark console tool v{$version}
Usage:
    command [options] [arguments]
    
Options:
    -h, --help      Display this help message
    -v, --version   Display this application version
    
Available commands:
{$command_buf}\n
CON;
        echo $desc;

        return 'a';
    }
}