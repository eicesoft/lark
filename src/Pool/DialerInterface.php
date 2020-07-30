<?php
namespace Lark\Pool;


/**
 * Interface DialerInterface
 * @package Lark\Pool
 */
interface DialerInterface
{
    /**
     * 拨号
     * @return object
     */
    public function dial();
}