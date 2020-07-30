<?php


namespace Lark\Command\Base\Gengrate;


use Lark\Command\Command;
use Lark\Command\CommandDescribe;
use Lark\Command\Style;

class GenerateEventCommand extends Command
{
    public function registry(): CommandDescribe
    {
        return new CommandDescribe('gen', 'event', 'Generate new event');
    }

    public function execute()
    {
        $args = $this->params['args'];
        $opts = $this->params['opts'];
        if ( $this->getArgLength() < 2 && $this->getOpt('name') == null) {
            $this->line("Usage:\n\tphp bin/lark gen:event EventName\n\tphp bin/lark gen:event --name EventName");
            exit(1);
        }
        $event_name = $this->isOpt('name') ? $this->getOpt('name') : $this->getArg(1);

        $listener = ucfirst($event_name) . 'Listener';
        $event = ucfirst($event_name) . 'Event';

        $app = _G('app');
        $listener_path = $app->generate('event');
        $listener_event_path = $listener_path . DS . 'Event';
        $doc = <<<PHP
<?php
namespace App\Listeners;


use App\Listeners\Event\{$event};
use Lark\Event\Listener;


/**
 * {$listener} class
 * @package App\Listeners
 */
class {$listener} implements Listener
{

    public function listen(): array
    {
        return [
            {$event}::class
        ];
    }

    public function process(\$event)
    {
    
    }
}
PHP;

        $listener_file = $listener_path . DS . $listener . '.php';
        if (!file_exists($listener_file)) {
            file_put_contents($listener_file, $doc);
            $this->line("Listener {$listener} create success.", new Style(Style::INFO));
        } else {
            $this->line("Listener file {$listener_file} exists, create fails.", new Style(Style::ERROR));
        }

        $doc = <<<PHP
<?php
namespace App\Listeners\Event;


use Lark\Event\Event;

/**
 * $event class
 * @package App\Listeners\Event
 */
class {$event} implements Event
{

    public function getName()
    {
        return '{$event}';
    }
}
PHP;

        $event_file = $listener_event_path . DS . $event . '.php';
        if (!file_exists($event_file)) {
            file_put_contents($event_file, $doc);
            $this->line("Event {$event} create success.", new Style(Style::INFO));
        } else {
            $this->line("Event file {$event_file} exists, create fails.", new Style(Style::ERROR));
        }
    }

}