<?php
namespace Lark\Annotation;

/**
 * Route Annotation class
 * @package Lark\Annotation
 * @Annotation
 */
class Route
{
    /**
     * @var string
     * @Required
     */
    public $path;

    /**
     * @var string
     */
    public $desc;

    /**
     * @var string
     */
    public $summary;

    /**
     * @var string
     * @Enum({"post", "get", "put", "delete", "option"})
     */
    public $method='get';

    /**
     * @var string
     */
    public $tag;

    /**
     * @var string
     */
    public $operationId;
}