<?php
namespace Lark\Annotation;

/**
 * Parameter annotation class
 * @package Lark\Annotation
 * @author kelezyb
 * @Annotation
 */
class Parameter
{
    /**
     * parameter
     * @var string
     * @Required
     */
    public $name;

    /**
     * @var string
     */
    public $desc='';

    /**
     * range is [header, query]
     * @Enum({"header", "header"})
     * @var string
     */
    public $in = "query";

    /**
     * is required
     * @var boolean
     */
    public $required = true;

    /**
     * data type
     * @var string
     * @Enum({"array", "boolean", "integer", "number", "object", "string"})
     */
    public $type = "string";
}