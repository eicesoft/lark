<?php
namespace Lark\Event;


/**
 * Interface Listener
 * @package Lark\Event
 * @author kelezyb
 */
interface Listener
{
    public function listen(): array;
    public function process($event);
}