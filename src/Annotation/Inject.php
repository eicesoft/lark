<?php
namespace Lark\Annotation;

/**
 * Inject annotation class
 * @package Lark\Annotation
 * @Annotation
 */
class Inject
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var bool
     */
    public $singleton;
}