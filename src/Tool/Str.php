<?php


namespace Lark\Tool;


class Str
{
    public static function len($str)
    {
        if (function_exists('mb_strlen')) {
            return mb_strlen($str, 'utf-8');
        }


        return strlen($str);
    }
}