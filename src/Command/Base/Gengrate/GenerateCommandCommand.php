<?php
namespace Lark\Command\Base\Gengrate;


use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Command\Style;

/**
 * Class GenerateCommandCommand
 * @package Lark\Command\Base\Gengrate
 * @author kelezyb
 */
class GenerateCommandCommand extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe('gen', 'cmd', "Generate new command");
    }

    public function execute()
    {
        $args = $this->params['args'];
        $opts = $this->params['opts'];

        if ( $this->getArgLength() < 3 && $this->getOpt('name') == null) {
            $this->line("Usage:\n\tphp bin/lark gen:command CommandName CommandLine\n\tphp bin/lark gen:command --name CommandName --cmd CommandLine");
            exit(1);
        }

        if($this->getOpt('cmd')) {
            $this->line("Usage:\n\tphp bin/lark gen:command CommandName CommandLine\n\tphp bin/lark gen:command --name CommandName --cmd CommandLine");
            exit(1);
        }

        $command = $this->isOpt('name') ? $this->getOpt('name') : $this->getArg(1);
        $cmd = $this->isOpt('cmd') ? $this->getOpt('cmd') : $this->getArg(2);

        $cmds = explode(':', $cmd);
        if (count($cmds) == 1) {
            $module = 'null';
            $cmd = $cmds[0];
        } else {
            $module = "'{$cmds[0]}'";
            $cmd = $cmds[1];
        }

        $commandName = ucfirst($command) . 'Command';

        $app = _G('app');
        $command_path = $app->generate('command');
        $doc = <<<PHP
<?php
namespace App\Commands;

use App\Services\UserService;
use Lark\Annotation\Bean;
use Lark\Annotation\InjectService;
use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Command\Progress;

/**
 * {$commandName} class
 * @package App\Commands
 * @Bean
 */
class {$commandName} extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe({$module}, '{$cmd}', '');
    }

    public function execute()
    {
        return 1;
    }
}
PHP;
        $command_file = $command_path . DS . $commandName . '.php';
        if (!file_exists($command_file)) {
            file_put_contents($command_file, $doc);
            $this->line("Command {$commandName} create success.", new Style(Style::INFO));
        } else {
            $this->line("Command file {$command_file} exists, create fails.", new Style(Style::ERROR));
        }
    }
}