<?php
namespace Lark\Annotation\Model;

use Doctrine\Common\Annotations\Annotation;

/**
 * Column annotation class
 * @package Lark\Annotation
 * @Annotation
 */
class Column {
  public $name;
  public $pk = false;
  public $default='';
}