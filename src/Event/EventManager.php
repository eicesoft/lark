<?php
namespace Lark\Event;


use Lark\Core\Config;
use Lark\Core\Context;
use Lark\Core\Kernel;

/**
 * Class EventManager
 * @package Lark\Event
 * @author kelezyb
 */
class EventManager extends Context
{
    private static $instance = null;

    public static function Instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private $eventListeners;

    private $listeners;

    private function __construct()
    {
        $this->eventListeners = [];
        $events = Config::get(null, 'events');
        foreach($events as $event) {
            $this->initListener($event);
        }
    }

    /**
     * init listener
     * @param string $event
     * @throws \ReflectionException
     */
    private function initListener($event)
    {
        /** @var Listener $listener */
        $listener = Kernel::Instance()->newInstance($event, []);
        $listener_events = $listener->listen();
        
        foreach($listener_events as $listener_event) {
            if (isset($this->eventListeners[$listener_event])) {
                $this->eventListeners[$listener_event][] = $event;
            } else {
                $this->eventListeners[$listener_event] = [$event];
            }
        }
        $this->listeners[$event] = $listener;
    }

    /**
     * trigger event
     * @param Event $event
     */
    public function trigger(Event $event)
    {
        $eventClass = get_class($event);
        if (isset($this->eventListeners[$eventClass])) {
            $eventListeners = $this->eventListeners[$eventClass];

            foreach($eventListeners as $eventListener) {
                /** @var Listener $listener */
                $listener = $this->listeners[$eventListener];
                if ($listener instanceof  Listener) {
//                    self::go(function () use ($listener, $event) {
                        $listener->process($event);
//                    });
                }
            }
        }
    }
}