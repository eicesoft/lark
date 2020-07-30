<?php
namespace Lark\Tool;


interface IPool
{
    public function get();
    public function release($object);
    public function close(): void;
}