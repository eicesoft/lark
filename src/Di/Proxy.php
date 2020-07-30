<?php
namespace Lark\Di;

use Lark\Core\Kernel;

/**
 * Proxy call class
 * @package Lark\Di
 * @author kelezyb
 */
class Proxy
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var object
     */
    private $prxoy = null;

    /**
     * Proxy constructor.
     * @param $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     * @throws \Lark\Core\Exception
     * @throws \ReflectionException
     */
    public function __call($name, $arguments)
    {
        $kernel = Kernel::Instance();
        if ($this->prxoy == null) {
            $this->prxoy = $kernel->newInstance($this->class, []);
        }
        
        return $kernel->call($this->prxoy, $name, $arguments);
    }
}