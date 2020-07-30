<?php

namespace Lark\Di;


use Lark\Core\Config;
use Lark\Core\Kernel;


/**
 * Class Bean
 * @package Lark\Di
 */
class Bean
{
    /**
     * @var Bean
     */
    private static $instance = null;

    /**
     * @return Bean
     */
    public static function Instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private $benas;

    private function __construct()
    {
        $this->benas = [];
    }

    /**
     * Create bean object
     * @param string $name
     * @return object
     */
    public function create($name)
    {
        $config = Config::get($name, 'beans');

        if (null !== $config) {
            $object = Kernel::Instance()->newInstance($config['class'], $config['params']);
            return $object;
        } else {

        }
    }

    /**
     * create singleton bean object
     * @param string $name
     * @return mixed
     */
    public function singleton($name)
    {
        if (!isset($this->benas[$name])) {
            $this->benas[$name] = $this->create($name);
        } else {
            return $this->benas[$name];
        }
    }
}