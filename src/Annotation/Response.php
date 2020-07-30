<?php
namespace Lark\Annotation;

/**
 * Controller Annotation class
 * @package Lark\Annotation
 * @Annotation
 */
class Response
{
    /**
     * default,template,json,xml
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $data = "";
}