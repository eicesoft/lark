<?php
namespace Lark\Annotation\Model;

use Doctrine\Common\Annotations\Annotation;

/**
 * Component annotation class
 * @package Lark\Annotation
 * @Annotation
 */
class Table
{
  public $name;
  public $database;
}