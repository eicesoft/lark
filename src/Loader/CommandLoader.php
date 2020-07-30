<?php
namespace Lark\Loader;

use Lark\Core\Command;
use Lark\Core\Kernel;


/**
 * Class CommandLoader
 * @package Lark\Loader
 * @author
 */
class CommandLoader extends Loader
{
    /**
     * @var CommandLoader
     */
    private static $instance = null;

    /**
     * get static instance
     * @return CommandLoader
     */
    public static function Instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public function __construct()
    {
        $app = Kernel::Instance()->get('app');
        $controller_path = $app->generate('command');

        parent::__construct($controller_path);
        $this->build_base();
    }

    private function build_base()
    {
        $psr4 = include(BASE_ROOT . '/vendor/composer/autoload_psr4.php');
        $core_path = $psr4['Lark\\'][0] . '/Command/Base';
        $fileItems = $this->glob($core_path);
        foreach ($fileItems as $fileItem) {
            $this->load_metas = array_merge($this->load_metas , $this->buildCache($fileItem));
        }
    }

    public function load($namespace, $classname)
    {
        $nclassName = $namespace . '\\' . $classname;
        $commands = [];
        $kennel = Kernel::Instance();
        /** @var Command $command_obj */
        $command_obj = $kennel->newInstance($nclassName, []);
        if ($command_obj instanceof  \Lark\Command\Command) {
            $command_desc = $command_obj->registry();
            $commands[] = [
                'object' => $command_obj,
                'class' => $nclassName,
                'desc' => $command_desc
            ];
        }

        return $commands;
    }
}